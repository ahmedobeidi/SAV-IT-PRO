<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

class JWTInvalidListener
{
    public function onJWTInvalid(JWTInvalidEvent $event): void
    {
        $event->setResponse(new JWTAuthenticationFailureResponse('Token JWT invalide', 401));
    }
}
