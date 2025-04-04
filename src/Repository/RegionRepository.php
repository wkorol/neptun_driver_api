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
            throw new \PDOException('Rejon o podanym ID już istnieje.');
        }
        $this->getEntityManager()->persist($region);
        $this->getEntityManager()->flush();
    }

    public function removeRegion(int $id): void
    {
        $region = $this->findOneBy(['id' => $id]);
        $this->getEntityManager()->remove($region);
        $this->getEntityManager()->flush();
    }

    public function editRegion(int $id, mixed $data): void
    {
        $region = $this->findOneBy(['id' => $id]);
        if ($region) {
            $region->setName($data['name']);
        }
        $this->getEntityManager()->flush();
    }


}
