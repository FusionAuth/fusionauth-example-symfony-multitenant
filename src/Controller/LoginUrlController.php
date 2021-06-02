<?php
namespace App\Controller;

use App\Entity\Tenant;
use App\Service\LoginUrlService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginUrlController extends AbstractController
{
  /**
  * @Route("/login")
  */
  public function login(LoginUrlService $loginUrlService, Request $request): Response
  {

    $fusionauth_base = $this->getParameter('fusionauth_base');
    $host = $request->getHost();

    // convert ppvcfoo.fusionauth.io to ppvcfoo so we can look up the tenant
    $hostname = str_replace('.fusionauth.io','',$host); // TBD have 'fusionauth.io' be a parameter

    $clientIdAndSecret = $this->retrieveClientIdAndSecret($hostname);

    return $this->redirect($loginUrlService->loginURL($hostname, $clientIdAndSecret[0], $clientIdAndSecret[1], $fusionauth_base));
  }
 
  /**
  * @Route("/register")
  */
  public function register(LoginUrlService $loginUrlService, Request $request): Response
  {
    $fusionauth_base = $this->getParameter('fusionauth_base');
    $host = $request->getHost();

    // convert ppvcfoo.fusionauth.io to ppvcfoo so we can look up the tenant
    $hostname = str_replace('.fusionauth.io','',$host); // TBD have 'fusionauth.io' be a parameter

    $clientIdAndSecret = $this->retrieveClientIdAndSecret($hostname);
    return $this->redirect($loginUrlService->registerURL($hostname, $clientIdAndSecret[0], $clientIdAndSecret[1], $fusionauth_base));
  }

  private function retrieveClientIdAndSecret($hostname): array
  {
    $client_id = '';
    $client_secret = '';

    if ($hostname === $this->getParameter('control_plane_hostname')) {
      $client_id = $this->getParameter('control_plane_client_id');
      $client_secret = $this->getParameter('control_plane_client_secret');
    } else { $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(Tenant::class);
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
 
}


