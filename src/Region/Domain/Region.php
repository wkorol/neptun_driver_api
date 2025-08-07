<?php

declare(strict_types=1);

namespace App\Region\Domain;

use App\Hotel\Domain\Hotel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

class Region implements \JsonSerializable
{
    /**
     * @param Collection<int, Hotel> $hotels
     */
    public function __construct(
        private readonly ?int       $id,
        private ?string             $name,
        private ?int                $position = null,
        private readonly Collection $hotels = new ArrayCollection(),
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return Collection<int, Hotel>
     */
    public function getHotels(): Collection
    {
        return $this->hotels;
    }

    public function addHotel(Hotel $hotel): void
    {
        if (!$this->hotels->contains($hotel)) {
            $this->hotels->add($hotel);
        }
    }

    public function removeHotel(Hotel $hotel): void
    {
        $this->hotels->removeElement($hotel);
    }

    public function getHotelsSortedByName(): Collection
    {
        return $this->hotels->matching(
            Criteria::create()->orderBy(['name' => 'ASC'])
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
