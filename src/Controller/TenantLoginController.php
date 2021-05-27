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
       // all handled by authenticator
    }

}
