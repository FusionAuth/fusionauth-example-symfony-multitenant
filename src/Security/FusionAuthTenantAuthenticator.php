<?php 

namespace App\Security;

use App\Entity\Tenant; 
use App\Entity\User; // your user entity
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'app_tenantlogin_login';
    }

    public function authenticate(Request $request): PassportInterface
    {

        $host = $request->getHost();

        // convert ppvcfoo.fusionauth.io to ppvcfoo so we can look up the tenant
        $hostname = str_replace('.fusionauth.io','',$host); // TBD have 'fusionauth.io' be a parameter

        $repository = $this->entityManager->getRepository(Tenant::class);
        $tenant = $repository->findOneBy(array('hostname'=>$hostname));

        $redirect_uri = 'https://'.$host.'/login/callback';

        $fusionauth_base = 'https://local.fusionauth.io'; // TBD inject this $this->getParameter('fusionauth_base');

        $this->provider = new GenericProvider([
            'clientId' => $tenant->getApplicationId(),
            'clientSecret' => $tenant->getClientSecret(),
            'responseResourceOwnerId' => 'sub',
            'redirectUri'  => $redirect_uri,
            'urlAuthorize' => $fusionauth_base.'/oauth2/authorize',
            'urlAccessToken' => $fusionauth_base.'/oauth2/token',
            'urlResourceOwnerDetails' => $fusionauth_base.'/oauth2/userinfo'
        ]);

        if (empty($request->query->get('state')) || (isset($_SESSION['oauth2state']) && $request->query->get('state') !== $_SESSION['oauth2state'])) { // TBD session?
            // throw exception ? TBD
            if (isset($_SESSION['oauth2state'])) {
              unset($_SESSION['oauth2state']);
            }
        }

        // Try to get an access token using the authorization code grant.
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $request->query->get('code')
        ]);

        $credentials = new AccessToken(['access_token' => $accessToken]);

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
                $user->setEmail($email);
                $user->setRoles($fusionAuthUser->toArray()['roles']);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->router->generate('app_chat_index');

        return new RedirectResponse($targetUrl);
    
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
