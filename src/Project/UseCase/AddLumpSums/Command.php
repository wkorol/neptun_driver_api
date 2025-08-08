<?php

declare(strict_types=1);

namespace App\Project\UseCase\AddLumpSums;

use App\LumpSums\Domain\LumpSums;

readonly class Command
{
    public function __construct(
        public LumpSums $lumpSums,
    ) {
    }
}
