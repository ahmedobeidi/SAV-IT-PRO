<?php

namespace App\Controller\Auth;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Connexion',
        description: 'Authentifie un utilisateur. Le traitement réel est intercepté par Symfony Security json_login.',
        tags: ['Authentification']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Identifiants de connexion',
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@email.com'),
                new OA\Property(property: 'password', type: 'string', example: 'MotDePasse123'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Connexion réussie')]
    #[OA\Response(response: 401, description: 'Identifiants invalides')]
    public function __invoke(): Response
    {
        // This should never be reached because Symfony Security (json_login) intercepts it.
        return new Response(status: 204);
    }
}