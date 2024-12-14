<?php

namespace App\Controller;

use App\DTO\Request\Booking\CreateBookingDTO;
use App\DTO\Request\Booking\UpdateBookingDTO;
use App\DTO\Response\Booking\BookingResponseDTO;
use App\Service\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    private const BOOKING_ROUTE = '/bookings/{id}';
    public function __construct(private BookingService $bookingService) {}

    #[Route('/bookings', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreateBookingDTO(
            userId: $data['userId'] ?? null,
            promoId: $data['promoId'] ?? null,
            totalPrice: $data['totalPrice'] ?? null,
            status: $data['status'] ?? null
        );
        $booking = $this->bookingService->createBooking($dto);

        return $this->json(new BookingResponseDTO($booking), Response::HTTP_CREATED);
    }
    #[Route('/bookings/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $bookings = $this->bookingService->getAllBookings();
        $response = [];

        foreach ($bookings as $booking) {
            $response[] = (new BookingResponseDTO($booking))->toArray();
        }

        return $this->json($response);
    }
    #[Route(self::BOOKING_ROUTE, methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingById($id);

        if (!$booking) {
            return $this->json(['message' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json((new BookingResponseDTO($booking))->toArray());
    }
    #[Route(self::BOOKING_ROUTE, methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateBookingDTO(
            totalPrice: $data['totalPrice'] || null,
            status: $data['status'] || null
        );

        $booking = $this->bookingService->updateBooking($id, $dto);

        return $this->json(new BookingResponseDTO($booking));
    }
    #[Route(self::BOOKING_ROUTE, methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->bookingService->deleteBooking($id);

        return $this->json(['message' => 'Booking deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
