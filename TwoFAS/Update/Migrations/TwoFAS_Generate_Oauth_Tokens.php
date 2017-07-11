<?php

namespace TwoFAS\Update\Migrations;

use TwoFAS\Update\Deprecated\UserZone\Integration as Deprecated_Integration;
use TwoFAS\Storage\TwoFAS_Storage;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;
use TwoFAS\Update\Deprecated\UserZone\Exception\Exception as Deprecated_User_Zone_Exception;
use TwoFAS\Update\Deprecated\UserZone\UserZone as Deprecated_User_Zone;
use TwoFAS\UserZone\UserZone;

class TwoFAS_Generate_Oauth_Tokens
{
    /**
     * @var TwoFAS_Storage
     */
    private $storage;

    /**
     * @var UserZone
     */
    private $user_zone;

    /**
     * @var array
     */
    private $config;

    /**
     * @param TwoFAS_Storage $storage
     * @param UserZone       $user_zone
     * @param array          $config
     */
    public function __construct(TwoFAS_Storage $storage, UserZone $user_zone, array $config = array()) {
        $this->storage   = $storage;
        $this->user_zone = $user_zone;
        $this->config    = $config;
    }

    /**
     * @return bool
     */
    public function run()
    {
        $options_storage   = $this->storage->get_options();
        $integration_login = $options_storage->get_twofas_integration_login();
        $email             = $options_storage->get_twofas_email();
        $password          = $options_storage->get_twofas_password();

        if (!$integration_login) {
            return false;
        }

        try {
            $deprecated_user_zone = $this->get_deprecated_user_zone($this->storage, $this->config);

            $integrations = $deprecated_user_zone->getIntegrations();

            $integration = $integrations->first(function(Deprecated_Integration $integration) use ($integration_login) {
                return $integration->getLogin() === $integration_login;
            });

            $this->user_zone->generateOAuthSetupToken($email, $password);
            $this->user_zone->generateIntegrationSpecificToken($email, $password, $integration->getId());

            $options_storage->delete_twofas_password();
            $options_storage->delete_twofas_enabled();
        } catch (Deprecated_User_Zone_Exception $e) {
            return false;
        } catch (User_Zone_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param TwoFAS_Storage $storage
     * @param array          $config
     *
     * @return Deprecated_User_Zone
     */
    public function get_deprecated_user_zone(TwoFAS_Storage $storage, array $config)
    {
        $options_storage = $storage->get_options();
        $email           = $options_storage->get_twofas_email();
        $password        = $options_storage->get_twofas_password();
        $headers         = $storage->get_sdk_headers();
        $user_zone       = new Deprecated_User_Zone($email, $password, $headers);

        if (isset($config['user_zone_url'])) {
            $user_zone->setBaseUrl($config['user_zone_url']);
        }

        return $user_zone;
    }
}
