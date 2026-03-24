<?php

namespace App\Tests\Functional\Api;

use App\Tests\Functional\ApiTestCase;

class TechnicianRepairOrderControllerTest extends ApiTestCase
{
    public function test_technician_can_list_only_assigned_orders(): void
    {
        $this->client->request(
            'GET',
            '/api/technician/repair-orders',
            server: $this->authHeader('tech@example.com')
        );

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();

        $this->assertGreaterThanOrEqual(1, $data['total']);
        $this->assertNotEmpty($data['items']);

        foreach ($data['items'] as $item) {
            $this->assertNotNull($item['assignedTo']);
            $this->assertSame(4, $item['assignedTo']['id']);
        }
    }

    public function test_technician_can_update_assigned_order_status(): void
    {
        $repairId = $this->createFreshAssignedRepairOrderForTech();

        $this->jsonRequest('PATCH', "/api/technician/repair-orders/{$repairId}/status", [
            'status' => 'WAITING_PARTS',
        ], $this->authHeader('tech@example.com'));

        $this->assertResponseIsSuccessful();
        $this->assertSame('WAITING_PARTS', $this->jsonResponse()['status']);
    }

    public function test_technician_cannot_mark_delivered(): void
    {
        $repairId = $this->createFreshAssignedRepairOrderForTech();

        $this->jsonRequest('PATCH', "/api/technician/repair-orders/{$repairId}/status", [
            'status' => 'DELIVERED',
        ], $this->authHeader('tech@example.com'));

        $this->assertResponseStatusCodeSame(409);
    }

    private function createFreshAssignedRepairOrderForTech(): int
    {
        $this->jsonRequest('POST', '/api/repair-orders', [
            'clientId' => 1,
            'equipmentModelId' => 1,
            'issueId' => 1,
            'price' => 100,
            'deposit' => 10,
            'description' => 'Fresh repair order for technician test',
        ], $this->authHeader('reception@example.com'));

        $this->assertResponseStatusCodeSame(201);

        $created = $this->jsonResponse();
        $repairId = $created['id'];

        $this->jsonRequest('PATCH', "/api/repair-orders/{$repairId}/assign", [
            'technicianId' => 4,
        ], $this->authHeader('admin@example.com'));

        $this->assertResponseIsSuccessful();

        return $repairId;
    }
}