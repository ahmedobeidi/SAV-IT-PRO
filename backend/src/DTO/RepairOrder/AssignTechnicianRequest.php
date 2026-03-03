<?php

namespace App\DTO\RepairOrder;

use Symfony\Component\Validator\Constraints as Assert;

class AssignTechnicianRequest
{
    #[Assert\NotNull]
    public int $technicianId;
}