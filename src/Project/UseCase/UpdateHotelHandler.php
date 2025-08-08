<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Hotel\Repository\HotelRepository;
use App\LumpSums\Domain\LumpSums;
use App\Project\UseCase\UpdateHotel\Command;
use App\Region\Domain\Region;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateHotelHandler
{
    public function __construct(
        private HotelRepository        $hotelRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function __invoke(Command $command): void
    {
        $existingHotel = $this->hotelRepository->findById($command->hotelId);
        $data = $command->data;

        if (!$existingHotel) {
            throw new \PDOException("Hotel nieznaleziony.");
        }
        if (isset($data['name'])) {
            if (!$this->hotelRepository->checkIfExists($data['name'])) {
                throw new \PDOException('Hotel o podanej nazwie już istnieje w systemie.');
            }
            $existingHotel->updateName($data['name']);
        }

        if (isset($data['regionId'])) {
            $region = $this->entityManager->getRepository(Region::class)->find($data['regionId']);
            $existingHotel->updateRegion($region);
        }

        if (isset($data['lumpSumsId'])) {
            $lumpSums = $this->entityManager->getRepository(LumpSums::class)->find($data['lumpSumsId']);
            $existingHotel->updateLumpSums($lumpSums);
        }

        if (isset($data['lumpSumsExpireDate'])) {
            $newLumpSumsExpireDate = $data['lumpSumsExpireDate'];
            $existingHotel->updateLumpSumsExpireDate($newLumpSumsExpireDate);
        }

        if (isset($data['newLumpSumsId'])) {
            $newLumpSums = $this->entityManager->getRepository(LumpSums::class)->find($data['newLumpSumsId']);
            $existingHotel->updateNewLumpSums($newLumpSums);
        }

        $existingHotel->updateUpdateDate();

        $this->entityManager->flush();

    }
}