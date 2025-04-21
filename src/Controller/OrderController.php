<?php

declare(strict_types=1);


namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\MamTaxiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class OrderController extends AbstractController
{
    public function __construct(private OrderRepository $orderRepository, private MamTaxiClient $mamTaxiClient, private EntityManagerInterface $em)
    {
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

    #[Route('/orders/update-scheduled', name: 'update_all_existing_orders', methods: ['GET'])]
    public function updateAllExistingOrders(): JsonResponse
    {
        $orders = $this->orderRepository->findScheduledOrdersForToday();
        $updatedCount = 0;
        $batchSize = 100;

        if ($orders) {
            $chunks = array_chunk($orders, $batchSize);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $order) {
                    $data = $this->mamTaxiClient->fetchOrderDetails($order->getExternalId());

                    if ($this->orderRepository->updateOrder($order, $data)) {
                        $updatedCount++;
                    }
                }

                $this->em->flush();
                $this->em->clear();
            }
        }

        return new JsonResponse("Updated {$updatedCount} orders");
    }

    #[Route('/orders/delete-all-finished', name: 'delete_all_finished', methods: ['GET'])]
    public function deleteAllFinished(): JsonResponse
    {
        $this->orderRepository->deleteAllFinished();

        return new JsonResponse("Deleted finished orders");
    }

}