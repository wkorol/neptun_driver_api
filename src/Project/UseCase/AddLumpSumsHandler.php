<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\LumpSums\Repository\LumpSumsRepository;
use App\Project\UseCase\AddLumpSums\Command;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class AddLumpSumsHandler
{
    public function __construct(
        private LumpSumsRepository $lumpSumsRepository,
    ) {
    }

    public function __invoke(Command $command): void
    {
        if (null !== $this->lumpSumsRepository->find($command->lumpSums->getId())) {
            throw new \PDOException('Ryczałty o podanym ID już istnieją.');
        }
        $this->lumpSumsRepository->addLumpSums($command->lumpSums);
    }
}
