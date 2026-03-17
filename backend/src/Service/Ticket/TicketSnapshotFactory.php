<?php

namespace App\Service\Ticket;

use App\Entity\RepairOrder;

class TicketSnapshotFactory
{
    public function create(RepairOrder $repairOrder): array
    {
        return [
            'reference' => $repairOrder->getReference(),
            'status' => $repairOrder->getStatus()->value,
            'statusLabel' => $repairOrder->getStatus()->label(),
            'client' => [
                'id' => $repairOrder->getCreatedFor()->getId(),
                'firstName' => $repairOrder->getCreatedFor()->getFirstName(),
                'lastName' => $repairOrder->getCreatedFor()->getLastName(),
                'phone' => $repairOrder->getCreatedFor()->getPhone(),
                'email' => $repairOrder->getCreatedFor()->getEmail(),
            ],
            'createdBy' => [
                'id' => $repairOrder->getCreatedBy()->getId(),
                'firstName' => $repairOrder->getCreatedBy()->getFirstName(),
                'lastName' => $repairOrder->getCreatedBy()->getLastName(),
            ],
            'assignedTo' => $repairOrder->getAssignedTo() ? [
                'id' => $repairOrder->getAssignedTo()->getId(),
                'firstName' => $repairOrder->getAssignedTo()->getFirstName(),
                'lastName' => $repairOrder->getAssignedTo()->getLastName(),
            ] : null,
            'equipmentModel' => [
                'id' => $repairOrder->getEquipmentModel()->getId(),
                'name' => $repairOrder->getEquipmentModel()->getName(),
            ],
            'issue' => [
                'id' => $repairOrder->getIssue()->getId(),
                'name' => $repairOrder->getIssue()->getName(),
            ],
            'price' => $repairOrder->getPrice(),
            'deposit' => $repairOrder->getDeposit(),
            'description' => $repairOrder->getDescription(),
            'createdAt' => $repairOrder->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $repairOrder->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    public function hash(RepairOrder $repairOrder): string
    {
        return hash(
            'sha256',
            json_encode(
                $this->create($repairOrder),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
            )
        );
    }
}