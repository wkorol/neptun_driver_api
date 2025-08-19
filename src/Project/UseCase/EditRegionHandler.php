<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Project\UseCase\EditRegion\Command;
use App\Region\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class EditRegionHandler
{
    public function __construct(
        private RegionRepository $regionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Command $command): void
    {
        $region = $this->regionRepository->findById($command->id);
        $region?->setName($command->name);
        $this->entityManager->flush();
    }
}
