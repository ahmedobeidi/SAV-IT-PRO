<?php

namespace App\Service\RepairOrder;

use App\DTO\RepairOrder\AssignTechnicianRequest;
use App\DTO\RepairOrder\CreateRepairOrderRequest;
use App\Entity\Client;
use App\Entity\Issue;
use App\Entity\RepairOrder;
use App\Entity\RepairOrderLog;
use App\Entity\User;
use App\Enum\RepairOrderLogAction;
use App\Enum\RepairOrderStatus;
use App\Enum\UserRole;
use App\Repository\EquipmentModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\DTO\RepairOrder\UpdateRepairOrderRequest;

class RepairOrderService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EquipmentModelRepository $equipmentModelRepo,
        private RepairOrderLogFactory $logFactory,
        private RepairOrderReferenceGenerator $referenceGenerator,
    ) {}

    public function create(User $actor, CreateRepairOrderRequest $dto): RepairOrder
    {
        $client = $this->em->getRepository(Client::class)->find($dto->clientId);
        if (!$client || $client->isAnonymized()) {
            throw new \DomainException('Client introuvable ou anonymisé.');
        }

        $model = $this->equipmentModelRepo->find($dto->equipmentModelId);
        if (!$model) {
            throw new \DomainException('Modèle introuvable.');
        }

        $issue = $this->em->getRepository(Issue::class)->find($dto->issueId);
        if (!$issue) {
            throw new \DomainException('Panne introuvable.');
        }

        $r = new RepairOrder();
        $r->setReference($this->referenceGenerator->next());
        $r->setCreatedBy($actor);
        $r->setCreatedFor($client);
        $r->setEquipmentModel($model);
        $r->setIssue($issue);
        $r->setPrice($dto->price);
        $r->setDeposit($dto->deposit);
        $r->setDescription($dto->description);
        $r->setStatus(RepairOrderStatus::CREATED);
        $r->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($r);

        $this->addLog($r, $actor, RepairOrderLogAction::CREATED);

        $this->em->flush();

        return $r;
    }

    public function update(User $actor, RepairOrder $r, UpdateRepairOrderRequest $dto): RepairOrder
    {
        $issue = $this->em->getRepository(Issue::class)->find($dto->issueId);
        if (!$issue) {
            throw new \DomainException('Panne introuvable.');
        }

        $r->setIssue($issue);
        $r->setPrice($dto->price);
        $r->setDeposit($dto->deposit);
        $r->setDescription($dto->description);
        $r->setUpdatedAt(new \DateTimeImmutable());

        $this->addLog($r, $actor, RepairOrderLogAction::UPDATED);

        $this->em->flush();

        return $r;
    }

    public function assignTechnician(User $actor, RepairOrder $r, AssignTechnicianRequest $dto): RepairOrder
    {
        $tech = $this->em->getRepository(User::class)->find($dto->technicianId);
        if (!$tech || $tech->getRole() !== UserRole::TECHNICIAN) {
            throw new \DomainException('Technicien invalide.');
        }

        $r->setAssignedTo($tech);

        if (in_array($r->getStatus(), [RepairOrderStatus::CREATED, RepairOrderStatus::CANCELED], true)) {
            $r->setStatus(RepairOrderStatus::ASSIGNED);
        }

        $r->setUpdatedAt(new \DateTimeImmutable());

        $this->addLog($r, $actor, RepairOrderLogAction::ASSIGNED);

        $this->em->flush();
        return $r;
    }

    public function updateStatusByStaff(User $actor, RepairOrder $r, RepairOrderStatus $newStatus): RepairOrder
    {
        $r->setStatus($newStatus);
        $r->setUpdatedAt(new \DateTimeImmutable());

        $this->addLog($r, $actor, RepairOrderLogAction::STATUS_CHANGED);

        $this->em->flush();
        return $r;
    }

    public function updateStatusByTechnician(User $actor, RepairOrder $r, RepairOrderStatus $newStatus): RepairOrder
    {
        if ($r->getAssignedTo()?->getId() !== $actor->getId()) {
            throw new \DomainException('Ordre non assigné à ce technicien.');
        }

        if ($newStatus === RepairOrderStatus::DELIVERED) {
            throw new \DomainException('Le technicien ne peut pas marquer livré.');
        }

        $r->setStatus($newStatus);
        $r->setUpdatedAt(new \DateTimeImmutable());

        $this->addLog($r, $actor, RepairOrderLogAction::STATUS_CHANGED);

        $this->em->flush();
        return $r;
    }

    private function addLog(RepairOrder $r, User $actor, \App\Enum\RepairOrderLogAction $action): void
    {
        $log = new RepairOrderLog();
        $log->setRepairOrder($r);
        $log->setChangedBy($actor);
        $log->setAction($action);
        $log->setSnapshot($this->logFactory->snapshot($r));
        $this->em->persist($log);
    }
}
