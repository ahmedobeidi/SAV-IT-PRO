<?php

namespace App\DTO\RepairOrder;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateRepairOrderStatusRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [
        'CREATED',
        'ASSIGNED',
        'IN_PROGRESS',
        'WAITING_PARTS',
        'DONE',
        'DELIVERED',
        'CANCELED',
    ])]
    public string $status;
}