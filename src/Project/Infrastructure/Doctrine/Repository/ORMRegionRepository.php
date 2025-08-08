<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine\Repository;

use App\Region\Domain\Region;
use App\Region\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ORMRegionRepository implements RegionRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function all(): array
    {
        return $this->entityManager->getRepository(Region::class)->findBy([], ['position' => 'ASC']);
    }

    public function findById(int $id): ?Region
    {
        return $this->entityManager->getRepository(Region::class)->findOneBy(['id' => $id]);
    }

    public function add(Region $region): void
    {
        $this->entityManager->persist($region);
        $this->entityManager->flush();
    }

    public function remove(int $id): void
    {
        $region = $this->entityManager->getRepository(Region::class)->findOneBy(['id' => $id]);
        $this->entityManager->remove($region);
        $this->entityManager->flush();
    }
}
