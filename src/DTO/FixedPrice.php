<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @phpstan-import-type TariffArray from Tariff
 *
 * @phpstan-type FixedPriceArray array{
 *     name: string,
 *     tariff1: TariffArray,
 *     tariff2: TariffArray
 * }
 */
readonly class FixedPrice implements \JsonSerializable
{
    public function __construct(
        private string $name,
        private Tariff $tariff1,
        private Tariff $tariff2,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTariff1(): Tariff
    {
        return $this->tariff1;
    }

    public function getTariff2(): Tariff
    {
        return $this->tariff2;
    }

    /**
     * @return FixedPriceArray
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'tariff1' => $this->tariff1->jsonSerialize(),
            'tariff2' => $this->tariff2->jsonSerialize(),
        ];
    }
}
