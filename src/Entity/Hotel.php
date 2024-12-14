<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource] // Kích hoạt API Platform cho Entity này
#[ORM\Entity]
#[ORM\Table(name: "hotel")]
class Hotel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    private string $location;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'integer')]
    private int $emptyRoom;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $price;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    // Getter methods
    public function getHotelId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmptyRoom(): int
    {
        return $this->emptyRoom;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    // Setter methods
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function setEmptyRoom(int $emptyRoom): void
    {
        $this->emptyRoom = $emptyRoom;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
