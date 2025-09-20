<?php

declare(strict_types=1);

namespace App\Region\Domain;

use App\Hotel\Domain\Hotel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @phpstan-type RegionArray array{
 *     id: ?int,
 *     name: ?string,
 *     position: ?int,
 *     imgLink: ?string,
 * }
 */
class Region implements \JsonSerializable
{
    /** @var Collection<int, Hotel> */
    private Collection $hotels;

    public function __construct(
        private ?int $id,
        private ?string $name,
        private ?int $position = null,
        private ?string $imgLink = null,
    ) {
        $this->hotels = new ArrayCollection();
    }

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

    /** @return Collection<int, Hotel> */
    public function getHotels(): Collection
    {
        return $this->hotels;
    }

    public function addHotel(Hotel $hotel): void
    {
        if (!$this->hotels->contains($hotel)) {
            $this->hotels->add($hotel);
            $hotel->setRegion($this);
        }
    }

    public function removeHotel(Hotel $hotel): void
    {
        if ($this->hotels->removeElement($hotel)) {
            if ($hotel->getRegion() === $this) {
                $hotel->setRegion(null);
            }
        }
    }

    /**
     * @return RegionArray
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'imgLink' => $this->imgLink
        ];
    }
}
