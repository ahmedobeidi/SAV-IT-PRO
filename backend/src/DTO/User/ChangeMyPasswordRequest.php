<?php

namespace App\DTO\User;

use App\Validator\PasswordRules;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeMyPasswordRequest
{
    #[Assert\NotBlank(message: 'Le mot de passe actuel est obligatoire.')]
    public ?string $currentPassword = null;

    #[Assert\NotBlank(message: 'Le nouveau mot de passe est obligatoire.')]
    #[Assert\Length(min: 8, max: 255)]
    #[Assert\Regex(
        pattern: PasswordRules::REGEX,
        message: PasswordRules::MESSAGE
    )]
    public ?string $newPassword = null;

    #[Assert\NotBlank(message: 'La confirmation du mot de passe est obligatoire.')]
    #[Assert\Expression(
        'this.newPassword === this.confirmPassword',
        message: 'La confirmation du mot de passe ne correspond pas.'
    )]
    public ?string $confirmPassword = null;
}