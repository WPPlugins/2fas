<?php

namespace TwoFAS\Actions;

use TwoFAS\TwoFAS_App;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;

abstract class TwoFAS_User_Creation_Action extends TwoFAS_Action
{
    /**
     * @var array
     */
    private $map_validation_notification = array(
        'validation.required' => 'e-email-required',
        'validation.email'    => 'e-email-validation',
        'validation.unique'   => 'e-email-unique',
    );

    /**
     * @param TwoFAS_App $app
     * @param string     $twofas_email
     * @param string     $twofas_password
     *
     * @throws User_Zone_Exception
     */
    protected function init_twofas_integration(TwoFAS_App $app, $twofas_email, $twofas_password)
    {
        $options_storage = $app->get_storage()->get_options();
        $user_zone       = $app->get_sdk_bridge()->get_user_zone();

        $user_zone->generateOAuthSetupToken($twofas_email, $twofas_password);

        $app->get_uninstaller()->uninstall_except('twofas_oauth_token_setup');

        $twofas_integration_name = $this->generate_integration_name();
        $integration             = $user_zone->createIntegration($twofas_integration_name);

        $user_zone->generateIntegrationSpecificToken($twofas_email, $twofas_password, $integration->getId());

        $key = $user_zone->createKey($integration->getId(), 'twofas-wp-key');

        // Set encryption key
        $options_storage->save_aes_key();

        // Save parameters in wp db
        $options_storage->set_twofas_integration_login($integration->getLogin());
        $options_storage->set_twofas_key_token($key->getToken());

        // Set 2FA globally for WP users
        $options_storage->set_twofas_email($twofas_email);
    }

    /**
     * @return string
     */
    protected function generate_integration_name()
    {
        $twofas_integration_name = parse_url(get_site_url());

        if (!isset($twofas_integration_name['host'])) {
            $twofas_integration_name = parse_url($_SERVER['HTTP_HOST']);
        }

        return $twofas_integration_name['host'];
    }

    /**
     * @param $validation_error
     *
     * @return string
     */
    protected function map_validation_error_to_notification_key($validation_error)
    {
        if (isset($validation_error['email'])
            && isset($this->map_validation_notification[$validation_error['email'][0]])
        ) {
            return $this->map_validation_notification[$validation_error['email'][0]];
        }

        return 'e-generic-error';
    }
}
