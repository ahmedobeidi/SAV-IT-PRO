<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $firstName;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $lastName;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: [
        'ROLE_SUPER_ADMIN',
        'ROLE_ADMIN',
        'ROLE_TECHNICIAN',
        'ROLE_RECEPTION',
    ])]
    public string $role;
}