<?php

// src/Controller/TenantController.php
namespace App\Controller;

use App\Entity\Tenant;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TenantLoginController extends AbstractController
{
    /**
    * @Route("/login/callback")
    */
    public function login(Request $request, LoggerInterface $logger): Response
    {
        $host = $request->getHost();
        $logger->error('An error occurred:'.$host);

        // convert ppvcfoo.fusionauth.io to ppvcfoo so we can look up the tenant
        $hostname = str_replace('.fusionauth.io','',$host); // TBD have 'fusionauth.io' be a parameter

        $repository = $this->getDoctrine()->getRepository(Tenant::class);
        $tenant = $repository->findOneBy(array('hostname'=>$hostname));

        $redirect_uri = 'https://'.$host.'/login/callback';

        $fusionauth_base = $this->getParameter('fusionauth_base');

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => $tenant->getApplicationId(), 
            'clientSecret' => $tenant->getClientSecret(),
            'redirectUri'  => $redirect_uri,
            'urlAuthorize' => $fusionauth_base.'/oauth2/authorize',
            'urlAccessToken' => $fusionauth_base.'/oauth2/token',
            'urlResourceOwnerDetails' => $fusionauth_base.'/oauth2/userinfo' 
        ]);
        
        if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
        
          if (isset($_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
          }
            
          return $this->redirectToRoute('app_chat_index');// TBD should sent to error
        }
        
        try {
        
          // Try to get an access token using the authorization code grant.
          $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
          ]);
        
          // Using the access token, we may look up details about the
          // resource owner.
          $resourceOwner = $provider->getResourceOwner($accessToken);
          //$logger->error(implode(",",$resourceOwner->toArray()));
        
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        
          // Failed to get the access token or user details.
          exit($e->getMessage());
        
        }
        return $this->redirectToRoute('app_chat_index');

    }

}
