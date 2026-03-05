<?php

namespace App\DTO\Issue;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateIssueRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    public string $name;
}