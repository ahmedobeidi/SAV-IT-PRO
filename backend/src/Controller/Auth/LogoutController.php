<?php

namespace App\Controller\Auth;

use App\Service\AuthService;
use App\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LogoutController extends AbstractController
{
    #[Route('/api/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Déconnexion',
        description: 'Révoque le refresh token fourni et déconnecte l’utilisateur.',
        tags: ['Authentification'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Refresh token à révoquer',
        content: new OA\JsonContent(
            required: ['refresh_token'],
            properties: [
                new OA\Property(property: 'refresh_token', type: 'string', example: 'refresh_token_value'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Déconnexion réussie')]
    #[OA\Response(response: 400, description: 'refresh_token obligatoire')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    public function logout(Request $request, AuthService $authService): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return new JsonResponse([
                'message' => 'Le refresh_token est obligatoire'
            ], 400);
        }

        $refresh = $authService->findValidRefreshToken($refreshToken);

        if ($refresh && $refresh->getUser()?->getId() === $user->getId()) {
            $authService->revokeRefreshToken($refresh);
        }

        return new JsonResponse([
            'message' => 'Déconnexion réussie'
        ]);
    }
}