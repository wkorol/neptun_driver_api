<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Project\UseCase\RemoveRegion\Command;
use App\Region\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveRegionHandler
{
    public function __construct(
        private RegionRepository $regionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Command $command): void
    {
        $region = $this->regionRepository->findById($command->id);
        if ($region) {
            $this->entityManager->remove($region);
            $this->entityManager->flush();
        }
    }
}
