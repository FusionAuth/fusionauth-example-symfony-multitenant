<?php
namespace App\Service;

class LoginUrlService
{
  public function loginURL($hostname, $client_id, $client_secret, $fusionauth_base)
  {
    $redirect_uri = 'https://'.$hostname.'.fusionauth.io/login/callback'; // TBD have fusionauth.io be parameter

    $provider = new \League\OAuth2\Client\Provider\GenericProvider([
      'clientId' => $client_id,
      'clientSecret' => $client_secret,
      'redirectUri'  => $redirect_uri,
      'urlAuthorize' => $fusionauth_base.'/oauth2/authorize',
      'urlAccessToken' => $fusionauth_base.'/oauth2/token',
      'urlResourceOwnerDetails' => $fusionauth_base.'/oauth2/userinfo'
    ]);

    return $provider->getAuthorizationUrl();
  }

  public function registerURL($hostname, $client_id, $client_secret, $fusionauth_base)
  {
    $redirect_uri = 'https://'.$hostname.'.fusionauth.io/login/callback'; // TBD have fusionauth.io be parameter

    $provider = new \League\OAuth2\Client\Provider\GenericProvider([
      'clientId' => $client_id,
      'clientSecret' => $client_secret,
      'redirectUri'  => $redirect_uri,
      'urlAuthorize' => $fusionauth_base.'/oauth2/authorize',
      'urlAccessToken' => $fusionauth_base.'/oauth2/token',
      'urlResourceOwnerDetails' => $fusionauth_base.'/oauth2/userinfo'
    ]);

    return str_replace('authorize','register',$provider->getAuthorizationUrl());
  }

}

