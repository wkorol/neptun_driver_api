<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Hotel\Repository\HotelRepository;
use App\Project\UseCase\AddHotel\Command;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class AddHotelHandler
{
    public function __construct(
        private HotelRepository $hotelRepository
    ) {
    }

    public function __invoke(Command $command): void
    {
        $this->hotelRepository->add($command->hotel);
    }
}