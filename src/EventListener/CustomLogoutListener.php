<?php

namespace App\EventListener;

use App\Service\LoginUrlService;
use App\Service\OauthClientService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class CustomLogoutListener
{
    private $logger;
    private $entityManager;
    private $oauthClientService;
    private $loginUrlService;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, OauthClientService $oauthClientService, LoginUrlService $loginUrlService)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->oauthClientService = $oauthClientService;
        $this->loginUrlService = $loginUrlService;
    }

   public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
        $host = $event->getRequest()->getHost();

        $clientId = $this->oauthClientService->retrieveClientId($host);

        $response = new RedirectResponse($this->loginUrlService->logoutURI($clientId));
        $event->setResponse($response);
    }

}
