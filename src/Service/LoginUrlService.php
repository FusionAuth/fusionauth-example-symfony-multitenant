<?php
namespace App\Service;

use  \League\OAuth2\Client\Provider\GenericProvider;
class LoginUrlService
{
  
  private $fusionauthBase;

  public function __construct(String $fusionauthBase)
  {
    $this->fusionauthBase = $fusionauthBase;
  }
  
  public function loginURL($host, $client_id, $client_secret)
  {
    $provider = new GenericProvider([
      'clientId' => $client_id,
      'clientSecret' => $client_secret,
      'redirectUri'  => $this->redirectURI($host),
      'urlAuthorize' => $this->fusionauthBase.'/oauth2/authorize',
      'urlAccessToken' => $this->fusionauthBase.'/oauth2/token',
      'urlResourceOwnerDetails' => $this->fusionauthBase.'/oauth2/userinfo'
    ]);

    return $provider->getAuthorizationUrl();
  }

  public function registerURL($host, $client_id, $client_secret)
  {

    $provider = new GenericProvider([
      'clientId' => $client_id,
      'clientSecret' => $client_secret,
      'redirectUri'  => $this->redirectURI($host),
      'urlAuthorize' => $this->fusionauthBase.'/oauth2/authorize',
      'urlAccessToken' => $this->fusionauthBase.'/oauth2/token',
      'urlResourceOwnerDetails' => $this->fusionauthBase.'/oauth2/userinfo'
    ]);

    return str_replace('authorize','register',$provider->getAuthorizationUrl());
  }

  public function redirectURI($host) {
    return 'https://'.$host.'/login/callback'; 
  }

  public function logoutURI($clientId) {
    return $this->fusionauthBase.'/oauth2/logout?client_id='.$clientId;
  }

}

