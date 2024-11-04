<?php

namespace App\Repository;

use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Region>
 */
class RegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    public function addRegion(Region $region): void
    {
        if ($this->find($region->getId()) !== null) {
            throw new \PDOException('Region already exists.');
        }
        $this->getEntityManager()->persist($region);
        $this->getEntityManager()->flush();
    }
}
