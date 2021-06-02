<?php

namespace App\EventListener;

use App\Entity\Tenant; 
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class CustomLogoutListener
{
    private $controlPlaneClientId;
    private $controlPlaneHostname;
    private $fusionauthBase;
    private $logger;
    private $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, String $fusionauthBase, String $controlPlaneClientId, String $controlPlaneHostname)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->fusionauthBase = $fusionauthBase;
        $this->controlPlaneClientId = $controlPlaneClientId;
        $this->controlPlaneHostname = $controlPlaneHostname;
    }

   public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
        $host = $event->getRequest()->getHost();

        // convert ppvcfoo.fusionauth.io to ppvcfoo so we can look up the tenant
        $hostname = str_replace('.fusionauth.io','',$host); // TBD have 'fusionauth.io' be a parameter
        $clientId = $this->retrieveClientId($hostname, $this->entityManager);

        $fusionauth_base = $this->fusionauthBase;

        $response = new RedirectResponse($fusionauth_base.'/oauth2/logout?client_id='.$clientId);
        $event->setResponse($response);
    }

  // TBD somewhat duplicated code with the loginurl controller
  private function retrieveClientId($hostname, $entityManager): String
  {
    $client_id = '';

    if ($hostname === $this->controlPlaneHostname) {
      $client_id = $this->controlPlaneClientId;
    } else { 
      $repository = $entityManager->getRepository(Tenant::class);
      $tenant = $repository->findOneBy(array('hostname'=>$hostname));
      if ($tenant) {
        $client_id = $tenant->getApplicationId();
      } else {
        // TBD what if someone is probing our allowed hostnames
      }
    }
    return $client_id;
  }
}
