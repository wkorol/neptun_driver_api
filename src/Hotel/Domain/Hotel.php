<?php

declare(strict_types=1);

namespace App\Hotel\Domain;

use ApiPlatform\Metadata\ApiResource;
use App\LumpSums\Domain\LumpSums;
use App\Region\Domain\Region;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-import-type RegionArray from Region
 * @phpstan-import-type LumpSumsArray from LumpSums
 *
 * @phpstan-type HotelArray array{
 *     id: string,
 *     name: string,
 *     region: ?RegionArray,
 *     lump_sums: ?LumpSumsArray,
 *     lump_sums_expire_date: ?string,
 *     new_lump_sums: ?LumpSumsArray,
 *     update_date: ?string
 * }
 */
class Hotel implements \JsonSerializable
{
    public function __construct(
        private Uuid $id,
        private string $name,
        private ?Region $region = null,
        private ?LumpSums $lumpSums = null,
        private ?\DateTimeImmutable $lumpSumsExpireDate = null,
        private ?LumpSums $newLumpSums = null,
        private ?\DateTimeImmutable $updateDate = null,
    ) {
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
        return $this->lumpSums;
    }

    public function getLumpSumsExpireDate(): ?\DateTimeImmutable
    {
        return $this->lumpSumsExpireDate;
    }

    public function getNewLumpSums(): ?LumpSums
    {
        return $this->newLumpSums;
    }

    public function getUpdateDate(): ?\DateTimeImmutable
    {
        return $this->updateDate;
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

    public function updateLumpSums(LumpSums $lumpSums): void
    {
        $this->lumpSums = $lumpSums;
    }

    public function updateLumpSumsExpireDate(?\DateTimeImmutable $expireDate = null): void
    {
        $this->lumpSumsExpireDate = $expireDate;
    }

    public function updateNewLumpSums(?LumpSums $newLumpSums): void
    {
        $this->newLumpSums = $newLumpSums;
    }

    public function updateUpdateDate(): void
    {
        $this->updateDate = new \DateTimeImmutable();
    }

    public function setRegion(?Region $region): void
    {
        $this->region = $region;
    }

    public function setLumpSums(?LumpSums $lumpSums): void
    {
        $this->lumpSums = $lumpSums;
    }

    public function setNewLumpSums(?LumpSums $newLumpSums): void
    {
        $this->newLumpSums = $newLumpSums;
    }

    /**
     * @return HotelArray
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'region' => $this->region?->jsonSerialize(),
            'lump_sums' => $this->lumpSums?->jsonSerialize(),
            'lump_sums_expire_date' => $this->lumpSumsExpireDate?->format('Y-m-d H:i:s'),
            'new_lump_sums' => $this->newLumpSums?->jsonSerialize(),
            'update_date' => $this->updateDate?->format('Y-m-d H:i:s'),
        ];
    }
}
