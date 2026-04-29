<?php

namespace App\Tests\Functional;

use App\DataFixtures\FixturePassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected ?EntityManagerInterface $em = null;

    /** @var array<string, array<string, mixed>> */
    private static array $tokenCache = [];

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function login(string $email, string $password = FixturePassword::DEFAULT): array
    {
        $cacheKey = $email . '|' . $password;

        if (isset(self::$tokenCache[$cacheKey])) {
            return self::$tokenCache[$cacheKey];
        }

        $this->client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => $password,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200, $this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::$tokenCache[$cacheKey] = $data;

        return $data;
    }

    protected function authHeader(string $email, string $password = FixturePassword::DEFAULT): array
    {
        $data = $this->login($email, $password);

        return [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $data['token'],
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    protected function jsonRequest(string $method, string $uri, ?array $payload = null, array $server = []): void
    {
        $this->client->request(
            $method,
            $uri,
            server: array_merge(['CONTENT_TYPE' => 'application/json'], $server),
            content: $payload === null ? null : json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    protected function jsonResponse(): array
    {
        $content = $this->client->getResponse()->getContent();

        if ($content === '' || $content === false) {
            return [];
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}