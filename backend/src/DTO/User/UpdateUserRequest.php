<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserRequest
{
    #[Assert\Length(max: 100)]
    public ?string $firstName = null;

    #[Assert\Length(max: 100)]
    public ?string $lastName = null;

    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\Length(min: 8, max: 255)]
    public ?string $password = null;

    #[Assert\Choice(choices: [
        'ROLE_SUPER_ADMIN',
        'ROLE_ADMIN',
        'ROLE_TECHNICIAN',
        'ROLE_RECEPTION',
    ])]
    public ?string $role = null;

    public ?bool $isActive = null;
}
