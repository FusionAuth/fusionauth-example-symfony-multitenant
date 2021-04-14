<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use Symfony\Component\HttpFoundation\RequestStack;

class RegistrationController extends AbstractController
{

   private $requestStack;
   public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
  /**
  * @Route("/register/")
  */
  public function index(): Response
  {
    $fusionauth_base = $this->getParameter('fusionauth_base');
    $client_id = $this->getParameter('fusionauth_client_id');
    $url = urlencode($this->generateUrl('connect_fusionauth_check',array(),UrlGeneratorInterface::ABSOLUTE_URL));
    $state = md5(random_bytes(10));
    $this->requestStack->getCurrentRequest()->getSession()->set(
                OAuth2Client::OAUTH2_SESSION_STATE_KEY,
                $state
            );
    return $this->redirect($fusionauth_base.'/oauth2/register?client_id='.$client_id.'&response_type=code&redirect_uri='.$url.'&state='.$state);
  }
}


