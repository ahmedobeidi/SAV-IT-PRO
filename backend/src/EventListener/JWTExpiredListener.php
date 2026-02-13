<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

class JWTExpiredListener
{
    public function onJWTExpired(JWTExpiredEvent $event): void
    {
        $event->setResponse(new JWTAuthenticationFailureResponse('Token JWT expiré', 401));
    }
}
