<?php

namespace App\Controller\Api;

use App\DTO\Client\CreateClientRequest;
use App\DTO\Client\UpdateClientRequest;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Security\Voter\ClientVoter;
use App\Service\Client\ClientService;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/clients',
        summary: 'Créer un client',
        description: 'Crée un nouveau client.',
        tags: ['Clients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Données du client à créer',
        content: new OA\JsonContent(
            required: ['firstName', 'lastName', 'phone'],
            properties: [
                new OA\Property(property: 'firstName', type: 'string', example: 'Jean'),
                new OA\Property(property: 'lastName', type: 'string', example: 'Dupont'),
                new OA\Property(property: 'phone', type: 'string', example: '0612345678'),
                new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'jean.dupont@email.com'),
                new OA\Property(property: 'address', type: 'string', nullable: true, example: '12 rue de Paris'),
                new OA\Property(property: 'postalCode', type: 'string', nullable: true, example: '75001'),
                new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Paris'),
                new OA\Property(property: 'landlinePhone', type: 'string', nullable: true, example: '0144556677'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Client créé avec succès')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès refusé')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Get(
        path: '/api/clients',
        summary: 'Lister les clients',
        description: 'Retourne une liste paginée des clients, avec filtre optionnel par téléphone.',
        tags: ['Clients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'phone', in: 'query', required: false, description: 'Filtre par téléphone', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d’éléments par page', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'Liste des clients récupérée avec succès')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès refusé')]
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
    #[OA\Get(
        path: '/api/clients/search',
        summary: 'Rechercher un client par téléphone',
        description: 'Retourne un client à partir de son numéro de téléphone exact.',
        tags: ['Clients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'phone', in: 'query', required: true, description: 'Numéro de téléphone du client', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Client trouvé')]
    #[OA\Response(response: 400, description: 'Le paramètre phone est requis')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès refusé')]
    #[OA\Response(response: 404, description: 'Client introuvable')]
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
    #[OA\Get(
        path: '/api/clients/{id}',
        summary: 'Afficher un client',
        description: 'Retourne le détail d’un client.',
        tags: ['Clients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du client', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Client récupéré avec succès')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès refusé')]
    #[OA\Response(response: 404, description: 'Client introuvable')]
    public function show(Client $client): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::VIEW, $client);

        return $this->json($client, 200, [], ['groups' => ['client:read']]);
    }

    #[Route('/{id}', name: 'api_clients_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[OA\Patch(
        path: '/api/clients/{id}',
        summary: 'Modifier un client',
        description: 'Met à jour partiellement les informations d’un client.',
        tags: ['Clients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du client', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Données à mettre à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'firstName', type: 'string', nullable: true, example: 'Jean'),
                new OA\Property(property: 'lastName', type: 'string', nullable: true, example: 'Dupont'),
                new OA\Property(property: 'phone', type: 'string', nullable: true, example: '0612345678'),
                new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'jean.dupont@email.com'),
                new OA\Property(property: 'address', type: 'string', nullable: true, example: '12 rue de Paris'),
                new OA\Property(property: 'postalCode', type: 'string', nullable: true, example: '75001'),
                new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Paris'),
                new OA\Property(property: 'landlinePhone', type: 'string', nullable: true, example: '0144556677'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Client mis à jour avec succès')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès refusé')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Get(
        path: '/api/clients/{id}/repairs',
        summary: 'Lister les réparations d’un client',
        description: 'Retourne les ordres de réparation associés à un client.',
        tags: ['Clients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du client', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Réparations récupérées avec succès')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès refusé')]
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
    #[OA\Patch(
        path: '/api/clients/{id}/anonymize',
        summary: 'Anonymiser un client',
        description: 'Anonymise les données personnelles du client.',
        tags: ['Clients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du client', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Client anonymisé avec succès')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès refusé')]
    public function anonymize(Client $client): JsonResponse
    {
        $this->denyAccessUnlessGranted(ClientVoter::ANONYMIZE, $client);

        $updated = $this->clientService->anonymize($client);

        return $this->json($updated, 200, [], ['groups' => ['client:read']]);
    }
}