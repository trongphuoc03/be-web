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
        list($token, $check) = $this->checkAuthor($request);
        if (!$check) {
            return $this->json(['message' => 'Đăng nhập trước'], Response::HTTP_UNAUTHORIZED);
        }
        $userId = $this->jWTService->getIdFromToken($token);
        $data = json_decode($request->getContent(), true);
        $flight = $this->flightService->getFlightById($data['flightId'] ?? null);
        $hotel = $this->hotelService->getHotelById($data['hotelId'] ?? null);
        $activity = $this->activityService->getActivityById($data['activityId'] ?? null);
        $combo = $this->comboService->getComboById($data['comboId'] ?? null);
        $promo = $this->promoService->getPromoById($data['promoId'] ?? null);
        if (!$flight && !$hotel && !$activity && !$combo) {
            return $this->json(['message' => 'Phải chọn (flight, hotel, activity) hoặc combo'], Response::HTTP_NOT_FOUND);
        }
        if ($combo && ($flight || $hotel || $activity)) {
            return $this->json(['message' => 'Chỉ chọn (flight, hotel, activity) hoặc combo'], Response::HTTP_BAD_REQUEST);
        }
        if ($combo) {
            $totalPrice = $combo->getPrice() * $data['quantity'];
        } else {
            if ($hotel){
                if (!$data['checkInDate'] || !$data['checkOutDate']) {
                    return $this->json(['message' => 'Ngày nhận phòng và ngày trả phòng là bắt buộc'], Response::HTTP_BAD_REQUEST);
                }
                $checkInDate = new \DateTime($data['checkInDate']);
                $checkOutDate = new \DateTime($data['checkOutDate']);
                if ($checkInDate > $checkOutDate) {
                    return $this->json(['message' => 'Ngày nhận phòng phải trước ngày trả phòng'], Response::HTTP_BAD_REQUEST);
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
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
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
        list($token, $check) = $this->checkAuthor($request);
        if (!$check) {
            return $this->json(['message' => 'Đăng nhập trước'], Response::HTTP_UNAUTHORIZED);
        }
        $admin = $this->checkAdminRole($request);
        $booking = $this->bookingService->getBookingById($id);
        if (!$booking) {
            return $this->json(['message' => 'Không tìm thấy booking'], Response::HTTP_NOT_FOUND);
        }
        $result = (new BookingResponseDTO($booking))->toArray();
        if ($admin || $result['userId'] === $this->jWTService->getIdFromToken($token)) {
            return $this->json($result);
        }
        return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
    }
    #[Route(self::BOOKING_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $admin = $this->checkAdminRole($request);
        if (!$admin) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
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
        $admin = $this->checkAdminRole($request);
        if (!$admin) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $this->bookingService->deleteBooking($id);

        return $this->json(['message' => 'Xóa booking thành công'], Response::HTTP_OK);
    }

    private function checkAdminRole(Request $request)
    {
       // Tách token từ header
       list($token, $check) = $this->checkAuthor($request);

       // Kiểm tra role
       if (!$this->jWTService->isAdmin($token)) {
        $check = false;
       }
        return $check;
    }
    

    private function checkAuthor(Request $request){
        $authorizationHeader = $request->headers->get('Authorization');
        $check = true;
       // Kiểm tra nếu không có header hoặc header không đúng định dạng
       if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
        $check = false;
       }

       return [$matches[1], $check];
    }
}
