<?php

namespace App\Tests\Functional\Api;

use App\Tests\Functional\ApiTestCase;

class EquipmentControllerTest extends ApiTestCase
{
    public function test_list_equipment_types(): void
    {
        $this->client->request(
            'GET',
            '/api/equipment-types',
            server: $this->authHeader('reception@example.com')
        );

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(2, $this->jsonResponse()['total']);
    }

    public function test_create_equipment_type_success(): void
    {
        $this->jsonRequest('POST', '/api/equipment-types', [
            'name' => 'Unique Test Type Alpha',
        ], $this->authHeader('reception@example.com'));

        $this->assertResponseStatusCodeSame(201);
        $data = $this->jsonResponse();

        $this->assertSame('Unique Test Type Alpha', $data['name']);
    }

    public function test_create_equipment_type_duplicate_returns_409(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/equipment-types',
            ['name' => 'Phone'],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseStatusCodeSame(409);
    }

    public function test_update_equipment_type_success(): void
    {
        $this->jsonRequest(
            'PATCH',
            '/api/equipment-types/2',
            ['name' => 'Laptop Pro'],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame('Laptop Pro', $this->jsonResponse()['name']);
    }

    public function test_list_brands_by_type(): void
    {
        $this->client->request(
            'GET',
            '/api/equipment-types/1/brands',
            server: $this->authHeader('admin@example.com')
        );

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(1, $this->jsonResponse()['total']);
    }

    public function test_create_brand_success(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/equipment-types/1/brands',
            ['name' => 'Samsung'],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('Samsung', $this->jsonResponse()['name']);
    }

    public function test_create_brand_duplicate_returns_409(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/equipment-types/1/brands',
            ['name' => 'Apple'],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseStatusCodeSame(409);
    }

    public function test_list_models_by_brand(): void
    {
        $this->client->request(
            'GET',
            '/api/equipment-brands/1/models',
            server: $this->authHeader('admin@example.com')
        );

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(1, $this->jsonResponse()['total']);
    }

    public function test_create_model_success(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/equipment-brands/1/models',
            ['name' => 'iPhone 14'],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('iPhone 14', $this->jsonResponse()['name']);
    }

    public function test_list_issues_by_type(): void
    {
        $this->client->request(
            'GET',
            '/api/equipment-types/1/issues',
            server: $this->authHeader('reception@example.com')
        );

        $this->assertResponseIsSuccessful();
        $data = $this->jsonResponse();

        $this->assertArrayHasKey('items', $data);
        $this->assertGreaterThanOrEqual(1, $data['total']);
        $this->assertNotEmpty($data['items']);
    }

    public function test_create_issue_success(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/equipment-types/1/issues',
            ['name' => 'Camera Issue'],
            $this->authHeader('admin@example.com')
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('Camera Issue', $this->jsonResponse()['name']);
    }
}
