<?php

declare(strict_types=1);

namespace App\Order\Repository;

use App\Order\Domain\Order;

interface OrderRepository
{
    /**
     * @return Order[]|null
     */
    public function findActualOrders(): ?array;

    public function findByExternalId(int $externalId): ?Order;

    /**
     * @return Order[]|null
     */
    public function findScheduledOrdersForToday(): ?array;

    /**
     * @return Order[]|null
     */
    public function findScheduledOrdersForNext5Days(): ?array;

    /**
     * @return Order[]|null
     */
    public function findLast3OrdersWithPhoneNumber(string $phoneNumber, int $externalId): ?array;

    public function deleteAllFinished(): void;

    public function save(Order $order): void;

    /**
     * @return Order[]
     */
    public function all(): array;
}
