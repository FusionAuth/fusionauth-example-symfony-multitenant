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
        $tenant->setApplicationId("abc");
        $tenant->setFusionAuthTenantId("abc");

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
}
