<?php

declare(strict_types=1);

namespace App\Hotel\Domain;

use App\LumpSums\Domain\LumpSums;
use App\Region\Domain\Region;
use Symfony\Component\Uid\Uuid;

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

    public function updateLumpSums(LumpSums $lumpSums,): void
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


    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'region' => $this->region,
            'lump_sums' => $this->lumpSums,
            'lump_sums_expire_date' => $this->lumpSumsExpireDate,
            'new_lump_sums' => $this->newLumpSums,
            'update_date' => $this->updateDate->format('Y-m-d H:i:s'),
        ];
    }
}