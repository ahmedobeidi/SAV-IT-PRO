<?php

namespace App\DTO\EquipmentBrand;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateEquipmentBrandRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name;
}