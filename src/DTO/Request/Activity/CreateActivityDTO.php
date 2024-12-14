<?php

namespace App\DTO\Request\Activity;

use Symfony\Component\Validator\Constraints as Assert;

class CreateActivityDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public int $emptySlot;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $location;

    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public float $price;

    public function __construct(string $name, int $emptySlot, string $location, ?string $description, float $price)
    {
        $this->name = $name;
        $this->emptySlot = $emptySlot;
        $this->location = $location;
        $this->description = $description;
        $this->price = $price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmptySlot(): int
    {
        return $this->emptySlot;
    }

    public function getLocation(): string
    {
        return $this->location;
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

    public function setEmptySlot(int $emptySlot): void
    {
        $this->emptySlot = $emptySlot;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'emptySlot' => $this->emptySlot,
            'location' => $this->location,
            'description' => $this->description,
            'price' => $this->price,
        ];
    }
}
