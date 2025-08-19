<?php

declare(strict_types=1);

namespace App\Project\UseCase\RemoveRegion;

readonly class Command
{
    public function __construct(public int $id)
    {
    }
}
