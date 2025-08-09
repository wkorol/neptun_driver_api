<?php

declare(strict_types=1);

namespace App\Project\UseCase\UpdateHotel;

use App\Project\UseCase\UpdateHotelHandler;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-import-type HotelUpdateDataArray from UpdateHotelHandler
 */
class Command
{
    /**
     * @param HotelUpdateDataArray $data
     */
    public function __construct(
        public Uuid $hotelId,
        public array $data,
    ) {
    }
}
