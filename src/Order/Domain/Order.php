<?php

declare(strict_types=1);

namespace App\Order\Domain;

use App\DTO\Status;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type OrderArray array{
 *     Id: int,
 *     CreatedAt: DateTimeImmutable,
 *     PlannedArrivalDate: ?string,
 *     Status: ?string,
 *     City: string,
 *     Street: ?string,
 *     House: ?string,
 *     From: string,
 *     TaxiNumber: ?string,
 *     Destination: ?string,
 *     Notes: ?string,
 *     PhoneNumber: ?string,
 *     CompanyName: ?string,
 *     Price: ?float,
 *     PassengersCount: ?int,
 *     PaymentMethod: ?int,
 *     ExternalOrderId: ?int,
 * }
 */
class Order implements \JsonSerializable
{
    public function __construct(
        private readonly Uuid $id,
        private readonly int $externalId,
        private \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $plannedArrivalDate,
        private int $status,
        private string $city,
        private ?string $street,
        private ?string $house,
        private string $from,
        private ?string $taxiNumber,
        private ?string $destination,
        private ?string $notes,
        private ?string $phoneNumber,
        private ?string $companyName,
        private ?float $price,
        private ?int $passengerCount,
        private ?int $paymentMethod,
        private ?int $externalOrderId,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getExternalId(): int
    {
        return $this->externalId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPlannedArrivalDate(): ?\DateTimeImmutable
    {
        return $this->plannedArrivalDate;
    }

    public function getStatus(): ?Status
    {
        return Status::tryFrom($this->status);
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getHouse(): ?string
    {
        return $this->house;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTaxiNumber(): ?string
    {
        return $this->taxiNumber;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getPassengerCount(): ?int
    {
        return $this->passengerCount;
    }

    public function getPaymentMethod(): ?int
    {
        return $this->paymentMethod;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function setHouse(?string $house): void
    {
        $this->house = $house;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    public function setTaxiNumber(?string $taxiNumber): void
    {
        $this->taxiNumber = $taxiNumber;
    }

    public function setDestination(?string $destination): void
    {
        $this->destination = $destination;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function setCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function setPassengerCount(?int $passengerCount): void
    {
        $this->passengerCount = $passengerCount;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    public function setArrivalDate(?\DateTimeImmutable $plannedArrivalDate): void
    {
        $this->plannedArrivalDate = $plannedArrivalDate;
    }

    public function setPaymentMethod(?int $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getExternalOrderId(): ?int
    {
        return $this->externalOrderId;
    }

    public function setExternalOrderId(?int $externalOrderId): void
    {
        $this->externalOrderId = $externalOrderId;
    }

    /**
     * @return OrderArray
     */
    public function jsonSerialize(): array
    {
        return [
            'Id' => $this->getExternalId(),
            'CreatedAt' => $this->getCreatedAt(),
            'PlannedArrivalDate' => $this->getPlannedArrivalDate()?->format('Y-m-d\TH:i:sP'),
            'Status' => $this->getStatus()?->toLabel(),
            'City' => $this->getCity(),
            'Street' => $this->getStreet(),
            'House' => $this->getHouse(),
            'From' => $this->getFrom(),
            'TaxiNumber' => $this->getTaxiNumber(),
            'Destination' => $this->getDestination(),
            'Notes' => $this->getNotes(),
            'PhoneNumber' => $this->getPhoneNumber(),
            'CompanyName' => $this->getCompanyName(),
            'Price' => $this->getPrice(),
            'PassengersCount' => $this->getPassengerCount(),
            'PaymentMethod' => $this->getPaymentMethod(),
            'ExternalOrderId' => $this->getExternalOrderId(),
        ];
    }
}
