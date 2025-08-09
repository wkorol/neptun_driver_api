<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine\Repository;

use App\Region\Domain\Region;
use App\Region\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ORMRegionRepository implements RegionRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
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

    public function addRegion(Region $region): void
    {
        if ($this->entityManager->getRepository(Region::class)->find($region->getId()) !== null) {
            throw new \PDOException('Rejon o podanym ID już istnieje.');
        }
        $this->entityManager->persist($region);
        $this->entityManager->flush();
    }

    public function removeRegion(int $id): void
    {
        $region = $this->entityManager->getRepository(Region::class)->findOneBy(['id' => $id]);
        $this->entityManager->remove($region);
        $this->entityManager->flush();
    }

    public function editRegion(int $id, mixed $data): void
    {
        $region = $this->entityManager->getRepository(Region::class)->findOneBy(['id' => $id]);
        if ($region) {
            $region->setName($data['name']);
        }
        $this->entityManager->flush();
    }
}