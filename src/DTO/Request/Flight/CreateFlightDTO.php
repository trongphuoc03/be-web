<?php

namespace App\DTO\Request\Flight;

use Symfony\Component\Validator\Constraints as Assert;

class CreateFlightDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $brand;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public int $emptySlot;

    #[Assert\NotBlank]
    public \DateTimeInterface $startTime;

    #[Assert\NotBlank]
    #[Assert\GreaterThan(propertyPath: 'startTime')]
    public \DateTimeInterface $endTime;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $startLocation;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $endLocation;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public float $price;

    public function __construct(string $brand, int $emptySlot, \DateTimeInterface $startTime, \DateTimeInterface $endTime, string $startLocation, string $endLocation, float $price)
    {
        $this->brand = $brand;
        $this->emptySlot = $emptySlot;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->startLocation = $startLocation;
        $this->endLocation = $endLocation;
        $this->price = $price;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getEmptySlot(): int
    {
        return $this->emptySlot;
    }

    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    public function getEndTime(): \DateTimeInterface
    {
        return $this->endTime;
    }

    public function getStartLocation(): string
    {
        return $this->startLocation;
    }

    public function getEndLocation(): string
    {
        return $this->endLocation;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function setEmptySlot(int $emptySlot): void
    {
        $this->emptySlot = $emptySlot;
    }

    public function setStartTime(\DateTimeInterface $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function setStartLocation(string $startLocation): void
    {
        $this->startLocation = $startLocation;
    }

    public function setEndLocation(string $endLocation): void
    {
        $this->endLocation = $endLocation;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}
