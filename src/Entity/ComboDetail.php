<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource] // Kích hoạt API Platform cho Entity này
#[ORM\Entity]
#[ORM\Table(name: "combodetail")]
class ComboDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Combo::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Combo $combo;

    #[ORM\ManyToOne(targetEntity: Flight::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?Flight $flight = null;

    #[ORM\ManyToOne(targetEntity: Hotel::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?Hotel $hotel = null;

    #[ORM\ManyToOne(targetEntity: Activity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?Activity $activity = null;

    // Getter methods
    public function getComboDetailId(): int
    {
        return $this->id;
    }

    public function getCombo(): Combo
    {
        return $this->combo;
    }

    public function getFlight(): ?Flight
    {
        return $this->flight;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    // Setter methods
    public function setCombo(Combo $combo): void
    {
        $this->combo = $combo;
    }

    public function setFlight(?Flight $flight): void
    {
        $this->flight = $flight;
    }

    public function setHotel(?Hotel $hotel): void
    {
        $this->hotel = $hotel;
    }

    public function setActivity(?Activity $activity): void
    {
        $this->activity = $activity;
    }
}
