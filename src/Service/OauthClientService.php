<?php
namespace App\Service;

use App\Entity\Tenant;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class OauthClientService 
{
    private $logger;
    private $entityManager;
    private $provider;
    private $fusionauthBase;
    private $controlPlaneClientId;
    private $controlPlaneClientSecret;
    private $controlPlaneHostname;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, String $fusionauthBase, String $controlPlaneClientId, String $controlPlaneClientSecret, String $controlPlaneHostname, LoginUrlService $loginUrlService)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->fusionauthBase = $fusionauthBase;
        $this->controlPlaneClientId = $controlPlaneClientId;
        $this->controlPlaneClientSecret = $controlPlaneClientSecret;
        $this->controlPlaneHostname = $controlPlaneHostname;
        $this->loginUrlService = $loginUrlService;
    }

  public function retrieveClientId(String $host): String
  {
    return $this->retrieveClientIdAndSecret($host)[0];
  }

  public function retrieveClientIdAndSecret(String $host): array
  {
    $client_id = '';
    $client_secret = '';

    if ($this->isControlPlaneHost($host)) {
      $client_id = $this->controlPlaneClientId;
      $client_secret = $this->controlPlaneClientSecret;
    } else { 
      $hostname = $this->hostname($host);
      $repository = $this->entityManager->getRepository(Tenant::class);
      $tenant = $repository->findOneBy(array('hostname'=>$hostname));
      if ($tenant) {
        $client_id = $tenant->getApplicationId();
        $client_secret = $tenant->getClientSecret();
      } else {
        // TBD what if someone is probing our allowed hostnames
      }
    } 
    return [$client_id, $client_secret];
  }

  public function isControlPlaneHost($host) {
    $hostname = $this->hostname($host);
    return ($hostname === $this->controlPlaneHostname);
  }

  public function hostname($host) {
    return str_replace('.fusionauth.io','',$host); // TBD have 'fusionauth.io' be a parameter
  }

  public function provider($host) {
    $clientIdAndSecret = $this->retrieveClientIdAndSecret($host);
    $clientId = $clientIdAndSecret[0];
    $clientSecret = $clientIdAndSecret[1];

    return new GenericProvider([
      'clientId' => $clientId,
      'clientSecret' => $clientSecret,
      'responseResourceOwnerId' => 'sub',
      'redirectUri'  => $this->loginUrlService->redirectURI($host),
      'urlAuthorize' => $this->fusionauthBase.'/oauth2/authorize',
      'urlAccessToken' => $this->fusionauthBase.'/oauth2/token',
      'urlResourceOwnerDetails' => $this->fusionauthBase.'/oauth2/userinfo'
    ]);
  }
}


