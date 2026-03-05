<?php

namespace App\Service\Equipment;

use App\DTO\Issue\CreateIssueRequest;
use App\DTO\Issue\UpdateIssueRequest;
use App\Entity\EquipmentType;
use App\Entity\Issue;
use App\Repository\IssueRepository;
use Doctrine\ORM\EntityManagerInterface;

class IssueService
{
    public function __construct(
        private EntityManagerInterface $em,
        private IssueRepository $repo
    ) {}

    public function create(EquipmentType $type, CreateIssueRequest $dto): Issue
    {
        $name = trim($dto->name);

        if ($this->repo->existsByNameForType($type, $name)) {
            throw new \DomainException('Cette panne existe déjà pour ce type d’équipement.');
        }

        $issue = new Issue();
        $issue->setName($name);
        $issue->setEquipmentType($type);

        $this->em->persist($issue);
        $this->em->flush();

        return $issue;
    }

    public function update(Issue $issue, UpdateIssueRequest $dto): Issue
    {
        $name = trim($dto->name);
        $type = $issue->getEquipmentType();

        if ($this->repo->existsByNameForType($type, $name, $issue->getId())) {
            throw new \DomainException('Cette panne existe déjà pour ce type d’équipement.');
        }

        $issue->setName($name);
        $issue->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $issue;
    }

    public function delete(Issue $issue): void
    {
        // ✅ YOUR IMPORTANT RULE
        if ($issue->getRepairOrders()->count() > 0) {
            throw new \DomainException('Impossible de supprimer : des ordres de réparation sont liés à cette panne.');
        }

        $this->em->remove($issue);
        $this->em->flush();
    }
}