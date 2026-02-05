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
use App\Service\OrderListTokenValidator;
use App\Service\OrderUpdatesTracker;
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
        private OrderUpdatesTracker $updatesTracker,
        private OrderListTokenValidator $tokenValidator,
        private bool $ordersFetchingDisabled,
    ) {
    }

    #[Route('/orders/scheduled/today', name: 'orders_scheduled', methods: ['GET'])]
    public function getScheduledOrdersForToday(Request $request): JsonResponse
    {
        if ($disabled = $this->denyIfOrdersFetchingDisabled()) {
            return $disabled;
        }
        if ($denied = $this->tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        return new JsonResponse($this->orderRepository->findScheduledOrdersForToday());
    }

    #[Route('/orders/scheduled/next5days', name: 'orders_scheduled_next5days', methods: ['GET'])]
    public function getScheduledOrdersForNext5Days(Request $request): JsonResponse
    {
        if ($disabled = $this->denyIfOrdersFetchingDisabled()) {
            return $disabled;
        }
        if ($denied = $this->tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        return new JsonResponse($this->orderRepository->findScheduledOrdersForNext5Days());
    }

    #[Route('/orders/history/by-day', name: 'orders_history_by_day', methods: ['GET'])]
    public function getOrdersHistoryByDay(Request $request): JsonResponse
    {
        if ($disabled = $this->denyIfOrdersFetchingDisabled()) {
            return $disabled;
        }
        if ($denied = $this->tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        $date = $request->query->get('date');
        $page = max(1, $request->query->getInt('page', 1));
        $size = $request->query->getInt('size', 20);
        $size = max(1, min(200, $size));

        if (!$date) {
            return new JsonResponse(['error' => 'Missing date'], 400);
        }

        try {
            $day = new \DateTimeImmutable($date);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid date'], 400);
        }

        $start = $day->setTime(0, 0, 0);
        $end = $day->setTime(23, 59, 59);
        $offset = ($page - 1) * $size;

        $items = $this->orderRepository->findOrdersForDay($start, $end, $offset, $size);
        $total = $this->orderRepository->countOrdersForDay($start, $end);

        return new JsonResponse([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'size' => $size,
        ]);
    }

    #[Route('/orders/find-by-phone', name: 'orders_find-by-phone', methods: ['GET'])]
    public function findOrdersByPhoneNumber(Request $request): JsonResponse
    {
        if ($disabled = $this->denyIfOrdersFetchingDisabled()) {
            return $disabled;
        }
        if ($denied = $this->tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        $phoneNumber = $request->query->get('phoneNumber');
        $externalId = $request->query->getInt('externalId');

        if (!$phoneNumber) {
            return new JsonResponse(['error' => 'Missing phone parameter'], 400);
        }

        return new JsonResponse(
            $this->orderRepository->findLast3OrdersWithPhoneNumber($phoneNumber, $externalId)
        );
    }

    #[Route('/orders/find-history-batch', name: 'orders_find_history_batch', methods: ['POST'])]
    public function findHistoryBatch(Request $request): JsonResponse
    {
        if ($disabled = $this->denyIfOrdersFetchingDisabled()) {
            return $disabled;
        }
        if ($denied = $this->tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        // działa dla JSON!
        $payload = $request->toArray();
        $phonesData = $payload['phones'] ?? null;

        if (!$phonesData || !is_array($phonesData)) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $result = [];

        foreach ($phonesData as $phone => $excludedIds) {
            $result[$phone] = $this->orderRepository->findLast3OrdersWithPhoneExcluding(
                $phone,
                $excludedIds ?? []
            );
        }

        return new JsonResponse($result);
    }



    #[Route('/orders/now', name: 'orders_actual', methods: ['GET'])]
    public function getActualOrders(Request $request): JsonResponse
    {
        if ($disabled = $this->denyIfOrdersFetchingDisabled()) {
            return $disabled;
        }
        if ($denied = $this->tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        return new JsonResponse($this->orderRepository->findActualOrders());
    }

    #[Route('/orders/summary', name: 'orders_summary', methods: ['GET'])]
    public function getOrdersSummary(Request $request): JsonResponse
    {
        if ($disabled = $this->denyIfOrdersFetchingDisabled()) {
            return $disabled;
        }
        if ($denied = $this->tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        return new JsonResponse([
            'today' => $this->orderRepository->findScheduledOrdersForToday(),
            'actual' => $this->orderRepository->findActualOrders(),
            'next5' => $this->orderRepository->findScheduledOrdersForNext5Days(),
        ]);
    }

    #[Route('/orders/update/actual', name: 'update_all_existing_orders', methods: ['GET'])]
    public function updateAllExistingOrders(Request $request): JsonResponse
    {
        if ($this->ordersFetchingDisabled) {
            return new JsonResponse('Order fetching is temporarily disabled.', 503);
        }
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

        if ($updatedCount > 0) {
            $this->updatesTracker->touch();
        }

        return new JsonResponse("Updated {$updatedCount} orders");
    }

    private function denyIfOrdersFetchingDisabled(): ?JsonResponse
    {
        if (!$this->ordersFetchingDisabled) {
            return null;
        }

        return new JsonResponse('Order fetching is temporarily disabled.', 503);
    }

    #[Route('/orders/delete-all-finished', name: 'delete_all_finished', methods: ['GET'])]
    public function deleteAllFinished(): JsonResponse
    {
        $this->deleteAllFinishedOrdersHandler->__invoke(new DeleteAllFinishedOrders\Command());
        $this->updatesTracker->touch();

        return new JsonResponse('Deleted finished orders');
    }
}
