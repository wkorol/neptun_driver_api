<?php

declare(strict_types=1);

namespace App\Project\UseCase\AddRegion;

use App\Hotel\Domain\Hotel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

readonly class Command
{
    /**
     * @param Collection<int, Hotel> $hotels
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?int $position = null,
        public Collection $hotels = new ArrayCollection(),
    ) {
    }
}
