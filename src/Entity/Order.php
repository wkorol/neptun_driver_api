<?php

declare(strict_types=1);

namespace App\Entity;

use App\DTO\Status;
use App\Repository\OrderRepository;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_external_id', columns: ['external_id'])
])]
class Order implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'integer', unique: true)]
    private int $externalId;
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $plannedArrivalDate = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $status;
    #[ORM\Column(type: 'string')]
    private string $city;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $street;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $house;
    #[ORM\Column(name: '`from`', type: 'string')]
    private string $from;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $taxiNumber;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $destination;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $notes;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $phoneNumber;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $companyName;
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $price;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $passengerCount;

    /**
     * @param int $externalId
     * @param \DateTimeImmutable $createdAt
     * @param \DateTimeImmutable|null $plannedArrivalDate
     * @param int $status
     * @param string $city
     * @param string|null $street
     * @param string|null $house
     * @param string $from
     * @param string|null $taxiNumber
     * @param string|null $destination
     * @param string|null $notes
     * @param string|null $phoneNumber
     * @param string|null $companyName
     * @param float|null $price
     * @param int|null $passengerCount
     */
    public function __construct(int $externalId, \DateTimeImmutable $createdAt, ?\DateTimeImmutable $plannedArrivalDate, int $status, string $city, ?string $street, ?string $house, string $from, ?string $taxiNumber, ?string $destination, ?string $notes, ?string $phoneNumber, ?string $companyName, ?float $price, ?int $passengerCount)
    {
        $this->id = Uuid::v4();
        $this->externalId = $externalId;
        $this->createdAt = $createdAt;
        $this->plannedArrivalDate = $plannedArrivalDate;
        $this->status = $status;
        $this->city = $city;
        $this->street = $street;
        $this->house = $house;
        $this->from = $from;
        $this->taxiNumber = $taxiNumber;
        $this->destination = $destination;
        $this->notes = $notes;
        $this->phoneNumber = $phoneNumber;
        $this->companyName = $companyName;
        $this->price = $price;
        $this->passengerCount = $passengerCount;
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
        if ($this->status === null) {
            return null;
        }
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
    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    public function setArrivalDate(\DateTimeImmutable $plannedArrivalDate): void
    {
        $this->plannedArrivalDate = $plannedArrivalDate;
    }


    public function jsonSerialize(): array
    {
       return [
           'createdAt' => $this->getCreatedAt(),
           'plannedArrivalDate' => $this->getPlannedArrivalDate()?->format('Y-m-d\TH:i:sP'),
           'status' => $this->getStatus()?->toLabel(),
           'city' => $this->getCity(),
           'street' => $this->getStreet(),
           'house' => $this->getHouse(),
           'from' => $this->getFrom(),
           'taxiNumber' => $this->getTaxiNumber(),
           'destination' => $this->getDestination(),
           'notes' => $this->getNotes(),
           'phoneNumber' => $this->getPhoneNumber(),
           'companyName' => $this->getCompanyName(),
           'price' => $this->getPrice(),
           'passengerCount' => $this->getPassengerCount(),
       ];
    }
}
