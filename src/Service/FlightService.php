<?php

namespace App\Service;

use App\DTO\Request\Flight\UpdateFlightDTO;
use App\DTO\Request\Flight\CreateFlightDTO;
use App\Entity\Flight;
use Doctrine\ORM\EntityManagerInterface;

class FlightService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createFlight(CreateFlightDTO $flightDTO): Flight
    {
        $flight = new Flight();
        // Assuming CreateFlightDTO has methods to get the necessary data
        $flight->setBrand($flightDTO->getBrand());
        $flight->setEmptySlot($flightDTO->getEmptySlot());
        $flight->setStartTime($flightDTO->getStartTime());
        $flight->setEndTime($flightDTO->getEndTime());
        $flight->setStartLocation($flightDTO->getStartLocation());
        $flight->setEndLocation($flightDTO->getEndLocation());
        $flight->setPrice($flightDTO->getPrice());
        // Add other properties as needed

        $this->entityManager->persist($flight);
        $this->entityManager->flush();
        return $flight;
    }

    public function getAllFlights(): array
    {
        return $this->entityManager->getRepository(Flight::class)->findAll();
    }

    public function getFlightById(int $id): ?Flight
    {
        return $this->entityManager->getRepository(Flight::class)->find($id);
    }

    public function updateFlight(int $id, UpdateFlightDTO $flightDTO): ?Flight
    {
        $flight = $this->getFlightById($id);
        if (!$flight) {
            return null;
        }

        // Assuming UpdateFeedbackDTO has methods to get the necessary data
        $flight->setBrand($flightDTO->getBrand());
        $flight->setEmptySlot($flightDTO->getEmptySlot());
        $flight->setStartTime($flightDTO->getStartTime());
        $flight->setEndTime($flightDTO->getEndTime());
        $flight->setStartLocation($flightDTO->getStartLocation());
        $flight->setEndLocation($flightDTO->getEndLocation());
        $flight->setPrice($flightDTO->getPrice());
        // Add other properties as needed

        $this->entityManager->flush();
        return $flight;
    }

    public function deleteFlight(int $id): void
    {
        $flight = $this->getFlightById($id);
        if ($flight) {
            $this->entityManager->remove($flight);
            $this->entityManager->flush();
        }
    }
}
