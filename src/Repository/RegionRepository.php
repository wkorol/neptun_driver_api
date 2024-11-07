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

    public function getHotelsByRegion(int $id): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.hotels', 'h')            // Join the hotels associated with the region
            ->leftJoin('h.lump_sums', 'ls')         // Join the `lump_sums` association in `Hotel`
            ->leftJoin('h.new_lump_sums', 'nls')    // Join the `new_lump_sums` association in `Hotel`
            ->addSelect('h', 'ls', 'nls')           // Select hotels and lump sums
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult();
    }



}
