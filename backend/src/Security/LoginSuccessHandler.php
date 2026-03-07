<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\UserRole;
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
            return new JsonResponse(['message' => 'Utilisateur invalide'], 400);
        }

        $jwt = $this->jwtManager->create($user);
        $refresh = $this->authService->createRefreshToken($user, 7);

        $roles = $user->getRoles();

        $mainRole = array_values(array_filter(
            $roles,
            fn($r) => $r !== 'ROLE_USER'
        ))[0] ?? null;

        $roleLabel = null;

        if ($mainRole) {
            $roleLabel = UserRole::from($mainRole)->label();
        }

        return new JsonResponse([
            'token' => $jwt,
            'refresh_token' => $refresh->getToken(),
            'expires_in' => 3600,
            'role' => $roleLabel
        ]);
    }
}
