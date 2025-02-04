<?php

namespace App\Controller;

use App\DTO\Request\ComboDetail\CreateComboDetailDTO;
use App\DTO\Request\ComboDetail\UpdateComboDetailDTO;
use App\DTO\Response\Activity\ActivityResponseDTO;
use App\DTO\Response\Combo\ComboResponseDTO;
use App\DTO\Response\ComboDetail\ComboDetailResponseDTO;
use App\DTO\Response\Flight\FlightResponseDTO;
use App\DTO\Response\Hotel\HotelResponseDTO;
use App\Service\ComboDetailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JWTService;

class ComboDetailController extends AbstractController
{
    private const COMBO_DETAIL_ROUTE = '/combo-details/{id}';
    public function __construct(private ComboDetailService $comboDetailService, private JWTService $jWTService) {}

    #[Route('/combo-details/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $comboDetails = $this->comboDetailService->getAllComboDetails();
        $response = [];

        foreach ($comboDetails as $comboDetail) {
            $response[] = [
                'comboDetailId' => $comboDetail->getComboDetailId(),
                'combo' => $comboDetail->getCombo() ? (new ComboResponseDTO($comboDetail->getCombo()))->toArray() : null,
                'flight' => $comboDetail ? (new FlightResponseDTO($comboDetail->getFlight()))->toArray() : null,
                'hotel' => $comboDetail ? (new HotelResponseDTO($comboDetail->getHotel()))->toArray() : null,
                'activity' => $comboDetail ? (new ActivityResponseDTO($comboDetail->getActivity()))->toArray() : null,
            ];
        }

        return $this->json($response);
    }
    #[Route(self::COMBO_DETAIL_ROUTE, methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $comboDetail = $this->comboDetailService->getComboDetailById($id);

        if (!$comboDetail) {
            return $this->json(['message' => 'Combo Detail not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
                'comboDetailId' => $comboDetail->getComboDetailId(),
                'combo' => $comboDetail->getCombo() ? (new ComboResponseDTO($comboDetail->getCombo()))->toArray() : null,
                'flight' => $comboDetail ? (new FlightResponseDTO($comboDetail->getFlight()))->toArray() : null,
                'hotel' => $comboDetail ? (new HotelResponseDTO($comboDetail->getHotel()))->toArray() : null,
                'activity' => $comboDetail ? (new ActivityResponseDTO($comboDetail->getActivity()))->toArray() : null,
            ]);
    }
    #[Route(self::COMBO_DETAIL_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateComboDetailDTO(flightId: $data['flightId'], hotelId: $data['hotelId'], activityId: $data['activityId']);

        $comboDetail = $this->comboDetailService->updateComboDetail($id, $dto);

        return $this->json([
                'comboDetailId' => $comboDetail->getComboDetailId(),
                'combo' => $comboDetail->getCombo() ? (new ComboResponseDTO($comboDetail->getCombo()))->toArray() : null,
                'flight' => $comboDetail ? (new FlightResponseDTO($comboDetail->getFlight()))->toArray() : null,
                'hotel' => $comboDetail ? (new HotelResponseDTO($comboDetail->getHotel()))->toArray() : null,
                'activity' => $comboDetail ? (new ActivityResponseDTO($comboDetail->getActivity()))->toArray() : null,
            ]);
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
