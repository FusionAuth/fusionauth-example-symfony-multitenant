<?php 

namespace App\Security;

use App\Entity\Tenant; 
use App\Entity\User; // your user entity
use App\Service\OauthClientService;
use Doctrine\ORM\EntityManagerInterface;
use \Firebase\JWT\JWT;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class FusionAuthTenantAuthenticator extends AbstractAuthenticator
{
    private $logger;
    private $entityManager;
    private $router;
    private $provider;
    private $oauthClientService;
    private $jwtSigningKey;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, RouterInterface $router, OauthClientService $oauthClientService)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->oauthClientService = $oauthClientService;
        $this->jwtSigningKey = $oauthClientService->getJwtSigningKey();
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'app_tenantlogin_login';
    }

    public function authenticate(Request $request): PassportInterface
    {
        $host = $request->getHost();

        $this->provider = $this->oauthClientService->provider($host);

        if (empty($request->query->get('state')) || (isset($_SESSION['oauth2state']) && $request->query->get('state') !== $_SESSION['oauth2state'])) { // TBD unsure if I should use the superglobal
            if (isset($_SESSION['oauth2state'])) {
              unset($_SESSION['oauth2state']);
            }
            // we don't have a valid user
            throw new CustomUserMessageAuthenticationException('No valid user provided');
        }

        // Try to get an access token using the authorization code grant.
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $request->query->get('code')
        ]);

        // maybe belongs in the oauth service?
        $decodedJwt = JWT::decode($accessToken, $this->jwtSigningKey, array('HS256'));
        
        // map between what league client expects and what fusionauth provides
        $credentials = new AccessToken([
           'access_token' => $accessToken,
           'applicationId' => $decodedJwt->applicationId, 
           'expired' => $decodedJwt->exp, 
           'iss' => $decodedJwt->iss, 
        ]);

        $expectedClientId = $this->oauthClientService->retrieveClientIdAndSecret($host)[0];
        $values = $credentials->getValues();
        $applicationId = $values['applicationId'];
        if ($expectedClientId !== $applicationId) {
            // valid user, but not registered for the fusionauth application
            throw new CustomUserMessageAuthenticationException('User not registered in FusionAuth');
        }

        return new SelfValidatingPassport(
            new UserBadge($credentials, function($credentials) {
                /** @var FusionAuthUser $fusionAuthUser */
                $accessToken = new AccessToken(['access_token' => $credentials]);
                $fusionAuthUser = $this->provider->getResourceOwner($accessToken);

                $email = $fusionAuthUser->toArray()["email"];

                // 1) have they logged in with FusionAuth before? Easy!
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['fusionAuthId' => $fusionAuthUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                // Maybe you just want to "register" them by creating
                // a User object
                $user = new User();
                $user->setFusionAuthId($fusionAuthUser->getId());
                $user->setEmail($fusionAuthUser->getId().$email);
                $user->setRoles($fusionAuthUser->toArray()['roles']);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $host = $request->getHost();

        if ($this->oauthClientService->isControlPlaneHost($host)) {
          $targetUrl = $this->router->generate('app_home_index');
        } else {
          $targetUrl = $this->router->generate('app_chat_index');
        }

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

}
