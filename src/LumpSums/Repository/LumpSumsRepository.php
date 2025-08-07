<?php

declare(strict_types=1);

namespace App\LumpSums\Repository;

use App\LumpSums\Domain\LumpSums;
use Symfony\Component\Uid\Uuid;

interface LumpSumsRepository
{
    public function all(): array;
    public function find(Uuid $id): ?LumpSums;
    public function addLumpSums(LumpSums $fixedPrice): void;
    public function removeLumpSums(Uuid $id): void;
    public function updateLumpSums(LumpSums $existingLumpSums, array $data): void;

}