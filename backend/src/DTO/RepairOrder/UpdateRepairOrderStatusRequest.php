<?php

namespace App\DTO\RepairOrder;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateRepairOrderStatusRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [
        'CREATED',
        'IN_PROGRESS',
        'WAITING_PARTS',
        'DONE',
        'DELIVERED',
        'CANCELED',
    ])]
    public string $status;
}