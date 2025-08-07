<?php

declare(strict_types=1);

namespace App\Order\Repository;

use App\Order\Domain\Order;

interface OrderRepository
{
    public function addOrder(Order $order): void;
    public function findActualOrders(): ?array;
    public function findScheduledOrdersForToday(): ?array;
    public function findScheduledOrdersForNext5Days(): ?array;
    public function updateOrder(Order $order, array $data): bool;
    public function deleteAllFinished(): void;
}