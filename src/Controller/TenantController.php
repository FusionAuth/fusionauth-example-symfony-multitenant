<?php

// src/Controller/TenantController.php
namespace App\Controller;

use App\Entity\Tenant;
use App\Form\Type\TenantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TenantController extends AbstractController
{
    /**
    * @Route("/tenant/new")
    */
    public function new(Request $request): Response
    {

        // creates a task object and initializes some data for this example
        $tenant = new Tenant();

        $form = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tenant = $form->getData();
            $user = $this->getUser();
            $tenant->setUser($user);

            //dump($form->getData());die; 
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tenant);
            $entityManager->flush();

            return $this->redirectToRoute('app_tenant_success');
        }

        return $this->render('tenant/new.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
    * @Route("/tenant/success")
    */
    public function success(Request $request): Response
    {
        return $this->render('tenant/success.html.twig');
    }

    /**
    * @Route("/tenant/view/{tenantId}")
    */
    public function view(Request $request, int $tenantId): Response
    {
        $repository = $this->getDoctrine()->getRepository(Tenant::class);

 
        $tenant = $repository->find($tenantId);
        $fusionauth_base = $this->getParameter('fusionauth_base');

        $redirect_uri = 'https://'.$tenant->getHostname().'.fusionauth.io/login/callback'; // TBD have fusionauth.io be parameter

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => $tenant->getApplicationId(), 
            'clientSecret' => $tenant->getClientSecret(),
            'redirectUri'  => $redirect_uri,
            'urlAuthorize' => $fusionauth_base.'/oauth2/authorize',
            'urlAccessToken' => $fusionauth_base.'/oauth2/token',
            'urlResourceOwnerDetails' => $fusionauth_base.'/oauth2/userinfo' 
        ]);
 
        $_SESSION['oauth2state'] = $provider->getState();

        // well known url TBD document this?
        $application_registration_url = str_replace('authorize','register',$provider->getAuthorizationUrl());

        return $this->render('tenant/view.html.twig', [
          'tenant' => $tenant,
          'application_login_url' => $provider->getAuthorizationUrl(),
          'application_register_url' => $application_registration_url
        ]);
    }
}
