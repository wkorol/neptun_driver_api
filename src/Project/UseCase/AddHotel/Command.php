<?php

declare(strict_types=1);

namespace App\Project\UseCase\AddHotel;

use App\Hotel\Domain\Hotel;

readonly class Command
{
    public function __construct(
        public Hotel $hotel
    ) {
    }
}