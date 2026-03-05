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
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        message: 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character.'
    )]
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
