<?php

namespace App\DTO\EquipmentBrand;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEquipmentBrandRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name;
}