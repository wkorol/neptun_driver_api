<?php

declare(strict_types=1);

namespace App\TaxiService\Domain;

use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type ServiceArray array{
 *     id: string,
 *     name: ?string,
 *     description: ?string,
 *     price: ?string
 * }
 */
class Service implements \JsonSerializable
{
    public function __construct(
        private Uuid $id,
        private ?string $name = null,
        private ?string $description = null,
        private ?string $price = null,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): void
    {
        $this->price = $price;
    }

    /**
     * @return ServiceArray
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
        ];
    }
}
