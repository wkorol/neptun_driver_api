<?php

namespace App\Entity;

use App\Repository\HotelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: HotelRepository::class)]
class Hotel implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[Orm\Column(type: 'string', length: 255, unique: true)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'hotels')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true, onDelete: null)]
    private ?Region $region = null;

    #[ORM\ManyToOne(inversedBy: 'hotels')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true, onDelete: null)]
    private ?LumpSums $lump_sums = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lump_sums_expire_date = null;

    #[ORM\ManyToOne(inversedBy: 'hotels')]
    private ?LumpSums $new_lump_sums = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $update_date = null;

    public function __construct(
        string $name,
        Region $region,
        LumpSums $lumpSums,
        ?\DateTimeImmutable $lumpSumsExpireDate = null,
        ?LumpSums $newLumpSums = null
    )
    {
        $this->id = Uuid::v4();
        $this->name = $name;
        $this->region = $region;
        $this->lump_sums = $lumpSums;
        $this->lump_sums_expire_date = $lumpSumsExpireDate;
        $this->new_lump_sums = $newLumpSums;
        $this->update_date = new \DateTimeImmutable();
    }


    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function getLumpSums(): ?LumpSums
    {
        return $this->lump_sums;
    }

    public function getLumpSumsExpireDate(): ?\DateTimeImmutable
    {
        return $this->lump_sums_expire_date;
    }

    public function getNewLumpSums(): ?LumpSums
    {
        return $this->new_lump_sums;
    }

    public function getUpdateDate(): ?\DateTimeImmutable
    {
        return $this->update_date;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    public function updateRegion(Region $region): void
    {
        $this->region = $region;
    }

    public function updateLumpSums(LumpSums $lumpSums,): void
    {
        $this->lump_sums = $lumpSums;

    }

    public function updateLumpSumsExpireDate(?\DateTimeImmutable $expireDate = null): void
    {
        $this->lump_sums_expire_date = $expireDate;
    }

    public function updateNewLumpSums(?LumpSums $newLumpSums): void
    {
        $this->new_lump_sums = $newLumpSums;
    }

    public function updateUpdateDate(): void
    {
        $this->update_date = new \DateTimeImmutable();
    }


    public function jsonSerialize(): array
    {
        return [
          'id' => $this->id,
          'name' => $this->name,
          'region' => $this->region,
          'lump_sums' => $this->lump_sums,
          'lump_sums_expire_date' => $this->lump_sums_expire_date,
          'new_lump_sums' => $this->new_lump_sums,
          'update_date' => $this->update_date
        ];
    }


}
