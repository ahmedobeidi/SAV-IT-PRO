<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Service\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RefreshController extends AbstractController
{
    #[Route('/api/auth/refresh', name: 'api_auth_refresh', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/refresh',
        summary: 'Rafraîchir le token JWT',
        description: 'Génère un nouveau token JWT et un nouveau refresh token à partir d’un refresh token valide.',
        tags: ['Authentification']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Refresh token',
        content: new OA\JsonContent(
            required: ['refresh_token'],
            properties: [
                new OA\Property(property: 'refresh_token', type: 'string', example: 'refresh_token_value'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Token rafraîchi avec succès')]
    #[OA\Response(response: 400, description: 'refresh_token requis')]
    #[OA\Response(response: 401, description: 'Refresh token invalide')]
    public function refresh(
        Request $request,
        AuthService $authService,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return new JsonResponse(['message' => 'refresh_token est requis'], 400);
        }

        $refresh = $authService->findValidRefreshToken($refreshToken);
        if (!$refresh) {
            return new JsonResponse(['message' => 'Refresh token invalide'], 401);
        }

        $user = $refresh->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Utilisateur invalide'], 401);
        }

        $authService->revokeRefreshToken($refresh);
        $newRefresh = $authService->createRefreshToken($user, 7);
        $newJwt = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $newJwt,
            'refresh_token' => $newRefresh->getPlainToken(),
            'expires_in' => 3600
        ]);
    }
}