<?php

declare(strict_types=1);

namespace App\Controller;

use App\Order\Repository\OrderRepository;
use App\Project\UseCase\DeleteAllFinishedOrders;
use App\Project\UseCase\DeleteAllFinishedOrdersHandler;
use App\Project\UseCase\UpdateOrder\Command;
use App\Project\UseCase\UpdateOrderHandler;
use App\Service\MamTaxiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/orders/now', name: 'orders_actual', methods: ['GET'])]
    public function getActualOrders(): JsonResponse
    {
        return new JsonResponse($this->orderRepository->findActualOrders());
    }

    #[Route('/orders/update/actual', name: 'update_all_existing_orders', methods: ['GET'])]
    public function updateAllExistingOrders(): JsonResponse
    {
        $scheduledOrdersForToday = $this->orderRepository->findScheduledOrdersForToday();
        $ordersForNext5Days = $this->orderRepository->findScheduledOrdersForNext5Days();
        $actualOrders = $this->orderRepository->findActualOrders();
        $orders = array_merge($scheduledOrdersForToday, $ordersForNext5Days, $actualOrders);
        $updatedCount = 0;
        $batchSize = 100;

        if ($orders) {
            $chunks = array_chunk($orders, $batchSize);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $order) {
                    $data = $this->mamTaxiClient->fetchOrderDetails($order->getExternalId());

                    if ($this->updateOrderHandler->__invoke(new Command(
                        $data['Id'],
                        $data['PlannedArrivalDate'],
                        $data['Status'],
                        $data['Notes'],
                        $data['PhoneNumber'],
                        $data['CompanyName'],
                        $data['Price'],
                        $data['PassengersCount'],
                        $data['PaymentMethod'],
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
