<?php

declare(strict_types=1);

namespace App\Project\UseCase\UpdateLumpSums;

use App\Project\UseCase\UpdateLumpSumsHandler;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-import-type UpdateLumpSumsDataArray from UpdateLumpSumsHandler
 */
class Command
{
    /**
     * @param UpdateLumpSumsDataArray $data
     */
    public function __construct(
        public Uuid $id,
        public array $data,
    ) {
    }
}
