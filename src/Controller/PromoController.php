<?php

namespace App\Controller;

use App\DTO\Request\Promo\CreatePromoDTO;
use App\DTO\Request\Promo\UpdatePromoDTO;
use App\DTO\Response\Promo\PromoResponseDTO;
use App\Service\PromoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JWTService;
use DateTime;
class PromoController extends AbstractController
{
    public function __construct(private PromoService $promoService, private JWTService $jWTService) {}

    #[Route('/promos', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $expiredDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['expiredDate']);
        $dto = new CreatePromoDTO(
            name: $data['name'],
            description: $data['description'],
            discount: $data['discount'],
            expiredDate: $expiredDate,
            amount: $data['amount'],
            conditions: $data['conditions']
        );
        $promo = $this->promoService->createPromo($dto);

        return $this->json((new PromoResponseDTO($promo))->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/promos/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $promos = $this->promoService->getAllPromos();
        $response = [];

        foreach ($promos as $promo) {
            $response[] = (new PromoResponseDTO($promo))->toArray();
        }

        return $this->json($response);
    }

    private const PROMO_ROUTE = '/promos/{id}';

    #[Route(self::PROMO_ROUTE, methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $promo = $this->promoService->getPromoById($id);

        if (!$promo) {
            return $this->json(['message' => 'Không tìm thấy khuyến mãi'], Response::HTTP_NOT_FOUND);
        }

        return $this->json((new PromoResponseDTO($promo))->toArray());
    }

    #[Route(self::PROMO_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $expiredDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['expiredDate']);
        $dto = new UpdatePromoDTO(
            name: $data['name'],
            description: $data['description'],
            discount: $data['discount'],
            expiredDate: $expiredDate,
            amount: $data['amount'],
            conditions: $data['conditions']
        );

        $promo = $this->promoService->updatePromo($id, $dto);

        return $this->json((new PromoResponseDTO($promo))->toArray());
    }

    #[Route(self::PROMO_ROUTE, methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $this->promoService->deletePromo($id);

        return $this->json(['message' => 'Xóa khuyến mãi thành công'], Response::HTTP_OK);
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
