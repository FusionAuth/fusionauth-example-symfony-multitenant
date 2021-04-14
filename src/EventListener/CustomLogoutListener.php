<?php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\LogoutEvent;

class CustomLogoutListener
{
   public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
    }
}
