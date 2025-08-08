<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine\Repository;

use App\Hotel\Domain\Hotel;
use App\Hotel\Repository\HotelRepository;
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
        return $this->entityManager->getRepository(Hotel::class)->findAll();
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

    public function checkIfExists(string $name): bool
    {
        /**
         * @var Hotel|null $hotel
         */
        $hotel = $this->findByName($name);
        if ($hotel) {
            return true;
        }

        return false;
    }

}