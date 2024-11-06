<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class LumpSums
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'json')]
    private array $fixedValues;

    public function __construct(string $name, array $fixedValues)
    {
        $this->id = Uuid::v4();
        $this->name = $name;
        $this->fixedValues = $fixedValues;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFixedValues(): array
    {
        return $this->fixedValues;
    }
}
