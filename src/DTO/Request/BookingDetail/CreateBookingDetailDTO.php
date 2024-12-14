<?php

namespace App\DTO\Request\BookingDetail;

use Symfony\Component\Validator\Constraints as Assert;

class CreateBookingDetailDTO
{
    #[Assert\NotBlank]
    public int $bookingId;

    public ?int $flightId = null;
    public ?int $hotelId = null;
    public ?int $activityId = null;
    public ?int $comboId = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $quantity;

    #[Assert\NotBlank]
    public \DateTimeInterface $checkInDate;

    #[Assert\GreaterThan(propertyPath: 'checkInDate')]
    public \DateTimeInterface $checkOutDate;

    public function __construct(int $bookingId, ?int $flightId, ?int $hotelId, ?int $activityId, ?int $comboId, int $quantity, \DateTimeInterface $checkInDate, \DateTimeInterface $checkOutDate)
    {
        $this->bookingId = $bookingId;
        $this->flightId = $flightId;
        $this->hotelId = $hotelId;
        $this->activityId = $activityId;
        $this->comboId = $comboId;
        $this->quantity = $quantity;
        $this->checkInDate = $checkInDate;
        $this->checkOutDate = $checkOutDate;
    }

    public function getBookingId(): int
    {
        return $this->bookingId;
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

    public function getComboId(): ?int
    {
        return $this->comboId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getCheckInDate(): \DateTimeInterface
    {
        return $this->checkInDate;
    }

    public function getCheckOutDate(): \DateTimeInterface
    {
        return $this->checkOutDate;
    }

    public function setBookingId(int $bookingId): void
    {
        $this->bookingId = $bookingId;
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

    public function setComboId(?int $comboId): void
    {
        $this->comboId = $comboId;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setCheckInDate(\DateTimeInterface $checkInDate): void
    {
        $this->checkInDate = $checkInDate;
    }

    public function setCheckOutDate(\DateTimeInterface $checkOutDate): void
    {
        $this->checkOutDate = $checkOutDate;
    }

    public function toArray(): array
    {
        return [
            'bookingId' => $this->bookingId,
            'flightId' => $this->flightId,
            'hotelId' => $this->hotelId,
            'activityId' => $this->activityId,
            'comboId' => $this->comboId,
            'quantity' => $this->quantity,
            'checkInDate' => $this->checkInDate->format('Y-m-d'),
            'checkOutDate' => $this->checkOutDate->format('Y-m-d'),
        ];
    }
}
