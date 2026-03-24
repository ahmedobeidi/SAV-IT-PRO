<?php

namespace App\Tests\Functional\Api;

use App\Tests\Functional\ApiTestCase;

class TicketControllerTest extends ApiTestCase
{
    public function test_staff_can_generate_ticket(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/repair-orders/1/tickets/generate',
            null,
            $this->authHeader('reception@example.com')
        );

        $this->assertResponseIsSuccessful();

        $data = $this->jsonResponse();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('filename', $data);
        $this->assertArrayHasKey('viewUrl', $data);
        $this->assertTrue($data['isCurrent']);
    }

    public function test_technician_cannot_generate_ticket(): void
    {
        $this->jsonRequest(
            'POST',
            '/api/repair-orders/1/tickets/generate',
            null,
            $this->authHeader('tech@example.com')
        );

        $this->assertResponseStatusCodeSame(403);
    }
}