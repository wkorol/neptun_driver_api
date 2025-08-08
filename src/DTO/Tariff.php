<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @phpstan-type TariffArray array{
 *     tariffType: TariffType,
 *     carValue: int,
 *     bus5_6Value: int,
 *     bus7_8Value: ?int
 * }
 */
readonly class Tariff implements \JsonSerializable
{
    public function __construct(
        private TariffType $tariffType,
        private int $carValue,
        private int $bus5_6Value,
        private ?int $bus7_8Value,
    ) {
    }

    /**
     * @param array{
     *     tariffType: int,
     *     carValue: int,
     *     bus5_6Value: int,
     *     bus7_8Value: ?int
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            TariffType::tryFrom($data['tariffType']),
            $data['carValue'],
            $data['bus5_6Value'],
            $data['bus7_8Value'],
        );
    }

    /**
     * @return TariffArray
     */
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
