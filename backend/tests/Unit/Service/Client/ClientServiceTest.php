<?php

namespace App\Tests\Unit\Service\Client;

use App\DTO\Client\CreateClientRequest;
use App\DTO\Client\UpdateClientRequest;
use App\Entity\Client;
use App\Service\Client\ClientService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ClientServiceTest extends TestCase
{
    public function test_create_creates_and_persists_client(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Client::class));
        $em->expects($this->once())->method('flush');

        $dto = new CreateClientRequest();
        $dto->firstName = 'Alice';
        $dto->lastName = 'Martin';
        $dto->phone = '0612345678';
        $dto->email = 'alice@example.com';
        $dto->address = '10 Rue A';
        $dto->postalCode = '75010';
        $dto->city = 'Paris';
        $dto->landlinePhone = '0144556677';

        $service = new ClientService($em);

        $client = $service->create($dto);

        $this->assertSame('Alice', $client->getFirstName());
        $this->assertSame('Martin', $client->getLastName());
        $this->assertSame('0612345678', $client->getPhone());
        $this->assertSame('alice@example.com', $client->getEmail());
        $this->assertFalse($client->isAnonymized());
        $this->assertInstanceOf(\DateTimeImmutable::class, $client->getUpdatedAt());
    }

    public function test_update_updates_only_non_null_fields(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $client = (new Client())
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPhone('0600000001')
            ->setEmail('john@example.com')
            ->setCity('Paris');

        $dto = new UpdateClientRequest();
        $dto->firstName = null;
        $dto->lastName = 'Smith';
        $dto->phone = null;
        $dto->email = 'smith@example.com';
        $dto->city = 'Lyon';

        $service = new ClientService($em);
        $updated = $service->update($client, $dto);

        $this->assertSame('John', $updated->getFirstName());
        $this->assertSame('Smith', $updated->getLastName());
        $this->assertSame('0600000001', $updated->getPhone());
        $this->assertSame('smith@example.com', $updated->getEmail());
        $this->assertSame('Lyon', $updated->getCity());
        $this->assertInstanceOf(\DateTimeImmutable::class, $updated->getUpdatedAt());
    }

    public function test_anonymize_replaces_sensitive_data(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $client = (new Client())
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPhone('0600000001')
            ->setEmail('john@example.com')
            ->setAddress('12 Rue')
            ->setPostalCode('75001')
            ->setCity('Paris')
            ->setLandlinePhone('0102030405')
            ->setIsAnonymized(false);

        $service = new ClientService($em);
        $anonymized = $service->anonymize($client);

        $this->assertSame('Anonyme', $anonymized->getFirstName());
        $this->assertSame('Anonyme', $anonymized->getLastName());
        $this->assertStringStartsWith('ANON-', $anonymized->getPhone());
        $this->assertNull($anonymized->getEmail());
        $this->assertNull($anonymized->getAddress());
        $this->assertNull($anonymized->getPostalCode());
        $this->assertNull($anonymized->getCity());
        $this->assertNull($anonymized->getLandlinePhone());
        $this->assertTrue($anonymized->isAnonymized());
    }
}