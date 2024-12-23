<?php

namespace App\Controller;

use App\DTO\Request\Combo\CreateComboDTO;
use App\DTO\Request\Combo\UpdateComboDTO;
use App\DTO\Request\ComboDetail\CreateComboDetailDTO;
use App\DTO\Response\Combo\ComboResponseDTO;
use App\DTO\Response\ComboDetail\ComboDetailResponseDTO;
use App\Service\ActivityService;
use App\Service\ComboDetailService;
use App\Service\ComboService;
use App\Service\FlightService;
use App\Service\HotelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JWTService;
class ComboController extends AbstractController
{
    private const COMBO_ROUTE = '/combos/{id}';
    public function __construct(
        private ComboService $comboService,
        private JWTService $jWTService, 
        private ComboDetailService $comboDetailService,
        private FlightService $flightService,
        private HotelService $hotelService,
        private ActivityService $activityService
    ) {}

    #[Route('/combos', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $dto = new CreateComboDTO(name: $data['name'], description: $data['description'], price: $data['price']);
        $combo = $this->comboService->createCombo($dto);
        $flight = $this->flightService->getFlightById($data['flightId']);
        $hotel = $this->hotelService->getHotelById($data['hotelId']);
        $activity = $this->activityService->getActivityById($data['activityId']);
        $dto = new CreateComboDetailDTO(comboId: $combo, flightId: $flight, hotelId: $hotel, activityId: $activity);
        $comboDetail = $this->comboDetailService->createComboDetail($dto);

        return $this->json(array_merge(
            (array) new ComboResponseDTO($combo),
            (array) new ComboDetailResponseDTO($comboDetail)
        ), Response::HTTP_CREATED);
        
    }
    #[Route('/combos/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $combos = $this->comboService->getAllCombos();
        $response = [];

        foreach ($combos as $combo) {
            $comboDetail = $this->comboDetailService->getComboDetailByComboId($combo->getComboId());
            $response[] = [
                'combo' => (new ComboResponseDTO($combo))->toArray(),
                'comboDetail' => (new ComboDetailResponseDTO($comboDetail))->toArray(),
            ];
        }

        return $this->json($response);
    }
    #[Route(self::COMBO_ROUTE, methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $combo = $this->comboService->getComboById($id);
        $comboDetail = $this->comboDetailService->getComboDetailByComboId($id);

        if (!$combo) {
            return $this->json(['message' => 'Combo not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['combo' => new ComboResponseDTO($combo), 'comboDetail' => new ComboDetailResponseDTO($comboDetail)]);
    }
    #[Route(self::COMBO_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateComboDTO(name: $data['name'], description: $data['description'], price: $data['price']);

        $combo = $this->comboService->updateCombo($id, $dto);

        return $this->json(new ComboResponseDTO($combo));
    }
    #[Route(self::COMBO_ROUTE, methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $this->comboService->deleteCombo($id);

        return $this->json(['message' => 'Combo deleted successfully'], Response::HTTP_OK);
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
