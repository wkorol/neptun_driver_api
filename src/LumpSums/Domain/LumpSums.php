<?php

declare(strict_types=1);

namespace App\LumpSums\Domain;

use Symfony\Component\Uid\Uuid;

class LumpSums implements \JsonSerializable
{
    public function __construct(
        private Uuid $id,
        private string $name,
        private array $fixedValues
    ) {}

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFixedValues(): array
    {
        return $this->fixedValues;
    }

    public function setFixedValues(array $fixedValues): void
    {
        $this->fixedValues = $fixedValues;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'fixedValues' => $this->fixedValues,
        ];
    }
}
