<?php

declare(strict_types=1);

namespace App\DTO;

class Tariff implements \JsonSerializable
{
    public function __construct(
        private readonly TariffType $tariffType,
        private readonly int        $carValue,
        private readonly int $bus5_6Value,
        private readonly ?int $bus7_8Value,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            TariffType::tryFrom($data['tariffType']),
            $data['carValue'],
            $data['bus5_6Value'],
            $data['bus7_8Value'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'tariffType' => $this->tariffType,
            'carValue' => $this->carValue,
            'bus5_6Value' => $this->bus5_6Value,
            'bus7_8Value' => $this->bus7_8Value,
        ];
    }
}