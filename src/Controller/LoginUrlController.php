<?php
namespace App\Controller;

use App\Entity\Tenant;
use App\Service\LoginUrlService;
use App\Service\OauthClientService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginUrlController extends AbstractController
{
  /**
  * @Route("/login")
  */
  public function login(LoginUrlService $loginUrlService, Request $request, OauthClientService $oauthClientService): Response
  {
    $clientIdAndSecret = $oauthClientService->retrieveClientIdAndSecret($request->getHost());

    return $this->redirect($loginUrlService->loginURL($request->getHost(), $clientIdAndSecret[0], $clientIdAndSecret[1]));
  }
 
  /**
  * @Route("/register")
  */
  public function register(LoginUrlService $loginUrlService, Request $request, OauthClientService $oauthClientService): Response
  {

    $clientIdAndSecret = $oauthClientService->retrieveClientIdAndSecret($request->getHost());
    return $this->redirect($loginUrlService->registerURL($request->getHost(), $clientIdAndSecret[0], $clientIdAndSecret[1]));
  }

}


