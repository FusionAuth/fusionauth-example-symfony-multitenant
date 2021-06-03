<?php

// src/EventSubscriber/HostnameSubscriber.php
namespace App\EventSubscriber;

use App\Service\OauthClientService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class HostnameSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $oauthClientService;

    public function __construct(Environment $twig, OauthClientService $oauthClientService)
    {
        $this->twig = $twig;
        $this->oauthClientService = $oauthClientService;
    }

    public function onKernelController(ControllerEvent $event)
    {  
        $isControlPlaneHost = $this->oauthClientService->isControlPlaneHost($event->getRequest()->getHost());
        $this->twig->addGlobal('onControlPlaneApplication', $isControlPlaneHost);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
