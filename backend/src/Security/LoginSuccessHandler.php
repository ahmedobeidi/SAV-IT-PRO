<?php

namespace App\Security;

use App\Entity\User;
use App\Service\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private AuthService $authService
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Invalid user'], 400);
        }

        $jwt = $this->jwtManager->create($user);
        $refresh = $this->authService->createRefreshToken($user, 7);

        return new JsonResponse([
            'token' => $jwt,
            'refresh_token' => $refresh->getToken(),
            'expires_in' => 3600
        ]);
    }
}
