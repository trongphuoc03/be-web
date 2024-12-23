<?php

namespace App\Controller;

use App\DTO\Request\Booking\CreateBookingDTO;
use App\DTO\Request\Booking\UpdateBookingDTO;
use App\DTO\Request\BookingDetail\CreateBookingDetailDTO;
use App\DTO\Response\Booking\BookingResponseDTO;
use App\DTO\Response\BookingDetail\BookingDetailResponseDTO;
use App\Enum\BookingStatus;
use App\Service\ActivityService;
use App\Service\BookingDetailService;
use App\Service\BookingService;
use App\Service\ComboService;
use App\Service\FlightService;
use App\Service\HotelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JWTService;
use App\Service\PromoService;

class BookingController extends AbstractController
{
    private const BOOKING_ROUTE = '/bookings/{id}';
    public function __construct(private BookingService $bookingService, 
    private JWTService $jWTService, 
    private BookingDetailService $bookingDetailService, 
    private FlightService $flightService,
    private HotelService $hotelService,
    private ActivityService $activityService,
    private ComboService $comboService,
    private PromoService $promoService
    ) {}

    #[Route('/bookings', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $token = $this->checkAuthor($request);
        $userId = $this->jWTService->getIdFromToken($token);
        $data = json_decode($request->getContent(), true);
        $flight = $this->flightService->getFlightById($data['flightId'] ?? null);
        $hotel = $this->hotelService->getHotelById($data['hotelId'] ?? null);
        $activity = $this->activityService->getActivityById($data['activityId'] ?? null);
        $combo = $this->comboService->getComboById($data['comboId'] ?? null);
        $promo = $this->promoService->getPromoById($data['promoId'] ?? null);
        if (!$flight && !$hotel && !$activity && !$combo) {
            return $this->json(['message' => 'Flight, hotel, activity or combo not found'], Response::HTTP_NOT_FOUND);
        }
        if ($combo && ($flight || $hotel || $activity)) {
            return $this->json(['message' => 'Chỉ chọn (flight, hotel, activity) hoặc combo'], Response::HTTP_BAD_REQUEST);
        }
        if ($combo) {
            $totalPrice = $combo->getPrice() * $data['quantity'];
        } else {
            if ($hotel){
                if (!$data['checkInDate'] || !$data['checkOutDate']) {
                    return $this->json(['message' => 'Check in date and check out date are required'], Response::HTTP_BAD_REQUEST);
                }
                $checkInDate = new \DateTime($data['checkInDate']);
                $checkOutDate = new \DateTime($data['checkOutDate']);
                if ($checkInDate > $checkOutDate) {
                    return $this->json(['message' => 'Check in date must be before check out date'], Response::HTTP_BAD_REQUEST);
                }
                $day = $checkOutDate->diff($checkInDate)->days;
            }
            $totalPrice = ($flight->getPrice() + $hotel->getPrice()* $day + $activity->getPrice())* $data['quantity'];
        }
        if ($promo) {
            $totalPrice = $totalPrice - $totalPrice * $promo->getDiscount() / 100;
        }
        $dto = new CreateBookingDTO(
            userId: $userId,
            promoId: $data['promoId'] ?? null,
            totalPrice: $totalPrice,
            status: BookingStatus::PENDING->value,
        );
        $booking = $this->bookingService->createBooking($dto);
        $dto = new CreateBookingDetailDTO(
                    bookingId: $booking->getBookingId(),
                    flightId: $data['flightId'] ?? null,
                    hotelId: $data['hotelId'] ?? null,
                    activityId: $data['activityId'] ?? null,
                    comboId: $data['comboId'] ?? null,
                    quantity: $data['quantity'],
                    checkInDate: $data['checkInDate'] ?? null,
                    checkOutDate: $data['checkOutDate'] ?? null,
                );
        $bookingDetail = $this->bookingDetailService->createBookingDetail($dto);

        return $this->json(
            array_merge(
                (array) new BookingResponseDTO($booking),
                (array) new BookingDetailResponseDTO($bookingDetail)
            ),
            Response::HTTP_CREATED
        );
        
    }
    #[Route('/bookings/bulk', methods: ['GET'])]
    public function bulkRead(Request $request): JsonResponse
    {
        $this->checkAdminRole($request);
        $bookings = $this->bookingService->getAllBookings();
        $response = [];

        foreach ($bookings as $booking) {
            $response[] = (new BookingResponseDTO($booking))->toArray();
        }

        return $this->json($response);
    }
    #[Route(self::BOOKING_ROUTE, methods: ['GET'])]
    public function read(int $id, Request $request): JsonResponse
    {
        $token = $this->checkAuthor($request);
        $booking = $this->bookingService->getBookingById($id);
        if (!$booking) {
            return $this->json(['message' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }
        $result = (new BookingResponseDTO($booking))->toArray();
        if (!$this->jWTService->isAdmin($token) || $result['userId'] === $this->jWTService->getIdFromToken($token)) {
            return $this->json($result);
        }
        return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }
    #[Route(self::BOOKING_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $this->checkAdminRole($request);
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateBookingDTO(
            totalPrice: $data['totalPrice'] || null,
            status: $data['status'] || null
        );

        $booking = $this->bookingService->updateBooking($id, $dto);

        return $this->json(new BookingResponseDTO($booking));
    }
    #[Route(self::BOOKING_ROUTE, methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $this->checkAdminRole($request);
        $this->bookingService->deleteBooking($id);

        return $this->json(['message' => 'Booking deleted successfully'], Response::HTTP_OK);
    }

    private function checkAdminRole(Request $request)
    {
       // Tách token từ header
       $token = $this->checkAuthor($request);

       // Kiểm tra role
       if (!$this->jWTService->isAdmin($token)) {
           throw new \Exception ('Unauthorized', Response::HTTP_UNAUTHORIZED);

       }
    }
    

    private function checkAuthor(Request $request){
        $authorizationHeader = $request->headers->get('Authorization');
        
       // Kiểm tra nếu không có header hoặc header không đúng định dạng
       if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            throw new \Exception ('Authorization header is missing or invalid', Response::HTTP_UNAUTHORIZED);
       }

       return $matches[1];
    }
}
