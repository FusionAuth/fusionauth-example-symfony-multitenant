<?php

namespace App\EventListener;

use App\Entity\Tenant;
use App\Exception\FusionAuthException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use FusionAuth\FusionAuthClient;
use Psr\Log\LoggerInterface;

class TenantChangedNotifier
{
    private $fusionauthKeyManagerKey;
    private $fusionauthBase;
    private $blueprintTenantId;
    private $saasRootDomain;
    private $logger;

    public function __construct($fusionauthBase, $fusionauthKeyManagerKey, LoggerInterface $logger, $blueprintTenantId, $saasRootDomain)
    {
        $this->fusionauthBase = $fusionauthBase;
        $this->blueprintTenantId = $blueprintTenantId;
        $this->fusionauthKeyManagerKey = $fusionauthKeyManagerKey;
        $this->saasRootDomain = $saasRootDomain;
        $this->logger = $logger;
    }

// will only happen on initial creation https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/events.html#lifecycle-events
    public function prePersist(Tenant $tenant, LifecycleEventArgs $event): void
    {
        $client = new FusionAuthClient($this->fusionauthKeyManagerKey, $this->fusionauthBase);

        // set up tenant
        $tenant_id = $this->createFusionAuthTenant($client, $tenant);
        $tenant->setFusionAuthTenantId($tenant_id);

        // set up api tenant key
        $tenant_api_info = $this->createTenantAPIKey($client, $tenant_id, $tenant->getHostname());
        $tenant_api_key_id = $tenant_api_info[0];
        $tenant_api_key = $tenant_api_info[1];
        $tenant->setApiKeyId($tenant_api_key_id);
        $tenant->setApiKey($tenant_api_key);

        // don't use the global client for any other purpose. We should use the created tenant api key now
        $client = null;

        $client = new FusionAuthClient($tenant_api_key, $this->fusionauthBase);

        $application_info = $this->createApplication($client, $tenant->getHostname());
        $application_id = $application_info[0];
        $client_secret = $application_info[1];
        $tenant->setApplicationId($application_id);
        $tenant->setClientSecret($client_secret);
    }

    private function createFusionAuthTenant(FusionAuthClient $client, Tenant $tenant): String
    {
        $result = $client->retrieveTenant($this->blueprintTenantId);
        if (!$result->wasSuccessful()) {
            $this->logger->error('An error occurred!');
            $this->logger->error(var_export($result,TRUE));
            throw new FusionAuthException("Can't save: ".var_export($result,TRUE));
        }

        $blueprint_tenant = $result->successResponse;
        
        // pick off what we know we want to minimize forward compatibility issues.

        $tenant_object = array();
        $tenant_object["name"] = $tenant->getHostname();
        $tenant_object["themeId"] = $blueprint_tenant->tenant->themeId;
        $tenant_object["issuer"] = "https://".$hostname.$this->saasRootDomain;

        $tenant_email_configuration = $this->convertObjectToArray($blueprint_tenant->tenant->emailConfiguration);
        $tenant_object["emailConfiguration"] = $tenant_email_configuration;

        $tenant_jwt_configuration = $this->convertObjectToArray($blueprint_tenant->tenant->jwtConfiguration);
        $tenant_object["jwtConfiguration"] = $tenant_jwt_configuration;

        $tenant_externalId_configuration = $this->convertObjectToArray($blueprint_tenant->tenant->externalIdentifierConfiguration);
        $tenant_object["externalIdentifierConfiguration"] = $tenant_externalId_configuration;

        $tenant_request = array();
        $tenant_request["tenant"] = $tenant_object;

        $result = $client->createTenant('', $tenant_request);
        if (!$result->wasSuccessful()) {
            $this->logger->error('An error occurred!');
            $this->logger->error(var_export($result,TRUE));
            throw new FusionAuthException("Can't save: ".var_export($result,TRUE));
        } // TBD handle duplicates more gracefully

        $new_tenant = $result->successResponse;

        return $new_tenant->tenant->id;
    }

    private function createTenantAPIKey(FusionAuthClient $client, String $fusionauth_tenant_id, String $hostname): array
    {

        $apikey_object = array();
        $apikey_object["metaData"]["attributes"]["description"] = "API key for ".$hostname;
        $apikey_object["tenantId"] = $fusionauth_tenant_id;

        $apikey_request = array();
        $apikey_request["apiKey"] = $apikey_object;

        $result = $client->createAPIKey('', $apikey_request);
        if (!$result->wasSuccessful()) {
            $this->logger->error('An error occurred!');
            $this->logger->error(var_export($result,TRUE));
            throw new FusionAuthException("Can't save: ".var_export($result,TRUE));
        }

        $apikey = $result->successResponse;

        return [$apikey->apiKey->id, $apikey->apiKey->key];
    }

    private function createApplication(FusionAuthClient $client, String $hostname): array
    {

        $application_object = array();
        $application_object["name"] = "Default application for ".$hostname;
        $ppvc_app_base = "https://".$hostname.$this->saasRootDomain;

        $application_oauthconfiguration = array();
        $application_oauthconfiguration["authorizedRedirectURLs"] = [$ppvc_app_base."/login/callback"]; 
        $application_oauthconfiguration["enabledGrants"] = ["authorization_code"];
        $application_oauthconfiguration["logoutURL"] = $ppvc_app_base;
        $application_object["oauthConfiguration"] = $application_oauthconfiguration;

        $application_registrationconfiguration = array();
        $application_registrationconfiguration["enabled"] = true;
        $application_object["registrationConfiguration"] = $application_registrationconfiguration;

        $application_request = array();
        $application_request["application"] = $application_object;

        $result = $client->createApplication('', $application_request);
        if (!$result->wasSuccessful()) {
            $this->logger->error('An error occurred!');
            $this->logger->error(var_export($result,TRUE));
            throw new FusionAuthException("Can't save: ".var_export($result,TRUE));
        }

        $application = $result->successResponse;

        return [$application->application->id, $application->application->oauthConfiguration->clientSecret];
    }

    private function convertObjectToArray($object) {
        return json_decode(json_encode($object));
    }
 
}
