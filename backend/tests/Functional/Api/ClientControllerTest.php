<?php

namespace App\Tests\Functional\Api;

use App\Tests\Functional\ApiTestCase;

class ClientControllerTest extends ApiTestCase
{
    public function test_reception_can_list_clients(): void
    {
        $this->client->request(
            'GET',
            '/api/clients',
            server: $this->authHeader('reception@example.com')
        );

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();

        $this->assertArrayHasKey('items', $data);
        $this->assertGreaterThanOrEqual(2, $data['total']);
    }

    public function test_technician_cannot_list_clients(): void
    {
        $this->client->request(
            'GET',
            '/api/clients',
            server: $this->authHeader('tech@example.com')
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_create_client_success(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/clients',
            [
                'firstName' => 'Alice',
                'lastName' => 'Martin',
                'phone' => '0612345678',
                'email' => 'alice@example.com',
                'address' => '10 Rue A',
                'postalCode' => '75010',
                'city' => 'Paris',
                'landlinePhone' => '0144556677',
            ],
            $this->authHeader('reception@example.com')
        );

        $this->assertResponseStatusCodeSame(201);

        $data = $this->jsonResponse();

        $this->assertSame('Alice', $data['firstName']);
        $this->assertSame('Martin', $data['lastName']);
    }

    public function test_create_client_validation_error(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/clients',
            [
                'firstName' => '',
                'lastName' => '',
                'phone' => '',
            ],
            $this->authHeader('reception@example.com')
        );

        $this->assertResponseStatusCodeSame(422);

        $data = $this->jsonResponse();

        $this->assertSame('Validation échouée', $data['message']);
        $this->assertNotEmpty($data['errors']);
    }

    public function test_show_client_success(): void
    {
        $this->client->request(
            'GET',
            '/api/clients/1',
            server: $this->authHeader('admin@example.com')
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $this->jsonResponse()['id']);
    }

    public function test_search_client_by_phone_success(): void
    {
        $this->client->request(
            'GET',
            '/api/clients/search?phone=0600000001',
            server: $this->authHeader('admin@example.com')
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame('0600000001', $this->jsonResponse()['phone']);
    }

    public function test_search_client_by_phone_requires_phone(): void
    {
        $this->client->request(
            'GET',
            '/api/clients/search',
            server: $this->authHeader('admin@example.com')
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_update_client_success(): void
    {
        $this->jsonRequest(
            'PATCH',
            '/api/clients/1',
            [
                'city' => 'Lyon',
                'postalCode' => '69001',
            ],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();

        $this->assertSame('Lyon', $data['city']);
        $this->assertSame('69001', $data['postalCode']);
    }

    public function test_anonymize_client_success(): void
    {
        $this->jsonRequest(
            'PATCH',
            '/api/clients/2/anonymize',
            null,
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();

        $this->assertTrue($data['isAnonymized']);
        $this->assertSame('Anonyme', $data['firstName']);
        $this->assertSame('Anonyme', $data['lastName']);
    }

    public function test_view_client_repairs_success(): void
    {
        $this->client->request(
            'GET',
            '/api/clients/1/repairs',
            server: $this->authHeader('reception@example.com')
        );

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();

        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));

        foreach ($data as $repair) {
            $this->assertArrayHasKey('id', $repair);
            $this->assertArrayHasKey('reference', $repair);
            $this->assertArrayHasKey('status', $repair);
        }
    }
}
