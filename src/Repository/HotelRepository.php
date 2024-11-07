<?php

namespace App\Repository;

use App\Entity\Hotel;
use App\Entity\LumpSums;
use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hotel>
 */
class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    public function addHotel(Hotel $hotel)
    {
        $this->getEntityManager()->persist($hotel);
        $this->getEntityManager()->flush();
    }

    public function updateHotel(Hotel $existingHotel, array $data): void
    {
        if (isset($data['name'])) {
            $existingHotel->updateName($data['name']);
        }

        if (isset($data['regionId'])) {
            $region = $this->getEntityManager()->getRepository(Region::class)->find($data['regionId']);
            $existingHotel->updateRegion($region);
        }

        if (isset($data['lumpSumsId'])) {
            $lumpSums = $this->getEntityManager()->getRepository(LumpSums::class)->find($data['lumpSumsId']);
            $existingHotel->updateLumpSums($lumpSums);
        }

        if (isset($data['lumpSumsExpireDate'])) {
            $newLumpSumsExpireDate = $data['lumpSumsExpireDate'];
            $existingHotel->updateLumpSumsExpireDate($newLumpSumsExpireDate);
        }

        if (isset($data['newLumpSumsId'])) {
            $newLumpSums = $this->getEntityManager()->getRepository(LumpSums::class)->find($data['newLumpSumsId']);
            $existingHotel->updateNewLumpSums($newLumpSums);
        }

        $existingHotel->updateUpdateDate();

        $this->getEntityManager()->flush();
    }

}
