<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatController extends AbstractController
{
  /**
  * @Route("/chat/")
  */
  public function index(): Response
  {
    return $this->render('chat/index.html.twig', []);
  }
}


