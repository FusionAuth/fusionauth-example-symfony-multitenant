<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class CustomLogoutListener
{
    private $fusionauthClientId;
    private $fusionauthBase;

    public function __construct($fusionauthBase, $fusionauthClientId)
    {
        $this->fusionauthBase = $fusionauthBase;
        $this->fusionauthClientId = $fusionauthClientId;
    }

   public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {

        $fusionauth_base = $this->fusionauthBase;
        $client_id = $this->fusionauthClientId;
        $response = new RedirectResponse($fusionauth_base.'/oauth2/logout?client_id='.$client_id);
        $event->setResponse($response);
    }
}
