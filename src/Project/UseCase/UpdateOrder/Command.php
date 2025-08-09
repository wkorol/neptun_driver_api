<?php

declare(strict_types=1);

namespace App\Project\UseCase\UpdateOrder;

readonly class Command
{
    public function __construct(
        public ?int $externalId = null,
        public ?\DateTimeImmutable $plannedArrivalDate = null,
        public ?int $status = null,
        public ?string $notes = null,
        public ?string $phoneNumber = null,
        public ?string $companyName = null,
        public ?float $price = null,
        public ?int $passengerCount = null,
        public ?int $paymentMethod = null,
    ) {
    }
}
