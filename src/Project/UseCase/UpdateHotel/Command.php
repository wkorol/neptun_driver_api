<?php

declare(strict_types=1);

namespace App\Project\UseCase\UpdateHotel;

use Symfony\Component\Uid\Uuid;

class Command
{
    public function __construct(
        public Uuid $hotelId,
        public array $data
    ) {
    }
}