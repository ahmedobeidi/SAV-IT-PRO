<?php

namespace App\Controller\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/api/auth/forgot-password', name: 'api_auth_forgot_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/forgot-password',
        summary: 'Demander une réinitialisation de mot de passe',
        description: 'Envoie un lien de réinitialisation si l’email existe.',
        tags: ['Authentification']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Email du compte',
        content: new OA\JsonContent(
            required: ['email'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@email.com'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Demande traitée')]
    #[OA\Response(response: 400, description: 'Email invalide ou manquant')]
    public function forgot(
        Request $request,
        EntityManagerInterface $em,
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $email = trim($data['email'] ?? '');

        if (!$email) {
            return new JsonResponse(['message' => 'L’email est requis'], 400);
        }

        // Validate format, but do not reveal whether it exists
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['message' => 'Adresse e-mail invalide'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Si l’email existe, un lien de réinitialisation a été envoyé.'
            ], 200);
        }

        try {
            $resetToken = $resetPasswordHelper->generateResetToken($user);
        } catch (TooManyPasswordRequestsException $e) {
            return new JsonResponse([
                'message' => 'Si l’email existe, un lien de réinitialisation a été envoyé.'
            ], 200);
        }

        $token = $resetToken->getToken();

        $frontendResetUrl = sprintf(
            'http://localhost:5173/reset-password#token=%s',
            urlencode($token)
        );

        $message = (new TemplatedEmail())
            ->from('no-reply@sav-it-pro.com')
            ->to($user->getEmail())
            ->subject('Réinitialisez votre mot de passe')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context([
                'resetUrl' => $frontendResetUrl,
                'user' => $user,
            ]);

        try {
            $mailer->send($message);
        } catch (\Throwable $e) {
            if ($this->getParameter('kernel.environment') === 'dev') {
                return new JsonResponse([
                    'message' => 'Erreur d’envoi de mail (dev uniquement)',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return new JsonResponse([
                'message' => 'Si l’email existe, un lien de réinitialisation a été envoyé.'
            ], 200);
        }

        return new JsonResponse([
            'message' => 'Si l’email existe, un lien de réinitialisation a été envoyé.'
        ], 200);
    }
}