<?php

namespace App\Service;

use App\DTO\Request\Feedback\CreateFeedbackDTO;
use App\DTO\Request\Feedback\UpdateFeedbackDTO;
use App\Entity\Feedback;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\RatedType;

class FeedbackService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createFeedback(CreateFeedbackDTO $feedbackDTO): Feedback
    {
        $feedback = new Feedback();
        // Assuming Feedback entity has setTitle and setDescription methods
        $user = $this->entityManager->getRepository(User::class)->find($feedbackDTO->getUserId());
        $feedback->setUser($user);
        $ratedType = RatedType::from($feedbackDTO->getRatedType());
        $feedback->setRatedType($ratedType);
        $feedback->setRelatedId($feedbackDTO->getRelatedId());
        $feedback->setRating($feedbackDTO->getRating());
        $feedback->setComment($feedbackDTO->getComment());

        $this->entityManager->persist($feedback);
        $this->entityManager->flush();
        return $feedback;
    }

    public function getAllFeedbacks(): array
    {
        return $this->entityManager->getRepository(Feedback::class)->findAll();
    }

    public function getFeedbackById(int $id): ?Feedback
    {
        return $this->entityManager->getRepository(Feedback::class)->find($id);
    }

    public function updateFeedback(int $id, UpdateFeedbackDTO $feedbackDTO): Feedback
    {
        $feedback = $this->getFeedbackById($id);
        if (!$feedback) {
            throw new \Exception('Feedback not found');
        }

        $ratedType = RatedType::from($feedbackDTO->getRatedType());
        $feedback->setRatedType($ratedType);
        $feedback->setRelatedId($feedbackDTO->getRelatedId());
        $feedback->setRating($feedbackDTO->getRating());
        $feedback->setComment($feedbackDTO->getComment());

        $this->entityManager->flush();
        return $feedback;
    }

    public function deleteFeedback(int $id): void
    {
        $feedback = $this->getFeedbackById($id);
        if ($feedback) {
            $this->entityManager->remove($feedback);
            $this->entityManager->flush();
        }
    }
}
