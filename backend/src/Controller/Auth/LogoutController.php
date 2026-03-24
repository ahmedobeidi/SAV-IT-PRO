<?php

namespace App\Controller\Auth;

use App\Service\AuthService;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LogoutController extends AbstractController
{
    #[Route('/api/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
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
