<?php

namespace App\DTO\Request\ComboDetail;

class UpdateComboDetailDTO
{
    public ?int $flightId = null;

    public ?int $hotelId = null;

    public ?int $activityId = null;

    public function __construct(?int $flightId, ?int $hotelId, ?int $activityId)
    {
        $this->flightId = $flightId;
        $this->hotelId = $hotelId;
        $this->activityId = $activityId;
    }

    public function getFlightId(): ?int
    {
        return $this->flightId;
    }

    public function getHotelId(): ?int
    {
        return $this->hotelId;
    }

    public function getActivityId(): ?int
    {
        return $this->activityId;
    }

    public function setFlightId(?int $flightId): void
    {
        $this->flightId = $flightId;
    }

    public function setHotelId(?int $hotelId): void
    {
        $this->hotelId = $hotelId;
    }

    public function setActivityId(?int $activityId): void
    {
        $this->activityId = $activityId;
    }
}
