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
use App\Service\JWTService;

class FeedbackController extends AbstractController
{
    private const FEEDBACK_ROUTE = '/feedbacks/{id}';
    public function __construct(private FeedbackService $feedbackService, private JWTService $jWTService) {}

    #[Route('/feedbacks', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreateFeedbackDTO(
            userId: $data['userId'],
            ratedType: $data['ratedType'],
            relatedId: $data['relatedId'],
            rating: $data['rating'],
            comment: $data['comment']
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
    #[Route(self::FEEDBACK_ROUTE, methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true);
        $dto = new UpdateFeedbackDTO(
            ratedType: $data['ratedType'],
            relatedId: $data['relatedId'],
            rating: $data['rating'],
            comment: $data['comment']
        );

        $feedback = $this->feedbackService->updateFeedback($id, $dto);

        return $this->json(new FeedbackResponseDTO($feedback));
    }
    #[Route(self::FEEDBACK_ROUTE, methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        
        $this->feedbackService->deleteFeedback($id);

        return $this->json(['message' => 'Feedback deleted successfully'], Response::HTTP_OK);
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
