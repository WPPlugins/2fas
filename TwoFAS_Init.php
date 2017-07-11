<?php

defined('ABSPATH') or die();

require_once(__DIR__ . '/vendor/autoload.php');

use TwoFAS\Ajax\TwoFAS_Authenticate_Via_Phone;
use TwoFAS\Ajax\TwoFAS_Reload_Qr_Code;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Authentication\TwoFAS_Authenticate;
use TwoFAS\Authentication\TwoFAS_Login_Form_Footer;
use TwoFAS\Channels\TwoFAS_Channel_Factory;
use TwoFAS\Notifications\TwoFAS_Admin_Notification;
use TwoFAS\Request\TwoFAS_Request;
use TwoFAS\Roles\TwoFAS_Role;
use TwoFAS\SDK\TwoFAS_SDK_Bridge;
use TwoFAS\Storage\TwoFAS_Cookie_Storage;
use TwoFAS\Storage\TwoFAS_Storage;
use TwoFAS\Storage\TwoFAS_WP_DB_OAuth_Storage;
use TwoFAS\Storage\TwoFAS_WP_DB_Options_Storage;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\Templates\TwoFAS_Template;
use TwoFAS\TwoFAS_App;
use TwoFAS\Update\Migrations\TwoFAS_Generate_Oauth_Tokens;
use TwoFAS\Update\Migrations\TwoFAS_Update_Encryption_Key;
use TwoFAS\Update\TwoFAS_Plugin_Version;
use TwoFAS\Update\TwoFAS_Updater;
use TwoFAS\UserZone\OAuth\TokenType;
use TwoFAS\UserZone\UserZone;

class TwoFAS_Init
{
    public function start()
    {
        $this->define_constants();
        $this->enqueue_css_and_js();

        $config                = $this->get_config();
        $storage               = $this->get_storage();
        $twofas                = $this->get_api($storage, $config);
        $user_zone             = $this->get_user_zone($storage, $config);
        $sdk_bridge            = new TwoFAS_SDK_Bridge($twofas, $user_zone);
        $request               = new TwoFAS_Request();
        $plugin_version        = new TwoFAS_Plugin_Version($storage->get_options());
        $update_encryption_key = new TwoFAS_Update_Encryption_Key($storage, $twofas);
        $generate_oauth_tokens = new TwoFAS_Generate_Oauth_Tokens($storage, $user_zone, $config);
        $twofas_updater        = new TwoFAS_Updater($plugin_version, $update_encryption_key, $generate_oauth_tokens);
        $channel_factory       = new TwoFAS_Channel_Factory($twofas, $storage);
        $template              = new TwoFAS_Template($storage, $config);
        $uninstaller           = new TwoFAS_Uninstaller();

        // Do plugin update
        $twofas_updater->update_plugin();

        // Init roles used by plugin
        $roles = new TwoFAS_Role();

        TwoFAS_Admin_Notification::display_no_2fas_account_admin_notice($storage->get_options());

        $app = new TwoFAS_App(
            $storage,
            $request,
            $sdk_bridge,
            $uninstaller,
            $channel_factory,
            $template
        );

        $app->run();

        $twofas_authenticate = new TwoFAS_Authenticate($twofas, $storage, $channel_factory, $request, $template);

        add_filter('authenticate', array($twofas_authenticate, 'authenticate'), 100, 1);

        $twofas_footer = new TwoFAS_Login_Form_Footer();
        $twofas_footer->init();

        // Handle AJAX request
        $reload_qr_code         = new TwoFAS_Reload_Qr_Code($app);
        $authenticate_via_phone = new TwoFAS_Authenticate_Via_Phone($app);

        add_action('wp_ajax_twofas_reload_qr_code', array($reload_qr_code, 'handle'));
        add_action('wp_ajax_twofas_authenticate_via_phone', array($authenticate_via_phone, 'handle'));
    }

    /**
     * @return TwoFAS_Storage
     */
    private function get_storage()
    {
        $cookie_storage    = new TwoFAS_Cookie_Storage();
        $options_storage   = new TwoFAS_WP_DB_Options_Storage();
        $user_meta_storage = new TwoFAS_WP_Userdata_Storage();
        $oauth_storage     = new TwoFAS_WP_DB_OAuth_Storage();

        return new TwoFAS_Storage($cookie_storage, $options_storage, $user_meta_storage, $oauth_storage);
    }

    /**
     * @param TwoFAS_Storage $storage
     * @param array          $config
     *
     * @return TwoFAS
     */
    private function get_api(TwoFAS_Storage $storage, array $config)
    {
        $options_storage = $storage->get_options();
        $login           = $options_storage->get_twofas_integration_login();
        $key             = $options_storage->get_twofas_key_token();
        $headers         = $storage->get_sdk_headers();
        $twofas          = new TwoFAS($login, $key, $headers);

        if (isset($config['api_url'])) {
            $twofas->setBaseUrl($config['api_url']);
        }

        return $twofas;
    }

    /**
     * @param TwoFAS_Storage $storage
     * @param array          $config
     *
     * @return UserZone
     */
    private function get_user_zone(TwoFAS_Storage $storage, array $config)
    {
        $oauth_storage = $storage->get_oauth();
        $headers       = $storage->get_sdk_headers();
        $user_zone     = new UserZone($oauth_storage, TokenType::wordpress(), $headers);

        if (isset($config['user_zone_url'])) {
            $user_zone->setBaseUrl($config['user_zone_url']);
        }

        return $user_zone;
    }

    private function enqueue_css_and_js()
    {
        add_action('login_enqueue_scripts', array($this, 'enqueue_jquery_and_main_css'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin'));
    }

    public function enqueue_jquery_and_main_css()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_style('twofas-main-css', TWOFAS_PLUGIN_PATH . '/includes/css/twofas.css', array(), TWOFAS_PLUGIN_VERSION);
    }

    public function enqueue_admin()
    {
        $this->enqueue_jquery_and_main_css();

        wp_enqueue_style('twofas-icons', TWOFAS_PLUGIN_PATH . '/includes/css/twofas_icons.css', array(), TWOFAS_PLUGIN_VERSION);

        global $wp_version;

        if (version_compare($wp_version, '3.8', '<')) {
            wp_enqueue_style('twofas-old-wp', TWOFAS_PLUGIN_PATH . '/includes/css/wp-less-than-38.css', array(), TWOFAS_PLUGIN_VERSION);
        }

        if (version_compare($wp_version, '4.2', '<')) {
            wp_enqueue_style('twofas-old-notifications', TWOFAS_PLUGIN_PATH . '/includes/css/wp-less-than-42.css', array(), TWOFAS_PLUGIN_VERSION);
        }

        wp_enqueue_style('twofas-phone-number-css', TWOFAS_PLUGIN_PATH . '/includes/css/intlTelInput.css');
        wp_enqueue_script('twofas-phone-number-js', TWOFAS_PLUGIN_PATH . '/includes/js/intlTelInput.js');
    }

    private function define_constants()
    {
        define('TWOFAS_PLUGIN_PATH', plugins_url() . DIRECTORY_SEPARATOR . dirname(plugin_basename(__FILE__)));
        define('TWOFAS_PLUGIN_VERSION', '1.2.2');
        define('TWOFAS_WP_ADMIN_PATH', get_admin_url());
    }

    /**
     * @return array
     */
    private function get_config()
    {
        $config = array();

        if (file_exists(__DIR__ . '/twofas_config.php')) {
            $config = include __DIR__ . '/twofas_config.php';
        }

        return $config;
    }
}
