<?php

declare(strict_types=1);

namespace App\DTO;

class FixedPrice implements \JsonSerializable
{
    public function __construct(
        private readonly string $name,
        private readonly Tariff $tariff1,
        private readonly Tariff $tariff2,
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getTariff1(): array
    {
        return $this->tariff1->jsonSerialize();
    }

    public function getTariff2(): array
    {
        return $this->tariff2->jsonSerialize();
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'tariff1' => $this->getTariff1(),
            'tariff2' => $this->getTariff2()
        ];
    }
}