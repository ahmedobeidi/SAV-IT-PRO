<?php

namespace App\Controller\Api;

use App\DTO\Client\CreateClientRequest;
use App\DTO\Client\UpdateClientRequest;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Security\Voter\ClientVoter;
use App\Service\Client\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private ClientService $clientService
    ) {}

    #[Route('', name: 'api_clients_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::CREATE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new CreateClientRequest();
        $dto->firstName = $data['firstName'] ?? '';
        $dto->lastName = $data['lastName'] ?? '';
        $dto->phone = $data['phone'] ?? '';
        $dto->email = $data['email'] ?? null;
        $dto->address = $data['address'] ?? null;
        $dto->postalCode = $data['postalCode'] ?? null;
        $dto->city = $data['city'] ?? null;
        $dto->landlinePhone = $data['landlinePhone'] ?? null;

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => array_map(fn($e) => [
                    'field' => $e->getPropertyPath(),
                    'message' => $e->getMessage(),
                ], iterator_to_array($errors)),
            ], 422);
        }

        $client = $this->clientService->create($dto);

        return $this->json($client, 201, [], ['groups' => ['client:read']]);
    }

    #[Route('', name: 'api_clients_list', methods: ['GET'])]
    public function list(Request $request, ClientRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::VIEW_LIST);

        $phone = $request->query->get('phone');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $result = $repo->searchByPhonePaginated($phone, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['client:read']]);
    }

    #[Route('/search', name: 'api_clients_search_phone', methods: ['GET'])]
    public function searchByPhone(Request $request, ClientRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::VIEW_LIST);

        $phone = $request->query->get('phone');
        if (!$phone) {
            return $this->json(['message' => 'phone is required'], 400);
        }

        $client = $repo->findOneByPhone($phone);
        if (!$client) {
            return $this->json(['message' => 'Client not found'], 404);
        }

        $this->denyAccessUnlessGranted(ClientVoter::VIEW, $client);

        return $this->json($client, 200, [], ['groups' => ['client:read']]);
    }

    #[Route('/{id}', name: 'api_clients_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Client $client): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::VIEW, $client);

        return $this->json($client, 200, [], ['groups' => ['client:read']]);
    }

    #[Route('/{id}', name: 'api_clients_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function update(Client $client, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::EDIT, $client);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new UpdateClientRequest();
        $dto->firstName = $data['firstName'] ?? null;
        $dto->lastName = $data['lastName'] ?? null;
        $dto->phone = $data['phone'] ?? null;
        $dto->email = $data['email'] ?? null;
        $dto->address = $data['address'] ?? null;
        $dto->postalCode = $data['postalCode'] ?? null;
        $dto->city = $data['city'] ?? null;
        $dto->landlinePhone = $data['landlinePhone'] ?? null;

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => array_map(fn($e) => [
                    'field' => $e->getPropertyPath(),
                    'message' => $e->getMessage(),
                ], iterator_to_array($errors)),
            ], 422);
        }

        $updated = $this->clientService->update($client, $dto);

        return $this->json($updated, 200, [], ['groups' => ['client:read']]);
    }

    #[Route('/{id}/repairs', name: 'api_clients_repairs', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function repairs(Client $client): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::VIEW_REPAIRS, $client);

        return $this->json(
            $client->getRepairOrders(),
            200,
            [],
            ['groups' => ['repair:read']]
        );
    }

    #[Route('/{id}/anonymize', name: 'api_clients_anonymize', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function anonymize(Client $client): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::ANONYMIZE, $client);

        $updated = $this->clientService->anonymize($client);

        return $this->json($updated, 200, [], ['groups' => ['client:read']]);
    }
}