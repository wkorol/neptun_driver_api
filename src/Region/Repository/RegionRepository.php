<?php

declare(strict_types=1);

namespace App\Region\Repository;

use App\Region\Domain\Region;

interface RegionRepository
{
    /**
     * @return Region[]
     */
    public function all(): array;
    public function findById(int $id): ?Region;
    public function addRegion(Region $region): void;
    public function removeRegion(int $id): void;
    public function editRegion(int $id, mixed $data): void;
}