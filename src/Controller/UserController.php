<?php

namespace App\Controller;

use App\DTO\Request\User\CreateUserDTO;
use App\DTO\Request\User\UpdateUserDTO;
use App\DTO\Response\User\UserResponseDTO;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private UserService $userService) {}

    #[Route('/users', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreateUserDTO(
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            role: $data['role'] ?? null
        );

        $user = $this->userService->createUser($dto);
        return $this->json(new UserResponseDTO($user), Response::HTTP_CREATED);
    }

    #[Route('/users/bulk', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        $response = [];

        foreach ($users as $user) {
            $response[] = (new UserResponseDTO($user))->toArray();
        }

        return $this->json($response);
    }

    private const USER_ROUTE = '/users/{id}';

    #[Route(self::USER_ROUTE, methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json((new UserResponseDTO($user))->toArray());
    }

    #[Route(self::USER_ROUTE, methods: ['PATCH'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateUserDTO(
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            role: $data['role'] ?? null
        );

        $user = $this->userService->updateUser($id, $dto);

        return $this->json(new UserResponseDTO($user));
    }

    #[Route(self::USER_ROUTE, methods: ['DELETE'])]
    // #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
