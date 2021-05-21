<?php
namespace App\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class EntryPoint implements AuthenticationEntryPointInterface{
  private $url;
  public function __construct($url){
    $this->url = $url;
  }
  public function start(Request $request, AuthenticationException $authException = null){
      $response = new Response(
        '',
        Response::HTTP_FOUND, //for 302 and Response::HTTP_TEMPORARY_REDIRECT for HTTP307 
        array('Location'=>$this->url)); 
      return $response;
  }
}
