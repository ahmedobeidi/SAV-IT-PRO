<?php

namespace App\DTO\EquipmentModel;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEquipmentModelRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public string $name;
}