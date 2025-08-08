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
        $this->entityManager->persist($fixedPrice);
        $this->entityManager->flush();
    }
}