<?php

namespace App\DTO\EquipmentType;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateEquipmentTypeRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name;
}