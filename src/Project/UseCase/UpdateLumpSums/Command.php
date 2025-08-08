<?php

declare(strict_types=1);

namespace App\Project\UseCase\UpdateLumpSums;

use Symfony\Component\Uid\Uuid;

class Command
{
    public function __construct(
        public Uuid $id,
        public array $data
    ) {
    }
}