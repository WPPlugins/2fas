<?php

namespace TwoFAS\Templates;

use Twig_SimpleFunction;
use TwoFAS\Storage\TwoFAS_Storage;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use Twig_Environment;
use Twig_Loader_Filesystem;
use TwoFAS\Browser\TwoFAS_Browser;

class TwoFAS_Template
{
    /**
     * @var string
     */
    private $css_path;

    /**
     * @var string
     */
    private $templates_path;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var TwoFAS_Storage
     */
    private $storage;

    /**
     * @var array
     */
    private $config;

    /**
     * @param TwoFAS_Storage $storage
     * @param array          $config
     * @param string         $relative_templates_path
     * @param string         $relative_css_path
     */
    public function __construct(TwoFAS_Storage $storage, array $config, $relative_templates_path = 'Views', $relative_css_path = '')
    {
        $this->storage        = $storage;
        $this->templates_path = __DIR__ . DIRECTORY_SEPARATOR . $relative_templates_path;
        $this->css_path       = __DIR__ . DIRECTORY_SEPARATOR . $relative_css_path;
        $this->config         = $config;

        // Twig initialization
        $twig_loader = new Twig_Loader_Filesystem(__DIR__. DIRECTORY_SEPARATOR.$relative_templates_path);
        $this->twig  = new Twig_Environment($twig_loader);

        $this->twig->addFunction(new Twig_SimpleFunction('twofas_action_url', array($this, 'generate_url_to_action')));
        $this->twig->addFunction(new Twig_SimpleFunction('twofas_action_nonce', array($this, 'generate_nonce_field_for_action')));
        $this->twig->addFunction(new Twig_SimpleFunction('init_thickbox', array($this, 'init_thickbox')));
        $this->twig->addFunction(new Twig_SimpleFunction('render_dynamic_notification', array($this, 'render_dynamic_notification')));
        $this->twig->addFunction(new Twig_SimpleFunction('describe_device', array($this, 'describe_device')));
        $this->twig->addFunction(new Twig_SimpleFunction('timestamp_to_wp_datetime', array($this, 'timestamp_to_wp_datetime')));
        $this->twig->addFunction(new Twig_SimpleFunction('generate_link_to_action', array($this, 'generate_link_to_action')));
        $this->twig->addFunction(new Twig_SimpleFunction('display_header', array($this, 'display_header')));
        $this->twig->addFunction(new Twig_SimpleFunction('login_footer', array($this, 'login_footer')));
        $this->twig->addFunction(new Twig_SimpleFunction('display_not_configured_notification', array($this, 'display_not_configured_notification')));
        $this->twig->addFunction(new Twig_SimpleFunction('phone_number_ending', array($this, 'get_phone_number_ending')));
        $this->twig->addFunction(new Twig_SimpleFunction('get_dashboard_url', array($this, 'get_dashboard_url')));
    }

    public function login_footer()
    {
        login_footer();
    }

    /**
     * @param string $action
     * @param string $text
     * @param string $view
     *
     * @return string
     */
    public static function generate_link_to_action($action, $text, $view = '')
    {
        if (!$view) {
            $matches = array();
            preg_match('/page=([a-zA-Z\-]+)\&*/', $_SERVER['REQUEST_URI'], $matches);

            if (!isset($matches[1])) {
                return '<a href="#">' . $text . '</a>';
            }

            $view = $matches[1];
        }

        $url = get_admin_url() . 'admin.php?page=' . $view . '&twofas-action=' . $action;

        return '<a href="' . $url . '">' . $text . '</a>';
    }

    /**
     * @param int $timestamp
     *
     * @return string
     */
    public function timestamp_to_wp_datetime($timestamp)
    {
        $stamp     = new \DateTime("@" . $timestamp);
        $time_zone = get_option('timezone_string');

        if (!$time_zone) {
            $time_zone = 'UTC';
        }

        $stamp->setTimezone(new \DateTimeZone($time_zone));
        $time = $stamp->format(get_option('time_format'));
        $date = $stamp->format(get_option('date_format'));

        return $date . ' ' . $time;
    }

    /**
     * @param string $user_agent
     *
     * @return string
     */
    public function describe_device($user_agent)
    {
        $twofas_browser = new TwoFAS_Browser($user_agent);
        return $twofas_browser->describe();
    }

    /**
     * @param string $action
     *
     * @return string
     */
    public function generate_url_to_action($action)
    {
        return get_admin_url() . 'admin.php?page=' . $_GET['page'] . '&twofas-action=' . $action;
    }

    /**
     * @param string $action
     *
     * @return string
     */
    public function generate_nonce_field_for_action($action)
    {
        return wp_nonce_field($action);
    }

    public function init_thickbox()
    {
        add_thickbox();
    }

    /**
     * @return string
     */
    public function display_not_configured_notification()
    {
        $user_data = $this->storage->get_userdata();

        if ($this->storage->client_completed_registration()
            && $user_data->get_totp_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_NOT_CONFIGURED
            && $user_data->get_sms_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_NOT_CONFIGURED
            && $user_data->get_call_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_NOT_CONFIGURED
            && !preg_match('/twofas-configure-totp/', $_SERVER['REQUEST_URI'])
            && !preg_match('/twofas-configure-sms/', $_SERVER['REQUEST_URI'])
            && !preg_match('/twofas-configure-call/', $_SERVER['REQUEST_URI'])
            && !preg_match('/twofas-update-password/', $_SERVER['REQUEST_URI'])
        ) {
            return '<div class="notice notice-error is-dismissible error"><p>2FAS plugin has not been configured</p></div>';
        }

        return '';
    }


    /**
     * @param $error
     */
    public function display_header($error)
    {
        if ($error) {
            $wp_error = new \WP_Error($error['key'], $error['message']);
            login_header('Enter your token', '', $wp_error);
        } else {
            login_header('Enter your token', '', '');
        }
    }

    /**
     * @param string $template_name
     * @param array  $params
     *
     * @return string
     */
    public function render_template($template_name, $params = array())
    {
        $params['twofas_plugin_path'] = TWOFAS_PLUGIN_PATH;
        $params['twofas_admin_path']  = TWOFAS_WP_ADMIN_PATH;
        $params['login_url']          = wp_login_url();

        return $this->twig->render($template_name, $params);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function render_dynamic_notification($content)
    {
        $html = '';

        $html .= '<div id="twofas-dynamic-notification" class="notice notice-error is-dismissible error"><p>' . $content . '</p>';

        global $wp_version;

        if (version_compare($wp_version, '4.2', '>=')) {
            $html .= '<button id = "twofas-not-close-button" type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public static function sanitize_string($str)
    {
        return trim(htmlentities(strip_tags($str)));
    }

    /**
     * @param string $phone_number
     *
     * @return string
     */
    public function get_phone_number_ending($phone_number)
    {
        return substr($phone_number, -3);
    }

    /**
     * @return string
     */
    public function get_dashboard_url()
    {
        return $this->config['dashboard_url'];
    }

    /**
     * @param array  $array
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    protected function get_param_or_default(array $array, $key, $default = '')
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        return $default;
    }
}
