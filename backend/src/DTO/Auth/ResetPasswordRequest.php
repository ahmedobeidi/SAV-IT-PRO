<?php

namespace App\DTO\Auth;

use App\Validator\PasswordRules;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordRequest
{
    #[Assert\NotBlank(message: 'Le token est requis.')]
    public string $token;

    #[Assert\NotBlank(message: 'Le nouveau mot de passe est requis.')]
    #[Assert\Length(min: 8, max: 255)]
    #[Assert\Regex(
        pattern: PasswordRules::REGEX,
        message: PasswordRules::MESSAGE
    )]
    public string $newPassword;
}