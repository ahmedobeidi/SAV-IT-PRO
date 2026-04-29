<?php

namespace App\Tests\Functional\Api;

use App\Tests\Functional\ApiTestCase;

class RepairOrderControllerTest extends ApiTestCase
{
    public function test_staff_can_list_repair_orders(): void
    {
        $this->client->request(
            'GET',
            '/api/repair-orders',
            server: $this->authHeader('reception@example.com')
        );

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertGreaterThanOrEqual(1, $data['total']);
    }

    public function test_technician_cannot_list_all_repair_orders(): void
    {
        $this->client->request(
            'GET',
            '/api/repair-orders',
            server: $this->authHeader('tech@example.com')
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_create_repair_order_success(): void
    {
        $this->jsonRequest('POST', '/api/repair-orders', [
            'clientId' => 1,
            'equipmentModelId' => 1,
            'issueId' => 1,
            'price' => 150,
            'deposit' => 30,
            'description' => 'Replace broken screen',
        ], $this->authHeader('reception@example.com'));

        $this->assertResponseStatusCodeSame(201);

        $data = $this->jsonResponse();
        $this->assertSame(150.0, (float) $data['price']);
        $this->assertSame(30.0, (float) $data['deposit']);
        $this->assertSame('Replace broken screen', $data['description']);
        $this->assertArrayHasKey('reference', $data);
    }

    public function test_create_repair_order_with_mismatched_issue_returns_409(): void
    {
        $this->jsonRequest('POST', '/api/repair-orders', [
            'clientId' => 1,
            'equipmentModelId' => 1,
            'issueId' => 2,
            'price' => 150,
            'deposit' => 30,
            'description' => 'Mismatch issue/type',
        ], $this->authHeader('reception@example.com'));

        $this->assertResponseStatusCodeSame(409);
    }

    public function test_update_repair_order_success(): void
    {
        $this->jsonRequest('PATCH', '/api/repair-orders/1', [
            'equipmentModelId' => 1,
            'issueId' => 1,
            'price' => 180,
            'deposit' => 50,
            'description' => 'Updated description',
        ], $this->authHeader('reception@example.com'));

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();
        $this->assertSame(180.0, (float) $data['price']);
        $this->assertSame(50.0, (float) $data['deposit']);
        $this->assertSame('Updated description', $data['description']);
    }

    public function test_admin_can_assign_technician(): void
    {
        $this->jsonRequest('PATCH', '/api/repair-orders/1/assign', [
            'technicianId' => 4,
        ], $this->authHeader('admin@example.com'));

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();
        $this->assertSame(4, $data['assignedTo']['id']);
    }

    public function test_admin_can_unassign_technician(): void
    {
        $this->jsonRequest('PATCH', '/api/repair-orders/1/assign', [
            'technicianId' => null,
        ], $this->authHeader('admin@example.com'));

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();
        $this->assertNull($data['assignedTo']);
    }

    public function test_staff_can_update_status(): void
    {
        $this->jsonRequest('PATCH', '/api/repair-orders/1/status', [
            'status' => 'DONE',
        ], $this->authHeader('reception@example.com'));

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();
        $this->assertSame('DONE', $data['status']);
    }
}