<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine\Repository;

use App\TaxiService\Domain\Service;
use App\TaxiService\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;

class ORMServiceRepository implements ServiceRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return Service[]
     */
    public function all(): array
    {
        return $this->entityManager->getRepository(Service::class)->findAll();
    }
}
