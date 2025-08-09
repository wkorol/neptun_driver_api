<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Project\UseCase\AddRegion\Command;
use App\Region\Domain\Region;
use App\Region\Repository\RegionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class AddRegionHandler
{
    public function __construct(private RegionRepository $regionRepository)
    {
    }

    public function __invoke(Command $command): Region
    {
        $region = new Region(
            $command->id,
            $command->name,
            $command->position,
        );

        if (null !== $this->regionRepository->findById($region->getId())) {
            throw new \PDOException('Rejon o podanym ID już istnieje.');
        }

        $this->regionRepository->add($region);

        return $region;
    }
}
