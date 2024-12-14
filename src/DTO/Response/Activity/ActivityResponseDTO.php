<?php

namespace App\DTO\Response\Activity;

use App\Entity\Activity;

class ActivityResponseDTO
{
    private int $activityId;
    private string $name;
    private int $emptySlot;
    private string $location;
    private string $description;
    private float $price;

    public function __construct(Activity $activity)
    {
        $this->activityId = $activity->getActivityId();
        $this->name = $activity->getName();
        $this->emptySlot = $activity->getEmptySlot();
        $this->location = $activity->getLocation();
        $this->description = $activity->getDescription();
        $this->price = $activity->getPrice();
    }

    public function toArray(): array
    {
        return [
            'activityId' => $this->activityId,
            'name' => $this->name,
            'emptySlot' => $this->emptySlot,
            'location' => $this->location,
            'description' => $this->description,
            'price' => $this->price
        ];
    }
}
