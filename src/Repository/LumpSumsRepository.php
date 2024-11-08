<?php

namespace App\Repository;

use App\DTO\FixedPrice;
use App\DTO\Tariff;
use App\Entity\LumpSums;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

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

    public function removeLumpSums(Uuid $id): void
    {
        $lumpSums = $this->findOneBy(['id' => $id]);
        $this->getEntityManager()->remove($lumpSums);
        $this->getEntityManager()->flush();

    }

    public function updateLumpSums(LumpSums $existingLumpSums, array $data): void
    {
        if (isset($data['name'])) {
            $existingLumpSums->setName($data['name']);
        }

        if (isset($data['fixedValues'])) {
            // Convert each item in 'fixedValues' to a FixedPrice object
            $fixedValues = array_map(
                fn($valueData) => new FixedPrice(
                    $valueData['name'],
                    Tariff::fromArray($valueData['tariff1']),
                    Tariff::fromArray($valueData['tariff2'])
                ),
                $data['fixedValues']
            );
            $existingLumpSums->setFixedValues($fixedValues);
        }

        $this->getEntityManager()->flush();
    }

}
