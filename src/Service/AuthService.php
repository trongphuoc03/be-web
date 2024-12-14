<?php

namespace App\Service;

use App\DTO\Request\Auth\LoginDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtTokenManager
    ) {}

    public function login(LoginDTO $loginDTO): ?string
    {
        // Tìm người dùng trong cơ sở dữ liệu
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $loginDTO->getEmail()]);

        // Kiểm tra người dùng có tồn tại không và mật khẩu có đúng không
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $loginDTO->getPassword())) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Tạo JWT token
        return $this->jwtTokenManager->create($user);
    }
}
