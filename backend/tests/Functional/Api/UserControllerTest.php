<?php

namespace App\Tests\Functional\Api;

use App\Tests\Functional\ApiTestCase;

class UserControllerTest extends ApiTestCase
{
    public function test_super_admin_can_list_users(): void
    {
        $this->client->request('GET', '/api/users', server: $this->authHeader('superadmin@example.com'));
        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();
        $this->assertArrayHasKey('items', $data);
        $this->assertGreaterThan(0, $data['total']);
    }

    public function test_admin_can_create_technician(): void
    {
        $this->jsonRequest('POST', '/api/users', [
            'firstName' => 'New',
            'lastName' => 'Tech',
            'email' => 'newtech@example.com',
            'role' => 'ROLE_TECHNICIAN',
        ], $this->authHeader('admin@example.com'));

        $this->assertResponseStatusCodeSame(201);
        $data = $this->jsonResponse();

        $this->assertArrayHasKey('user', $data);
        $this->assertSame('newtech@example.com', $data['user']['email']);
    }

    public function test_admin_cannot_create_super_admin(): void
    {
        $this->jsonRequest('POST', '/api/users', [
            'firstName' => 'X',
            'lastName' => 'Y',
            'email' => 'newsuper@example.com',
            'role' => 'ROLE_SUPER_ADMIN',
        ], $this->authHeader('admin@example.com'));

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_show_user_success(): void
    {
        $this->client->request('GET', '/api/users/1', server: $this->authHeader('superadmin@example.com'));
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $this->jsonResponse()['id']);
    }

    public function test_update_user_success(): void
    {
        $this->jsonRequest('PATCH', '/api/users/4', [
            'firstName' => 'UpdatedTech',
            'lastName' => 'User',
            'email' => 'tech@example.com',
            'role' => 'ROLE_TECHNICIAN',
            'isActive' => true,
        ], $this->authHeader('superadmin@example.com'));

        $this->assertResponseIsSuccessful();
        $this->assertSame('UpdatedTech', $this->jsonResponse()['firstName']);
    }

    public function test_block_user_success(): void
    {
        $this->jsonRequest('PATCH', '/api/users/4/block', [
            'isActive' => false,
        ], $this->authHeader('superadmin@example.com'));

        $this->assertResponseIsSuccessful();
        $this->assertFalse($this->jsonResponse()['isActive']);
    }

    public function test_anonymize_user_success(): void
    {
        $this->jsonRequest('PATCH', '/api/users/4/anonymize', null, $this->authHeader('superadmin@example.com'));

        $this->assertResponseIsSuccessful();
        $data = $this->jsonResponse();

        $this->assertTrue($data['isAnonymized']);
        $this->assertFalse($data['isActive']);
    }

    public function test_change_my_password_success(): void
    {
        $this->jsonRequest('PATCH', '/api/me/password', [
            'currentPassword' => 'Password123!',
            'newPassword' => 'NewPassword123!',
            'confirmPassword' => 'NewPassword123!',
        ], $this->authHeader('passwordtester@example.com'));

        $this->assertResponseIsSuccessful();
        $this->assertSame('Mot de passe mis à jour avec succès.', $this->jsonResponse()['message']);
    }
}