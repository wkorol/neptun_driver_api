<?php

declare(strict_types=1);

namespace App\Order\Repository;

use App\Order\Domain\Order;

interface OrderRepository
{
    public function findActualOrders(): ?array;
    public function findByExternalId(int $externalId): ?Order;
    public function findScheduledOrdersForToday(): ?array;
    public function findScheduledOrdersForNext5Days(): ?array;
    public function deleteAllFinished(): void;
    public function save(Order $order): void;
}