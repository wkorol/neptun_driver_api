<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\DTO\FixedPrice;
use App\DTO\Tariff;
use App\LumpSums\Repository\LumpSumsRepository;
use App\Project\UseCase\UpdateLumpSums\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateLumpSumsHandler
{
    public function __construct(
        private LumpSumsRepository $lumpSumsRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Command $command): void
    {
        $existingLumpSums = $this->lumpSumsRepository->find($command->id);
        if (!$existingLumpSums) {
            throw new \PDOException('Nie znaleziono ryczałtów');
        }
        $data = $command->data;
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