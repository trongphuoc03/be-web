<?php

namespace App\DTO\Response\Combo;

use App\Entity\Combo;

class ComboResponseDTO
{
    public int $id;
    public string $name;
    public ?string $description;
    public float $price;

    // Constructor to initialize DTO from a Combo entity
    public function __construct(Combo $combo)
    {
        $this->id = $combo->getComboId(); // assuming getComboId() exists
        $this->name = $combo->getName();
        $this->description = $combo->getDescription();
        $this->price = $combo->getPrice();
    }

    // Convert DTO to array format (for easy JSON response)
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
        ];
    }
}
