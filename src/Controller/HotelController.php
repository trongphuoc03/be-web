<?php

namespace App\Controller;

use App\DTO\Request\Hotel\CreateHotelDTO;
use App\DTO\Request\Hotel\UpdateHotelDTO;
use App\DTO\Response\Hotel\HotelResponseDTO;
use App\Service\FileUploader;
use App\Service\HotelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JWTService;

class HotelController extends AbstractController
{
    private const HOTEL_ROUTE = '/hotels/{id}';

    public function __construct(private HotelService $hotelService, private JWTService $jWTService, private FileUploader $fileUploader) {}

    #[Route('/hotels', methods: ['POST'])]
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
        $dto = new CreateHotelDTO(
            name: $data['name'],
            imgUrl: $fileName,
            location: $data['location'],
            phone: $data['phone'],
            emptyRoom: $data['emptyRoom'],
            price: $data['price'],
            description: $data['description']
        );
        $hotel = $this->hotelService->createHotel($dto);

        return $this->json(new HotelResponseDTO($hotel), Response::HTTP_CREATED);
    }
    #[Route('/hotels/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $hotels = $this->hotelService->getAllHotels();
        $response = [];

        foreach ($hotels as $hotel) {
            $response[] = (new HotelResponseDTO($hotel))->toArray();
        }

        return $this->json($response);
    }

    #[Route(self::HOTEL_ROUTE, methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $hotel = $this->hotelService->getHotelById($id);

        if (!$hotel) {
            return $this->json(['message' => 'Không tìm thấy khách sạn'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(new HotelResponseDTO($hotel));
    }

    #[Route(self::HOTEL_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateHotelDTO(
            name: $data['name'],
            location: $data['location'],
            phone: $data['phone'],
            emptyRoom: $data['emptyRoom'],
            price: $data['price'],
            description: $data['description']
        );

        $hotel = $this->hotelService->updateHotel($id, $dto);

        return $this->json(new HotelResponseDTO($hotel));
    }

    #[Route(self::HOTEL_ROUTE, methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $this->hotelService->deleteHotel($id);

        return $this->json(['message' => 'Xóa khách sạn thành công'], Response::HTTP_OK);
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
