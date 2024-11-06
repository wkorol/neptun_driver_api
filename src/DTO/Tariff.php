<?php

declare(strict_types=1);

namespace App\DTO;

class Tariff implements \JsonSerializable
{
    public function __construct(
        private readonly TariffType $tariffType,
        private readonly int        $carValue,
        private readonly int        $busValue,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            TariffType::tryFrom($data['tariffType']),
            $data['carValue'],
            $data['busValue'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'tariffType' => $this->tariffType,
            'carValue' => $this->carValue,
            'busValue' => $this->busValue,
        ];
    }
}