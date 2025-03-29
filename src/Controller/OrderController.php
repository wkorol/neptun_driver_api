<?php

declare(strict_types=1);


namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class OrderController extends AbstractController
{
    public function __construct(private OrderRepository $orderRepository)
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
}