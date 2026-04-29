<?php

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class ApiRateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactory $apiLimiter,
        private RateLimiterFactory $loginLimiter,
        private RateLimiterFactory $forgotPasswordLimiter,
        private RateLimiterFactory $resetPasswordLimiter,
        private RateLimiterFactory $refreshTokenLimiter,
        private Security $security,
        private string $appEnv,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 20]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->appEnv === 'test') {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!str_starts_with($path, '/api')) {
            return;
        }

        if ($path === '/api/auth/login') {
            $this->consumeOrBlock($event, $this->loginLimiter, $this->keyByIp($request));
            return;
        }

        if ($path === '/api/auth/forgot-password') {
            $this->consumeOrBlock($event, $this->forgotPasswordLimiter, $this->keyByIp($request));
            return;
        }

        if ($path === '/api/auth/reset-password') {
            $this->consumeOrBlock($event, $this->resetPasswordLimiter, $this->keyByIp($request));
            return;
        }

        if ($path === '/api/auth/refresh') {
            $this->consumeOrBlock($event, $this->refreshTokenLimiter, $this->keyByIp($request));
            return;
        }

        $user = $this->security->getUser();
        $key = $user ? ('user_' . $user->getUserIdentifier()) : $this->keyByIp($request);

        $this->consumeOrBlock($event, $this->apiLimiter, $key);
    }

    private function keyByIp($request): string
    {
        return 'ip_' . ($request->getClientIp() ?? 'unknown');
    }

    private function consumeOrBlock(RequestEvent $event, RateLimiterFactory $factory, string $key): void
    {
        $limiter = $factory->create($key);
        $limit = $limiter->consume(1);

        if ($limit->isAccepted()) {
            return;
        }

        $retryAfter = $limit->getRetryAfter();
        $seconds = $retryAfter ? max(1, $retryAfter->getTimestamp() - time()) : 60;

        $response = new JsonResponse([
            'message' => 'Too many requests',
            'retryAfterSeconds' => $seconds,
        ], 429);

        $response->headers->set('Retry-After', (string) $seconds);
        $event->setResponse($response);
    }
}