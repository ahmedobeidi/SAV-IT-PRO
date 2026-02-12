<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Service\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RefreshController extends AbstractController
{
    #[Route('/api/auth/refresh', name: 'api_auth_refresh', methods: ['POST'])]
    public function refresh(
        Request $request,
        AuthService $authService,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return new JsonResponse(['message' => 'refresh_token is required'], 400);
        }

        $refresh = $authService->findValidRefreshToken($refreshToken);
        if (!$refresh) {
            return new JsonResponse(['message' => 'Invalid refresh token'], 401);
        }

        $user = $refresh->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Invalid user'], 401);
        }

        // Optional security: rotate refresh token (recommended)
        // revoke old and create a new one
        $authService->revokeRefreshToken($refresh);
        $newRefresh = $authService->createRefreshToken($user, 7);

        $newJwt = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $newJwt,
            'refresh_token' => $newRefresh->getToken(),
            'expires_in' => 3600
        ]);
    }
}
