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
    public function add(Region $region): void;
    public function remove(int $id): void;
}