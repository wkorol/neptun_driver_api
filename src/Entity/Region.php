<?php

namespace App\Entity;

use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
class Region implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: "integer", unique: true)]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Hotel>
     */
    #[ORM\OneToMany(targetEntity: Hotel::class, mappedBy: 'region')]
    private Collection $hotels;

    #[ORM\Column(type: 'integer', unique: true, nullable: true)]
    private ?int $position = null;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
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

    /**
     * @return Collection<int, Hotel>
     */
    public function getHotelsSortedByName(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['name' => 'ASC']);

        return $this->hotels->matching($criteria);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
