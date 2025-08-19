<?php

declare(strict_types=1);

namespace App\Project\UseCase\RemoveLumpSums;

use Symfony\Component\Uid\Uuid;

readonly class Command
{
    public function __construct(
        public Uuid $id,
    ) {
    }
}
