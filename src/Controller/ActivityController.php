<?php

namespace App\Controller;

use App\DTO\Request\Activity\CreateActivityDTO;
use App\DTO\Request\Activity\UpdateActivityDTO;
use App\DTO\Response\Activity\ActivityResponseDTO;
use App\Service\ActivityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityController extends AbstractController
{
    public function __construct(private ActivityService $activityService) {}

    #[Route('/activities', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreateActivityDTO(
            name: $data['name'] ?? null,
            emptySlot: $data['emptySlot'] ?? null,
            location: $data['location'] ?? null,
            description: $data['description'] ?? null,
            price: $data['price'] ?? null
        );
        
        $activity = $this->activityService->createActivity($dto);

        return $this->json(new ActivityResponseDTO($activity), Response::HTTP_CREATED);
    }

    #[Route('/activities/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $activities = $this->activityService->getAllActivities();
        $response = [];

        foreach ($activities as $activity) {
            $response[] = (new ActivityResponseDTO($activity))->toArray();
        }

        return $this->json($response);
    }

    #[Route('/activities/{id}', methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $activity = $this->activityService->getActivityById($id);
    
        if (!$activity) {
            return $this->json(['message' => 'Activity not found'], Response::HTTP_NOT_FOUND);
        }
    
        return $this->json((new ActivityResponseDTO($activity))->toArray());
    }

    #[Route('/activities/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateActivityDTO(
            name: $data['name'] ?? null,
            emptySlot: $data['emptySlot'] ?? null,
            location: $data['location'] ?? null,
            description: $data['description'] ?? null,
            price: $data['price'] ?? null
        );

        $activity = $this->activityService->updateActivity($id, $dto);

        return $this->json(new ActivityResponseDTO($activity));
    }

    #[Route('/activities/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->activityService->deleteActivity($id);

        return $this->json(['message' => 'Activity deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
