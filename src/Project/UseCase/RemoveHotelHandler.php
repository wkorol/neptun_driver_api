<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Hotel\Repository\HotelRepository;
use App\Project\UseCase\RemoveHotel\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class RemoveHotelHandler
{
    public function __construct(private HotelRepository $hotelRepository, private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Command $command): void
    {
        $hotel = $this->hotelRepository->findById($command->id);
        if ($hotel) {
            $this->entityManager->remove($hotel);
            $this->entityManager->flush();
        }
    }
}