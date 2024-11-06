<?php

namespace App\Repository;

use App\Entity\LumpSums;
use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LumpSums>
 */
class LumpSumsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LumpSums::class);
    }

    public function addLumpSums(LumpSums $fixedPrice): void
    {
        if ($this->find($fixedPrice->getId()) !== null) {
            throw new \PDOException('Region already exists.');
        }
        $this->getEntityManager()->persist($fixedPrice);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return FixedPrice[] Returns an array of FixedPrice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FixedPrice
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
