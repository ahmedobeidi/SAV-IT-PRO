<?php

namespace App\Service\RepairOrder;

use App\Entity\RepairOrder;

class RepairOrderLogFactory
{
    public function snapshot(RepairOrder $r): array
    {
        return [
            'id' => $r->getId(),
            'status' => $r->getStatus()->value,
            'assignedTo' => $r->getAssignedTo()?->getId(),
            'price' => $r->getPrice(),
            'deposit' => $r->getDeposit(),
        ];
    }
}