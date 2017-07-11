<?php

namespace TwoFAS\Storage;

use TwoFAS\UserZone\OAuth\TokenNotFoundException;
use WP_User;

class TwoFAS_Storage
{
    /**
     * @var TwoFAS_WP_DB_Options_Storage
     */
    private $options_storage;

    /**
     * @var TwoFAS_WP_Userdata_Storage
     */
    private $user_data_storage;

    /**
     * @var TwoFAS_Cookie_Storage
     */
    private $cookie_storage;

    /**
     * @var TwoFAS_WP_DB_OAuth_Storage
     */
    private $oauth_storage;

    /**
     * @param TwoFAS_Cookie_Storage        $cookie_storage
     * @param TwoFAS_WP_DB_Options_Storage $options_storage
     * @param TwoFAS_WP_Userdata_Storage   $user_meta_storage
     * @param TwoFAS_WP_DB_OAuth_Storage   $oauth_storage
     */
    public function __construct(
        TwoFAS_Cookie_Storage $cookie_storage,
        TwoFAS_WP_DB_Options_Storage $options_storage,
        TwoFAS_WP_Userdata_Storage $user_meta_storage,
        TwoFAS_WP_DB_OAuth_Storage $oauth_storage
    ) {
        $this->cookie_storage    = $cookie_storage;
        $this->options_storage   = $options_storage;
        $this->user_data_storage = $user_meta_storage;
        $this->oauth_storage     = $oauth_storage;
    }

    /**
     * @return TwoFAS_WP_DB_Options_Storage
     */
    public function get_options()
    {
        return $this->options_storage;
    }

    /**
     * @return TwoFAS_WP_Userdata_Storage
     */
    public function get_userdata()
    {
        return $this->user_data_storage;
    }

    /**
     * @return TwoFAS_Cookie_Storage
     */
    public function get_cookie_storage()
    {
        return $this->cookie_storage;
    }

    /**
     * @return TwoFAS_WP_DB_OAuth_Storage
     */
    public function get_oauth()
    {
        return $this->oauth_storage;
    }

    /**
     * @return bool
     */
    public function set_user_as_one_being_authenticated()
    {
        $wp_user = $this->get_wp_user_by_step_token();

        if (!$wp_user) {
            return false;
        }

        $this->user_data_storage->set_user_id($wp_user->ID);

        return true;
    }

    /**
     * @return bool|null|WP_User
     */
    public function get_wp_user_by_step_token()
    {
        $step_token = $this->cookie_storage->get_step_token();

        if (!$step_token) {
            return false;
        }

        $wp_user = $this->user_data_storage->get_user_by_step_token($step_token);

        return $wp_user;
    }

    /**
     * @return bool
     */
    public function set_step_token()
    {
        $token  = md5(uniqid('', true));
        $result = $this->cookie_storage->set_step_token($token);

        if ($result === false) {
            return false;
        }

        return $this->user_data_storage->set_step_token($token);
    }

    /**
     * @return bool
     */
    public function client_completed_registration()
    {
        try {
            $this->oauth_storage->retrieveToken('wordpress');

            return $this->options_storage->get_twofas_email()
                && $this->options_storage->get_twofas_integration_login()
                && $this->options_storage->get_twofas_key_token()
                && $this->options_storage->get_twofas_encryption_key();
        } catch (TokenNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function get_wordpress_version()
    {
        global $wp_version;

        return $wp_version;
    }

    /**
     * @return array
     */
    public function get_sdk_headers()
    {
        return array(
            'Plugin-Version' => TWOFAS_PLUGIN_VERSION,
            'App-Version'    => $this->get_wordpress_version(),
            'App-Name'       => get_bloginfo('name'),
            'App-Url'        => get_site_url()
        );
    }
}
