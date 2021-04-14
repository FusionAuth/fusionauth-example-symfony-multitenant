<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FusionauthController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/fusionauth", name="connect_fusionauth_start")
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {

        // will redirect to Facebook!
        return $clientRegistry
            ->getClient('fusionauth') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
	    	'profile', 'email' // the scopes you want to access
            ]);
    }

    /**
     * After going to Fusionauth, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/fusionauth/check", name="connect_fusionauth_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
    }
}
