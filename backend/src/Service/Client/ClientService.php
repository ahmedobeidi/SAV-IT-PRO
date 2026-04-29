<?php

namespace App\Service\Client;

use App\DTO\Client\CreateClientRequest;
use App\DTO\Client\UpdateClientRequest;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class ClientService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(CreateClientRequest $dto): Client
    {
        $client = new Client();
        $client->setFirstName($dto->firstName);
        $client->setLastName($dto->lastName);
        $client->setPhone($dto->phone);
        $client->setEmail($dto->email);
        $client->setAddress($dto->address);
        $client->setPostalCode($dto->postalCode);
        $client->setCity($dto->city);
        $client->setLandlinePhone($dto->landlinePhone);
        $client->setIsAnonymized(false);
        $client->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    public function update(Client $client, UpdateClientRequest $dto): Client
    {
        if ($dto->firstName !== null) $client->setFirstName($dto->firstName);
        if ($dto->lastName !== null) $client->setLastName($dto->lastName);
        if ($dto->phone !== null) $client->setPhone($dto->phone);
        if ($dto->email !== null) $client->setEmail($dto->email);
        if ($dto->address !== null) $client->setAddress($dto->address);
        if ($dto->postalCode !== null) $client->setPostalCode($dto->postalCode);
        if ($dto->city !== null) $client->setCity($dto->city);
        if ($dto->landlinePhone !== null) $client->setLandlinePhone($dto->landlinePhone);

        $client->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $client;
    }

    public function anonymize(Client $client): Client
    {
        $client->setFirstName('Anonyme');
        $client->setLastName('Anonyme');
        $client->setPhone('ANON-'.$client->getId()); // garde unique, mais plus exploitable comme vrai numéro
        $client->setEmail(null);
        $client->setAddress(null);
        $client->setPostalCode(null);
        $client->setCity(null);
        $client->setLandlinePhone(null);

        $client->setIsAnonymized(true);
        $client->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();
        return $client;
    }
}
