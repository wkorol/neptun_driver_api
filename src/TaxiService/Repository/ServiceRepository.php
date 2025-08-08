<?php

declare(strict_types=1);

namespace App\TaxiService\Repository;

use App\TaxiService\Domain\Service;

interface ServiceRepository
{
    /**
     * @return Service[]
     */
    public function all(): array;
}
