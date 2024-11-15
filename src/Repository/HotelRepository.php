<?php

namespace App\Repository;

use App\Entity\Hotel;
use App\Entity\LumpSums;
use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Hotel>
 */
class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    public function addHotel(Hotel $hotel): void
    {
        $this->checkIfExists($hotel->getName());
        $this->getEntityManager()->persist($hotel);
        $this->getEntityManager()->flush();
    }

    public function updateHotel(Hotel $existingHotel, array $data): void
    {
        if (isset($data['name'])) {
            $this->checkIfExists($data['name'], $existingHotel->getId());
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

    public function checkIfExists(string $name, ?Uuid $id = null): void
    {
        /**
         * @var Hotel|null $hotel
         */
        $hotel = $this->findOneBy(['name' => $name]);

        if ($hotel && ($id === null || !$hotel->getId()->equals($id))) {
            throw new \PDOException('Hotel already exists!');
        }
    }

    /**
     * @return Hotel[]
     */
    public function getHotelsByRegion(int $id): array
    {
        return $this->createQueryBuilder('h')
            ->leftJoin('h.lump_sums', 'ls')
            ->leftJoin('h.region', 'r')
            ->leftJoin('h.new_lump_sums', 'nls')        // Join the `new_lump_sums` association in `Hotel`
            ->addSelect('ls', 'nls', 'r')                    // Select lump sums
            ->where('h.region = :id')                   // Filter by region ID
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult();
    }

    public function removeHotel(Uuid $id): void
    {
        $hotel = $this->findOneBy(['id' => $id]);
        if (!$hotel) {
            throw new \PDOException('Hotel not found!');
        }
        $this->getEntityManager()->remove($hotel);
        $this->getEntityManager()->flush();
    }


}
