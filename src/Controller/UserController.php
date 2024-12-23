<?php

namespace App\Controller;

use App\Service\JWTService;
use App\DTO\Request\User\CreateUserDTO;
use App\DTO\Request\User\UpdateUserDTO;
use App\DTO\Response\User\UserResponseDTO;
use App\Entity\User;
use App\Enum\UserRole;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private UserService $userService, private JWTService $jwtService) {}

    #[Route('/signup', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreateUserDTO(
            username: $data['username'],
            password: $data['password'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            role: UserRole::USER->value
        );

        $user = $this->userService->createUser($dto);
        return $this->json(new UserResponseDTO($user), Response::HTTP_CREATED);
    }

    #[Route('/users/bulk', methods: ['GET'])]
    public function getAllUsers(Request $request): JsonResponse
    {
        $this->checkAdminRole($request);
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
        $user = $this->userService->getUserById($id);
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
    public function deleteUser(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['username']) || empty($data['password'])) {
            return $this->json(['message' => 'Username or password không được để trống'], Response::HTTP_BAD_REQUEST);
        }
        $user = $this->userService->getUserByUsername($data['username']);
        if ( !$user || !$this->userService->isPasswordValid($user, $data['password']) ) {
            return $this->json(['message' => 'Tài khoản hoặc mật khẩu không đúng'], Response::HTTP_BAD_REQUEST);
        }

        $userData = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'role' => $user->getRole()
        ];

        // Tạo JWT token
        $token = $this->jwtService->createToken($userData);

        // Trả về token
        return $this->json([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }

    private function checkAdminRole(Request $request)
    {
       // Lấy header Authorization
       $authorizationHeader = $request->headers->get('Authorization');
        
       // Kiểm tra nếu không có header hoặc header không đúng định dạng
       if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
           throw new \Exception ('Authorization header is missing or invalid', Response::HTTP_UNAUTHORIZED);
       }
       // Tách token từ header
       $token = $matches[1];

       // Kiểm tra role
       if (!$this->jwtService->isAdmin($token)) {
           throw new \Exception ('Unauthorized', Response::HTTP_UNAUTHORIZED);
       }
    }
}
