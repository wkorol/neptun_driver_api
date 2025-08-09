<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine\Repository;

use App\DTO\FixedPrice;
use App\DTO\Tariff;
use App\LumpSums\Domain\LumpSums;
use App\LumpSums\Repository\LumpSumsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

readonly class ORMLumpSumsRepository implements LumpSumsRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return LumpSums[]
     */
    public function all(): array
    {
        return $this->entityManager->getRepository(LumpSums::class)->findAll();
    }

    public function find(Uuid $id): ?LumpSums
    {
        return $this->entityManager->getRepository(LumpSums::class)->findOneBy(['id' => $id]);
    }

    public function addLumpSums(LumpSums $fixedPrice): void
    {
        if ($this->entityManager->getRepository(LumpSums::class)->find($fixedPrice->getId()) !== null) {
            throw new \PDOException('Ryczałty o podanym ID już istnieją.');
        }
        $this->entityManager->persist($fixedPrice);
        $this->entityManager->flush();
    }

    public function removeLumpSums(Uuid $id): void
    {
        $lumpSums = $this->entityManager->getRepository(LumpSums::class)->findOneBy(['id' => $id]);
        $this->entityManager->remove($lumpSums);
        $this->entityManager->flush();
    }

    public function updateLumpSums(LumpSums $existingLumpSums, array $data): void
    {
        if (isset($data['name'])) {
            $existingLumpSums->setName($data['name']);
        }

        if (isset($data['fixedValues'])) {
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

        $this->entityManager->flush();
    }
}