<?php

namespace App\DTO\Issue;

use Symfony\Component\Validator\Constraints as Assert;

class CreateIssueRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    public string $name;
}