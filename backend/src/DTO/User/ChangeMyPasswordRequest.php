<?php

namespace App\DTO\User;

use App\Validator\PasswordRules;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
    public ?string $confirmPassword = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->newPassword !== $this->confirmPassword) {
            $context->buildViolation('La confirmation du mot de passe ne correspond pas.')
                ->atPath('confirmPassword')
                ->addViolation();
        }
    }
}