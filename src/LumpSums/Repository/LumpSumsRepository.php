<?php

declare(strict_types=1);

namespace App\LumpSums\Repository;

use App\LumpSums\Domain\LumpSums;
use Symfony\Component\Uid\Uuid;

interface LumpSumsRepository
{
    /**
     * @return LumpSums[]
     */
    public function all(): array;

    public function find(Uuid $id): ?LumpSums;

    public function addLumpSums(LumpSums $fixedPrice): void;
}
