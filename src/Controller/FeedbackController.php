<?php

namespace App\Controller;

use App\DTO\Request\Feedback\CreateFeedbackDTO;
use App\DTO\Request\Feedback\UpdateFeedbackDTO;
use App\DTO\Response\Feedback\FeedbackResponseDTO;
use App\Service\FeedbackService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackController extends AbstractController
{
    private const FEEDBACK_ROUTE = '/feedbacks/{id}';
    public function __construct(private FeedbackService $feedbackService) {}

    #[Route('/feedbacks', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreateFeedbackDTO(
            userId: $data['userId'] ?? null,
            ratedType: $data['ratedType'] ?? null,
            relatedId: $data['relatedId'] ?? null,
            rating: $data['rating'] ?? null,
            comment: $data['comment'] ?? null
        );
        $feedback = $this->feedbackService->createFeedback($dto);

        return $this->json(new FeedbackResponseDTO($feedback), Response::HTTP_CREATED);
    }
    #[Route('/feedbacks/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $feedbacks = $this->feedbackService->getAllFeedbacks();
        $response = [];

        foreach ($feedbacks as $feedback) {
            $response[] = (new FeedbackResponseDTO($feedback))->toArray();
        }

        return $this->json($response);
    }
    #[Route(self::FEEDBACK_ROUTE, methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $feedback = $this->feedbackService->getFeedbackById($id);

        if (!$feedback) {
            return $this->json(['message' => 'Feedback not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(new FeedbackResponseDTO($feedback));
    }
    #[Route(self::FEEDBACK_ROUTE, methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateFeedbackDTO(
            ratedType: $data['ratedType'] ?? null,
            relatedId: $data['relatedId'] ?? null,
            rating: $data['rating'] ?? null,
            comment: $data['comment'] ?? null
        );

        $feedback = $this->feedbackService->updateFeedback($id, $dto);

        return $this->json(new FeedbackResponseDTO($feedback));
    }
    #[Route(self::FEEDBACK_ROUTE, methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->feedbackService->deleteFeedback($id);

        return $this->json(['message' => 'Feedback deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
