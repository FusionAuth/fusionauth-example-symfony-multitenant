<?php

// src/EventSubscriber/HostnameSubscriber.php
namespace App\EventSubscriber;

use App\Controller\TokenAuthenticatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class HostnameSubscriber implements EventSubscriberInterface
{
    private $controlPlaneHostname;
    private $twig;

    public function __construct($controlPlaneHostname, Environment $twig)
    {
        $this->controlPlaneHostname = $controlPlaneHostname;
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $host = $event->getRequest()->getHost();
        $hostname = str_replace('.fusionauth.io','',$host); // TBD have 'fusionauth.io' be a parameter

        $this->twig->addGlobal('onControlPlaneApplication', $hostname === $this->controlPlaneHostname);

    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
