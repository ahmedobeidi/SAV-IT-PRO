<?php

namespace App\DTO\RepairOrder;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRepairOrderRequest
{
    #[Assert\NotNull]
    public int $clientId;

    #[Assert\NotNull]
    public int $equipmentModelId;

    #[Assert\NotNull]
    public int $issueId;

    #[Assert\PositiveOrZero]
    public float $price = 0;

    #[Assert\PositiveOrZero]
    public ?float $deposit = null;

    #[Assert\Length(max: 5000)]
    public ?string $description = null;
}