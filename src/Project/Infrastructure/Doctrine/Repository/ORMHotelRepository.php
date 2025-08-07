<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine\Repository;

use App\Hotel\Domain\Hotel;
use App\Hotel\Repository\HotelRepository;
use App\LumpSums\Domain\LumpSums;
use App\Region\Domain\Region;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

readonly class ORMHotelRepository implements HotelRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function all(): array
    {
        $hotels = $this->entityManager->getRepository(Hotel::class)->findAll();
        return $hotels;
    }

    public function getByRegionId(int $regionId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('h', 'ls', 'nls', 'r')
            ->from(Hotel::class, 'h')
            ->leftJoin('h.lump_sums', 'ls')
            ->leftJoin('h.new_lump_sums', 'nls')
            ->leftJoin('h.region', 'r')
            ->where('r.id = :regionId')
            ->setParameter('regionId', $regionId)
            ->getQuery()
            ->getResult();
    }

    public function findById(Uuid $id): ?Hotel
    {
        return $this->entityManager->getRepository(Hotel::class)->findOneBy(['id' => $id]);
    }

    public function findByName(string $name): ?Hotel
    {
        return $this->entityManager->getRepository(Hotel::class)->findOneBy(['name' => $name]);
    }

    public function add(Hotel $hotel): void
    {
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();
    }

    public function remove(Hotel $hotel): void
    {
        $this->entityManager->remove($hotel);
        $this->entityManager->flush();
    }

    public function updateHotel(Hotel $existingHotel, array $data): void
    {
        if (isset($data['name'])) {
            $this->checkIfExists($data['name']);
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

    public function checkIfExists(string $name): void
    {
        /**
         * @var Hotel|null $hotel
         */
        $hotel = $this->findByName($name);

        if ($hotel) {
            throw new \PDOException('Hotel o podanej nazwie już istnieje w systemie.');
        }
    }

}