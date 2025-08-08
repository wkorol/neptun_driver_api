<?php

declare(strict_types=1);

namespace App\Project\UseCase\UpdateOrder;

class Command
{
    public function __construct(
        public readonly ?int $externalId = null,
        public readonly ?string $plannedArrivalDate = null,
        public readonly ?int $status = null,
        public readonly ?string $notes = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?string $companyName = null,
        public readonly ?float $price = null,
        public readonly ?int $passengerCount = null,
        public readonly ?int $paymentMethod = null
    ) {}
}