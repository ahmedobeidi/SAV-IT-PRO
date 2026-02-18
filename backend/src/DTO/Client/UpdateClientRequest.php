<?php

namespace App\DTO\Client;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateClientRequest
{
    #[Assert\Length(max: 100)]
    public ?string $firstName = null;

    #[Assert\Length(max: 100)]
    public ?string $lastName = null;

    #[Assert\Length(max: 30)]
    public ?string $phone = null;

    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\Length(max: 2000)]
    public ?string $address = null;

    #[Assert\Length(max: 20)]
    public ?string $postalCode = null;

    #[Assert\Length(max: 100)]
    public ?string $city = null;

    #[Assert\Length(max: 30)]
    public ?string $landlinePhone = null;
}
