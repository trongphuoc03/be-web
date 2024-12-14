<?php

namespace App\DTO\Request\Combo;

use Symfony\Component\Validator\Constraints as Assert;

class CreateComboDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $name;

    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public float $price;

    public function __construct(string $name, ?string $description, float $price)
    {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}