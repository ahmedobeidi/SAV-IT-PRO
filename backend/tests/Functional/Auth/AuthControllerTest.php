<?php

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\ApiTestCase;

class AuthControllerTest extends ApiTestCase
{
    public function test_login_success(): void
    {
        $this->jsonRequest('POST', '/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertResponseIsSuccessful();
        $data = $this->jsonResponse();

        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertSame(3600, $data['expires_in']);
        $this->assertSame('Administrateur', $data['role']);
    }

    public function test_login_fails_for_blocked_user(): void
    {
        $this->jsonRequest('POST', '/api/auth/login', [
            'email' => 'blocked@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertResponseStatusCodeSame(401);
        $data = $this->jsonResponse();
        $this->assertStringContainsString('Compte bloqué', $data['message']);
    }

    public function test_login_fails_for_password_setup_required_user(): void
    {
        $this->jsonRequest('POST', '/api/auth/login', [
            'email' => 'pending@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertResponseStatusCodeSame(401);
        $data = $this->jsonResponse();
        $this->assertStringContainsString('pas encore activé', $data['message']);
    }

    public function test_refresh_requires_token(): void
    {
        $this->jsonRequest('POST', '/api/auth/refresh', []);
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_refresh_success(): void
    {
        $login = $this->login('admin@example.com');

        $this->jsonRequest('POST', '/api/auth/refresh', [
            'refresh_token' => $login['refresh_token'],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $this->jsonResponse();

        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertSame(3600, $data['expires_in']);
    }

    public function test_logout_requires_authentication(): void
    {
        $this->jsonRequest('POST', '/api/auth/logout', []);
        $this->assertResponseStatusCodeSame(401);
    }

    public function test_logout_requires_refresh_token_when_authenticated(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/auth/logout',
            [],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertSame('Le refresh_token est obligatoire', $this->jsonResponse()['message']);
    }

    public function test_logout_success(): void
    {
        $login = $this->login('admin@example.com');

        $this->jsonRequest(
            'POST',
            '/api/auth/logout',
            ['refresh_token' => $login['refresh_token']],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame('Déconnexion réussie', $this->jsonResponse()['message']);
    }

    public function test_forgot_password_requires_email(): void
    {
        $this->jsonRequest('POST', '/api/auth/forgot-password', []);
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_forgot_password_returns_generic_message_for_unknown_email(): void
    {
        $this->jsonRequest('POST', '/api/auth/forgot-password', [
            'email' => 'unknown@example.com',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Si l’email existe', $this->jsonResponse()['message']);
    }
}