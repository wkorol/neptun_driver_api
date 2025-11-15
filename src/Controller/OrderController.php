<?php

declare(strict_types=1);

namespace App\Controller;

use App\Order\Domain\Order;
use App\Order\Repository\OrderRepository;
use App\Project\UseCase\DeleteAllFinishedOrders;
use App\Project\UseCase\DeleteAllFinishedOrdersHandler;
use App\Project\UseCase\UpdateOrder\Command;
use App\Project\UseCase\UpdateOrderHandler;
use App\Service\MamTaxiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private MamTaxiClient $mamTaxiClient,
        private EntityManagerInterface $entityManager,
        private UpdateOrderHandler $updateOrderHandler,
        private DeleteAllFinishedOrdersHandler $deleteAllFinishedOrdersHandler,
    ) {
    }

    #[Route('/orders/scheduled/today', name: 'orders_scheduled', methods: ['GET'])]
    public function getScheduledOrdersForToday(): JsonResponse
    {
        return new JsonResponse($this->orderRepository->findScheduledOrdersForToday());
    }

    #[Route('/orders/scheduled/next5days', name: 'orders_scheduled_next5days', methods: ['GET'])]
    public function getScheduledOrdersForNext5Days(): JsonResponse
    {
        return new JsonResponse($this->orderRepository->findScheduledOrdersForNext5Days());
    }

    #[Route('/orders/find-by-phone', name: 'orders_find-by-phone', methods: ['GET'])]
    public function findOrdersByPhoneNumber(Request $request): JsonResponse
    {
        $phoneNumber = $request->query->get('phoneNumber');
        $externalId = $request->query->getInt('externalId');

        if (!$phoneNumber) {
            return new JsonResponse(['error' => 'Missing phone parameter'], 400);
        }

        return new JsonResponse(
            $this->orderRepository->findLast3OrdersWithPhoneNumber($phoneNumber, $externalId)
        );
    }

    #[Route('/orders/now', name: 'orders_actual', methods: ['GET'])]
    public function getActualOrders(): JsonResponse
    {
        return new JsonResponse($this->orderRepository->findActualOrders());
    }

    #[Route('/orders/update/actual', name: 'update_all_existing_orders', methods: ['GET'])]
    public function updateAllExistingOrders(Request $request): JsonResponse
    {
        $updateAll = $request->get('all', false);
        $orders = null;
        if ($updateAll) {
            $orders = $this->orderRepository->all();
        }
        $scheduledOrdersForToday = $this->orderRepository->findScheduledOrdersForToday();
        $ordersForNext5Days = $this->orderRepository->findScheduledOrdersForNext5Days();
        $actualOrders = $this->orderRepository->findActualOrders();
        if (!$orders) {
            $orders = array_merge($scheduledOrdersForToday, $ordersForNext5Days, $actualOrders);
        }

        $updatedCount = 0;
        $batchSize = 100;
        if ($orders) {
            $chunks = array_chunk($orders, $batchSize);
            foreach ($chunks as $chunk) {
                /**
                 * @var Order $order
                 */
                foreach ($chunk as $order) {
                    $data = $this->mamTaxiClient->fetchOrderDetails($order->getExternalId());
                    if ($this->updateOrderHandler->__invoke(new Command(
                        $data['firstResponse']['Id'],
                        $data['firstResponse']['PlannedArrivalDate'] ? (new \DateTimeImmutable($data['firstResponse']['PlannedArrivalDate']))->modify('+1hour') : null,
                        $data['firstResponse']['Status'],
                        $data['firstResponse']['Notes'],
                        $data['firstResponse']['PhoneNumber'],
                        $data['firstResponse']['CompanyName'],
                        $data['firstResponse']['Price'],
                        $data['firstResponse']['PassengersCount'],
                        $data['firstResponse']['PaymentMethod'],
                        $data['secondResponse']['TaxiNumber'],
                        $data['firstResponse']['ExternalOrderId'],
                    ))) {
                        ++$updatedCount;
                    }
                }

                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        return new JsonResponse("Updated {$updatedCount} orders");
    }

    #[Route('/orders/delete-all-finished', name: 'delete_all_finished', methods: ['GET'])]
    public function deleteAllFinished(): JsonResponse
    {
        $this->deleteAllFinishedOrdersHandler->__invoke(new DeleteAllFinishedOrders\Command());

        return new JsonResponse('Deleted finished orders');
    }
}
