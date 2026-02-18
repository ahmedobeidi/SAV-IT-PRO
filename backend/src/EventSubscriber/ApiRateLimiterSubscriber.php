<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Bundle\SecurityBundle\Security;

class ApiRateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactory $apiLimiter,
        private RateLimiterFactory $loginLimiter,
        private Security $security,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 20]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Only /api/*
        if (!str_starts_with($path, '/api')) {
            return;
        }

        // PUBLIC endpoints (don’t throttle with "api" limiter)
        // but login gets its own stricter limiter.
        if ($path === '/api/auth/login') {
            $this->consumeOrBlock($event, $this->loginLimiter, $this->keyByIp($request));
            return;
        }

        // Other PUBLIC routes: skip or throttle lightly (your choice)
        if (
            str_starts_with($path, '/api/auth/forgot-password') ||
            str_starts_with($path, '/api/auth/reset-password') ||
            str_starts_with($path, '/api/auth/refresh')
        ) {
            // Option A: skip completely (simple)
            return;

            // Option B (better): throttle by IP with a separate limiter if you want
        }

        // Authenticated API: rate limit per USER (best), fallback to IP
        $user = $this->security->getUser();
        $key = $user ? ('user_'.$user->getUserIdentifier()) : $this->keyByIp($request);

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
