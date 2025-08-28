<?php

declare(strict_types=1);

namespace App\LumpSums\Domain;

use ApiPlatform\Metadata\ApiResource;
use App\DTO\FixedPrice;
use App\Hotel\Domain\Hotel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-import-type FixedPriceArray from FixedPrice
 *
 * @phpstan-type LumpSumsArray array{
 *     id: string,
 *     name: string,
 *     fixedValues: FixedPrice[]
 * }
 */
class LumpSums implements \JsonSerializable
{
    /** @var Collection<int, Hotel> */
    private Collection $hotels;

    /** @var Collection<int, Hotel> */
    private Collection $newHotels;

    /**
     * @param FixedPrice[] $fixedValues
     */
    public function __construct(
        private Uuid $id,
        private string $name,
        private array $fixedValues,
    ) {
        $this->hotels = new ArrayCollection();
        $this->newHotels = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return FixedPrice[]
     */
    public function getFixedValues(): array
    {
        return $this->fixedValues;
    }

    /**
     * @param FixedPrice[] $fixedValues
     */
    public function setFixedValues(array $fixedValues): void
    {
        $this->fixedValues = $fixedValues;
    }

    /** @return Collection<int, Hotel> */
    public function getHotels(): Collection
    {
        return $this->hotels;
    }

    public function addHotel(Hotel $hotel): void
    {
        if (!$this->hotels->contains($hotel)) {
            $this->hotels->add($hotel);
            // owning side is Hotel.lumpSums
            $hotel->setLumpSums($this);
        }
    }

    public function removeHotel(Hotel $hotel): void
    {
        if ($this->hotels->removeElement($hotel)) {
            if ($hotel->getLumpSums() === $this) {
                $hotel->setLumpSums(null);
            }
        }
    }

    /** @return Collection<int, Hotel> */
    public function getNewHotels(): Collection
    {
        return $this->newHotels;
    }

    public function addNewHotel(Hotel $hotel): void
    {
        if (!$this->newHotels->contains($hotel)) {
            $this->newHotels->add($hotel);
            // owning side is Hotel.newLumpSums
            $hotel->setNewLumpSums($this);
        }
    }

    public function removeNewHotel(Hotel $hotel): void
    {
        if ($this->newHotels->removeElement($hotel)) {
            if ($hotel->getNewLumpSums() === $this) {
                $hotel->setNewLumpSums(null);
            }
        }
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     fixedValues: FixedPrice[]
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'fixedValues' => $this->fixedValues,
        ];
    }
}
