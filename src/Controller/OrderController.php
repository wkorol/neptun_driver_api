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

    #[Route('/orders/scheduled', name: 'orders_scheduled', methods: ['GET'])]
    public function getScheduledOrders(): JsonResponse
    {
        return new JsonResponse($this->orderRepository->findScheduledOrders());
    }
}