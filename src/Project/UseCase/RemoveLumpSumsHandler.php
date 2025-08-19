<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\LumpSums\Repository\LumpSumsRepository;
use App\Project\UseCase\RemoveLumpSums\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class RemoveLumpSumsHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LumpSumsRepository $lumpSumsRepository,
    ) {
    }

    public function __invoke(Command $command): void
    {
        $lumpSums = $this->lumpSumsRepository->find($command->id);
        if ($lumpSums) {
            $this->entityManager->remove($lumpSums);
            $this->entityManager->flush();
        }
    }
}
