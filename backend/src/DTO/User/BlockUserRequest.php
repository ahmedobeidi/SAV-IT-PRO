<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class BlockUserRequest
{
    #[Assert\NotNull]
    public bool $isActive;
}
