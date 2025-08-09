<?php

declare(strict_types=1);

namespace App\Hotel\Repository;

use App\Hotel\Domain\Hotel;
use Symfony\Component\Uid\Uuid;

interface HotelRepository
{
    /**
     * @return Hotel[]
     */
    public function all(): array;

    /**
     * @return Hotel[]
     */
    public function getByRegionId(int $regionId): array;

    public function findById(Uuid $id): ?Hotel;

    public function findByName(string $name): ?Hotel;

    public function add(Hotel $hotel): void;

    public function checkIfExists(string $name): bool;
}
