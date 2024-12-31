<?php

namespace App\Controller;

use App\DTO\Request\Flight\CreateFlightDTO;
use App\DTO\Request\Flight\UpdateFlightDTO;
use App\DTO\Response\Flight\FlightResponseDTO;
use App\Service\FlightService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JWTService;
use DateTime;
class FlightController extends AbstractController
{
    private const FLIGHT_ROUTE = '/flights/{id}';
    public function __construct(private FlightService $flightService, private JWTService $jWTService) {}

    #[Route('/flights', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        // Đảm bảo thời gian đầu vào sử dụng múi giờ UTC
        $startTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['startTime'], new \DateTimeZone('UTC'));
        $endTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['endTime'], new \DateTimeZone('UTC'));

        if (!$startTime || !$endTime) {
            return $this->json(['message' => 'Thời gian không hợp lệ'], Response::HTTP_BAD_REQUEST);
        }

        $dto = new CreateFlightDTO(
            brand: $data['brand'],
            emptySlot: (int)$data['emptySlot'],
            startTime: $startTime,
            endTime: $endTime,
            startLocation: $data['startLocation'],
            endLocation: $data['endLocation'],
            price: (float)$data['price']
        );

        $flight = $this->flightService->createFlight($dto);

        $response = (new FlightResponseDTO($flight))->toArray();

        return $this->json($response, Response::HTTP_CREATED);
    }


    #[Route('/flights/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $flights = $this->flightService->getAllFlights();
        $response = [];

        foreach ($flights as $flight) {
            $response[] = (new FlightResponseDTO($flight))->toArray();
        }

        return $this->json($response);
    }
    #[Route(self::FLIGHT_ROUTE, methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $flight = $this->flightService->getFlightById($id);

        if (!$flight) {
            return $this->json(['message' => 'Không tìm thấy chuyến bay'], Response::HTTP_NOT_FOUND);
        }

        return $this->json((new FlightResponseDTO($flight))->toArray());
    }
    #[Route(self::FLIGHT_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $startTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['startTime']);
        $endTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['endTime']);
        $dto = new UpdateFlightDTO(
            brand: $data['brand'],
            emptySlot: $data['emptySlot'],
            startTime: $startTime,
            endTime: $endTime,
            startLocation: $data['startLocation'],
            endLocation: $data['endLocation'],
            price: $data['price']
        );

        $flight = $this->flightService->updateFlight($id, $dto);

        return $this->json((new FlightResponseDTO($flight))->toArray());
    }
    #[Route(self::FLIGHT_ROUTE, methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $this->flightService->deleteFlight($id);

        return $this->json(['message' => 'Xóa chuyến bay thành công'], Response::HTTP_OK);
    }

    private function checkAdminRole(Request $request)
    {
       // Lấy header Authorization
       $authorizationHeader = $request->headers->get('Authorization');
       $check = true;
       // Kiểm tra nếu không có header hoặc header không đúng định dạng
       if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
           $check = false;
       }
       // Tách token từ header
       $token = $matches[1];

       // Kiểm tra role
       if (!$this->jWTService->isAdmin($token)) {
           $check = false;
       }
       return $check;
    }
}
