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
    #[Assert\Length(min: 8, max: 255)]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        message: 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character.'
    )]
    public string $password;

    /**
     * On va envoyer le rôle en string: "ROLE_ADMIN", etc.
     */
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [
        'ROLE_SUPER_ADMIN',
        'ROLE_ADMIN',
        'ROLE_TECHNICIAN',
        'ROLE_RECEPTION',
    ])]
    public string $role;
}
