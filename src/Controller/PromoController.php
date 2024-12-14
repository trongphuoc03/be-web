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

class PromoController extends AbstractController
{
    public function __construct(private PromoService $promoService) {}

    #[Route('/promos', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreatePromoDTO(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            discount: $data['discount'] ?? null,
            expiredDate: $data['expiredDate'] ?? null,
            amount: $data['amount'] ?? null,
            conditions: $data['conditions'] ?? null
        );
        $promo = $this->promoService->createPromo($dto);

        return $this->json(new PromoResponseDTO($promo), Response::HTTP_CREATED);
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
            return $this->json(['message' => 'Promo not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json((new PromoResponseDTO($promo))->toArray());
    }

    #[Route(self::PROMO_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new UpdatePromoDTO(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            discount: $data['discount'] ?? null,
            expiredDate: $data['expiredDate'] ?? null,
            amount: $data['amount'] ?? null,
            conditions: $data['conditions'] ?? null
        );

        $promo = $this->promoService->updatePromo($id, $dto);

        return $this->json(new PromoResponseDTO($promo));
    }

    #[Route(self::PROMO_ROUTE, methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->promoService->deletePromo($id);

        return $this->json(['message' => 'Promo deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
