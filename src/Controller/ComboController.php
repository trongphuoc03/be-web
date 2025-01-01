<?php

namespace App\Controller;

use App\DTO\Request\Combo\CreateComboDTO;
use App\DTO\Request\Combo\UpdateComboDTO;
use App\DTO\Request\ComboDetail\CreateComboDetailDTO;
use App\DTO\Response\Activity\ActivityResponseDTO;
use App\DTO\Response\Combo\ComboResponseDTO;
use App\DTO\Response\ComboDetail\ComboDetailResponseDTO;
use App\DTO\Response\Flight\FlightResponseDTO;
use App\DTO\Response\Hotel\HotelResponseDTO;
use App\Service\ActivityService;
use App\Service\ComboDetailService;
use App\Service\ComboService;
use App\Service\FileUploader;
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
        private ActivityService $activityService,
        private FileUploader $fileUploader,
    ) {}

    #[Route('/combos', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['error' => 'Không tìm thấy file'], Response::HTTP_BAD_REQUEST);
        }
        $fileName = $this->fileUploader->upload($file);
        $dto = new CreateComboDTO(name: $data['name'], imgUrl: $fileName, description: $data['description'], price: $data['price']);
        $combo = $this->comboService->createCombo($dto);
        $flight = $this->flightService->getFlightById($data['flightId']);
        $hotel = $this->hotelService->getHotelById($data['hotelId']);
        $activity = $this->activityService->getActivityById($data['activityId']);
        $dto = new CreateComboDetailDTO(comboId: $combo, flightId: $flight, hotelId: $hotel, activityId: $activity);
        $this->comboDetailService->createComboDetail($dto);
        $response = [
            'comboId' => $combo->getComboId(),
            'name' => $combo->getName(),
            'imgUrl' => $combo->getImgUrl(),
            'description' => $combo->getDescription(),
            'price' => $combo->getPrice(),
            'flight' => (new FlightResponseDTO($flight))->toArray(),
            'hotel' => (new HotelResponseDTO($hotel))->toArray(),
            'activity' => (new ActivityResponseDTO($activity))->toArray(),
        ];
        return $this->json( $response, Response::HTTP_CREATED);
        
    }
    #[Route('/combos/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $combos = $this->comboService->getAllCombos();
        $response = [];

        foreach ($combos as $combo) {
            $comboDetail = $this->comboDetailService->getComboDetailByComboId($combo->getComboId());
            $response[] = [
                'comboId' => $combo->getComboId(),
                'name' => $combo->getName(),
                'imgUrl' => $combo->getImgUrl(),
                'description' => $combo->getDescription(),
                'price' => $combo->getPrice(),
                'flight' => $comboDetail ? (new FlightResponseDTO($comboDetail->getFlight()))->toArray() : null,
                'hotel' => $comboDetail ? (new HotelResponseDTO($comboDetail->getHotel()))->toArray() : null,
                'activity' => $comboDetail ? (new ActivityResponseDTO($comboDetail->getActivity()))->toArray() : null,
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

        return $this->json([
                'comboId' => $combo->getComboId(),
                'name' => $combo->getName(),
                'imgUrl' => $combo->getImgUrl(),
                'description' => $combo->getDescription(),
                'price' => $combo->getPrice(),
                'flight' => $comboDetail ? (new FlightResponseDTO($comboDetail->getFlight()))->toArray() : null,
                'hotel' => $comboDetail ? (new HotelResponseDTO($comboDetail->getHotel()))->toArray() : null,
                'activity' => $comboDetail ? (new ActivityResponseDTO($comboDetail->getActivity()))->toArray() : null,
            ]);
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
        $comboDetail = $this->comboDetailService->getComboDetailByComboId($id);

        return $this->json([
                'comboId' => $combo->getComboId(),
                'name' => $combo->getName(),
                'imgUrl' => $combo->getImgUrl(),
                'description' => $combo->getDescription(),
                'price' => $combo->getPrice(),
                'flight' => $comboDetail ? (new FlightResponseDTO($comboDetail->getFlight()))->toArray() : null,
                'hotel' => $comboDetail ? (new HotelResponseDTO($comboDetail->getHotel()))->toArray() : null,
                'activity' => $comboDetail ? (new ActivityResponseDTO($comboDetail->getActivity()))->toArray() : null,
            ]);
    }
    #[Route(self::COMBO_ROUTE, methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $this->comboService->deleteCombo($id);

        return $this->json(['message' => 'Xóa combo thành công'], Response::HTTP_OK);
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
