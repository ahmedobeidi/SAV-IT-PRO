<?php

namespace App\DTO\EquipmentType;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEquipmentTypeRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name;
}