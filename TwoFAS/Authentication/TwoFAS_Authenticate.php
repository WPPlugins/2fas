<?php

namespace TwoFAS\Authentication;

use InvalidArgumentException;
use TwoFAS\Api\Authentication;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\Exception\Exception as Api_Exception;
use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\Channels\TwoFAS_Authentication_Limit_Exceeded_Exception;
use TwoFAS\Channels\TwoFAS_Call_Channel;
use TwoFAS\Channels\TwoFAS_Channel_Factory;
use TwoFAS\Channels\TwoFAS_Totp_Channel;
use TwoFAS\Request\TwoFAS_Request;
use TwoFAS\Templates\TwoFAS_Template;
use TwoFAS\Templates\Views\TwoFAS_Views;
use WP_User;
use WP_Error;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Storage\TwoFAS_Storage;
use TwoFAS\Channels\TwoFAS_Channel;
use TwoFAS\Devices\TwoFAS_Trusted_Device;

class TwoFAS_Authenticate
{
    const TWOFAS_CODE            = 'twofas_code';
    const TWOFAS_ACTION          = 'twofas-action';
    const STOP_LOGIN_PROCESS     = 'stop-login-process';
    const RESEND_CODE            = 'resend-code';
    const TWOFAS_REMEMBER_DEVICE = 'twofas_remember_device';

    /**
     * @var TwoFAS
     */
    private $twofas;

    /**
     * @var TwoFAS_Storage
     */
    private $storage;

    /**
     * @var TwoFAS_Channel_Factory
     */
    private $channel_factory;

    /**
     * @var TwoFAS_Request
     */
    private $request;

    /**
     * @var TwoFAS_Template
     */
    private $template;

    /**
     * @param TwoFAS                 $twofas
     * @param TwoFAS_Storage         $storage
     * @param TwoFAS_Channel_Factory $channel_factory
     * @param TwoFAS_Request         $request
     * @param TwoFAS_Template        $template
     */
    public function __construct(
        TwoFAS                 $twofas,
        TwoFAS_Storage         $storage,
        TwoFAS_Channel_Factory $channel_factory,
        TwoFAS_Request         $request,
        TwoFAS_Template        $template
    ) {
        $this->twofas          = $twofas;
        $this->storage         = $storage;
        $this->channel_factory = $channel_factory;
        $this->request         = $request;
        $this->template        = $template;
    }

    /**
     * @param $user
     *
     * @return mixed
     */
    public function authenticate($user)
    {
        $wp_user = $this->storage->get_wp_user_by_step_token();

        if (!$this->is_wp_user($wp_user)) {
            if (!$this->is_wp_user($user)) {
                return $user;
            }

            $wp_user = $user;

            if (!$this->create_step_token($wp_user)) {
                return new WP_Error('twofas_step_token_error', 'Could not create step token cookie');
            }
        }

        try {
            $channel = $this->get_active_channel($wp_user);
        } catch (Api_Exception $e) {
            $this->reset_step_token($wp_user);
            return new WP_Error('twofas_error', 'Something went wrong. Please try again.');
        }

        if (!$channel) {
            $this->reset_step_token($wp_user);
            return $wp_user;
        }

        if ($this->is_external_login()) {
            $url = $this->get_redirect_to_url();
            wp_redirect($url);
            exit;
        }

        if ($this->user_wants_stop_login_process()) {
            $this->reset_step_token($wp_user);
            $this->redirect_to_login_page();
        }

        if ($channel->is_premium()
            && $this->user_wants_resend_code()
            && $this->user_has_open_authentication($wp_user)
        ) {
            try {
                $channel->open_authentication();

                if ($channel instanceof TwoFAS_Call_Channel) {
                    $message = 'Call with a code is being made';
                } else {
                    $message = 'Code has been resent';
                }

                $this->render_token_form($channel, '', '', array('info_message' => $message));
            } catch (TwoFAS_Authentication_Limit_Exceeded_Exception $e) {
                $this->block_user($wp_user);
                $this->reset_step_token($wp_user);
                $channel->close_authentication();

                return new WP_Error('twofas_blocked_user', 'Attempt limit exceeded. Your account has been blocked for 5 minutes.');
            } catch(Api_Exception $e) {
                $this->reset_step_token($wp_user);
                return new WP_Error('twofas_error', 'Something went wrong. Please try again.');
            }
        }

        if ($this->user_has_current_device_on_trusted_device_list($wp_user)) {
            $this->reset_step_token($wp_user);
            return $wp_user;
        }

        if ($this->is_user_blocked($wp_user)) {
            $this->reset_step_token($wp_user);
            return new WP_Error('twofas_blocked_user', 'Attempt limit exceeded. Your account has been blocked for 5 minutes.');
        }

        if ($this->user_has_open_authentication($wp_user) && !$this->user_has_valid_authentication($wp_user)) {
            $this->reset_step_token($wp_user);
            $channel->close_authentication();
            return new WP_Error('twofas_authentication_expired', 'Your authentication session has expired. Please log in again.');
        }

        if ($channel->is_premium() && !$this->user_has_open_authentication($wp_user)) {
            try {
                $channel->open_authentication();
            } catch (Api_Exception $e) {
                $this->reset_step_token($wp_user);
                return new WP_Error('twofas_error', 'Something went wrong. Please try again.');
            }
        }

        $code = $this->get_code();

        if (is_null($code)) {
            $this->render_token_form($channel, '', '');
        }

        if ($channel instanceof TwoFAS_Totp_Channel
            && !$this->user_has_open_authentication($wp_user)
        ) {
            try {
                $channel->open_authentication();
            } catch (Api_Exception $e) {
                $this->reset_step_token($wp_user);
                return new WP_Error('twofas_error', 'Something went wrong. Please try again.');
            }
        }

        $code_state = new TwoFAS_Code_State($code);

        if ($code_state->is_empty()) {
            if ($channel->is_totp()) {
                $this->render_token_form($channel, 'twofas_empty_code', 'Token cannot be empty');
            } else {
                $this->render_token_form($channel, 'twofas_empty_code', 'Code cannot be empty');
            }
        }

        if (!$code_state->has_valid_pattern()) {
            if ($channel->is_totp()) {
                $this->render_token_form($channel, 'twofas_invalid_code_pattern', 'Token is not in a valid format');
            } else {
                $this->render_token_form($channel, 'twofas_invalid_code_pattern', 'Code is not in a valid format');
            }
        }

        $code_state->set_twofas($this->twofas);

        $authentications = $this->get_authentications($wp_user);

        if ($code_state->rejected_can_retry($authentications)) {
            if ($channel->is_totp()) {
                $this->render_token_form($channel, 'twofas_invalid_code', 'Token is invalid');
            } else {
                $this->render_token_form($channel, 'twofas_invalid_code', 'Code is invalid');
            }
        }

        if ($code_state->rejected_cannot_retry($authentications)) {
            $this->block_user($wp_user);
            $this->reset_step_token($wp_user);
            $channel->close_authentication();
            return new WP_Error('twofas_blocked_user', 'Attempt limit exceeded. Your account has been blocked for 5 minutes.');
        }

        if ($code_state->error()) {
            $this->reset_step_token($wp_user);
            $channel->close_authentication();
            return new WP_Error('twofas_error', 'Something went wrong. Please try again.');
        }

        if ($this->user_wants_add_current_device_on_trusted_device_list()) {
            $this->remember_device($wp_user);
        }

        $this->reset_step_token($wp_user);
        $channel->close_authentication();

        if (get_user_option('use_ssl', $wp_user->ID)) {
            force_ssl_admin(true);
        }

        return $wp_user;
    }

    /**
     * @param WP_User $wp_user
     *
     * @return bool
     */
    private function create_step_token(WP_User $wp_user)
    {
        $this->storage->get_userdata()->set_user_id($wp_user->ID);
        return $this->storage->set_step_token();
    }

    /**
     * @param $user
     *
     * @return bool
     */
    private function is_wp_user($user)
    {
        return $user instanceof WP_User;
    }

    /**
     * @param WP_User $wp_user
     *
     * @return null|TwoFAS_Channel
     *
     * @throws Api_Exception
     */
    private function get_active_channel(WP_User $wp_user)
    {
        // Do not require second step if client did not create an account
        if (!$this->storage->client_completed_registration()) {
            return null;
        }

        $key_storage = $this->storage->get_options();

        // Do not require second step if user did not configure any authentication method yet
        try {
            $integration_user = $this->twofas->getIntegrationUserByExternalId($key_storage, $wp_user->ID);
        } catch (IntegrationUserNotFoundException $e) {
            return null;
        }

        // Do not require second step if user did not enable any authentication method yet
        try {
            return $this->channel_factory->create($integration_user);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * @return bool
     */
    private function user_wants_stop_login_process()
    {
        $action = $this->request->get_from_get(self::TWOFAS_ACTION);

        return $action === self::STOP_LOGIN_PROCESS;
    }

    /**
     * @return bool
     */
    private function user_wants_resend_code()
    {
        $action = $this->request->get_from_post(self::TWOFAS_ACTION);

        return $action === self::RESEND_CODE;
    }

    /**
     * @param WP_User $wp_user
     *
     * @return bool
     */
    private function user_has_current_device_on_trusted_device_list(WP_User $wp_user)
    {
        $this->storage->get_userdata()->set_user_id($wp_user->ID);

        return TwoFAS_Trusted_Device::is_device_trusted($this->storage);
    }

    /**
     * @param WP_User $wp_user
     *
     * @return bool
     */
    private function is_user_blocked(WP_User $wp_user)
    {
        $this->storage->get_userdata()->set_user_id($wp_user->ID);

        return $this->storage->get_userdata()->is_user_blocked();
    }

    /**
     * @param WP_User $wp_user
     *
     * @return bool
     */
    private function user_has_open_authentication(WP_User $wp_user)
    {
        $this->storage->get_userdata()->set_user_id($wp_user->ID);

        $authentication_ids = $this->storage->get_userdata()->get_authentication_id();

        return $this->storage->get_userdata()->has_open_authentication()
            && is_array($authentication_ids)
            && !empty($authentication_ids);
    }

    /**
     * @param WP_User $wp_user
     *
     * @return bool
     */
    private function user_has_valid_authentication(WP_User $wp_user)
    {
        $this->storage->get_userdata()->set_user_id($wp_user->ID);

        $valid_until = $this->storage->get_userdata()->get_authentication_valid_until();

        return $valid_until > time();
    }

    /**
     * @return null|string
     */
    private function get_code()
    {
        return $this->request->get_from_post(self::TWOFAS_CODE);
    }

    /**
     * @param WP_User $wp_user
     *
     * @return AuthenticationCollection
     */
    private function get_authentications(WP_User $wp_user)
    {
        $this->storage->get_userdata()->set_user_id($wp_user->ID);

        $authentication_id = $this->storage->get_userdata()->get_authentication_id();
        $authentications   = new AuthenticationCollection();

        if (is_array($authentication_id)) {
            foreach ($authentication_id as $id) {
                $authentication = new Authentication($id);
                $authentications->add($authentication);
            }
        } else {
            $authentication = new Authentication($authentication_id);
            $authentications->add($authentication);
        }

        return $authentications;
    }

    /**
     * @return array
     */
    private function get_request_params()
    {
        $params = array();

        $params['rememberme']             = $this->request->get_from_request('rememberme');
        $params['testcookie']             = $this->request->get_from_request('testcookie');
        $params['reauth']                 = $this->request->get_from_request('reauth');
        $params['interim_login']          = $this->request->get_from_request('interim-login');
        $params['redirect_to']            = $this->request->get_from_request('redirect_to');
        $params['twofas_remember_device'] = $this->request->get_from_request('twofas_remember_device');

        global $auth_secure_cookie;

        $redirect_to = $params['redirect_to'];

        if ($auth_secure_cookie && false !== strpos($redirect_to, 'wp-admin')) {
            $redirect_to           = preg_replace('|^http://|', 'https://', $redirect_to);
            $params['redirect_to'] = $redirect_to;
        }

        return $params;
    }

    /**
     * @param TwoFAS_Channel $channel
     * @param string         $error_key
     * @param string         $error_message
     * @param array          $additional_params
     */
    private function render_token_form(TwoFAS_Channel $channel, $error_key = '', $error_message = '', $additional_params=null)
    {
        $params = $this->get_request_params();

        if (empty($params['redirect_to'])) {
            $params['redirect_to'] = admin_url();
        }

        if ($error_key && $error_message) {
            $params['error']['key']     = $error_key;
            $params['error']['message'] = $error_message;
        }

        $channel_message     = $channel->get_channel_message();
        $request_for_code    = $channel->get_request_for_code_message();
        $resend_code_message = $channel->get_resend_code_message();

        if ($channel_message) {
            $params['channel_message'] = $channel_message;
        }

        if ($resend_code_message) {
            $params['resend_code'] = $resend_code_message;
        }

        $params['premium_message']  = $channel->is_premium();
        $params['request_for_code'] = $request_for_code;
        $params['phone_number']     = $channel->get_phone_number();

        if ($additional_params) {
            $params = array_merge($params, $additional_params);
        }

        echo $this->template->render_template(TwoFAS_Views::AUTHENTICATION_LOGIN_FORM, $params);
        exit();
    }

    /**
     * @param WP_User $wp_user
     */
    private function block_user(WP_User $wp_user)
    {
        $this->storage->get_userdata()->set_user_id($wp_user->ID);
        $this->storage->get_userdata()->block_user();
    }

    /**
     * @param $wp_user
     */
    private function reset_step_token($wp_user)
    {
        $this->storage->get_cookie_storage()->unset_step_token();

        $hash = md5(uniqid());

        $this->storage->get_userdata()->set_user_id($wp_user->ID);
        $this->storage->get_userdata()->set_step_token($hash);
    }

    /**
     * @return bool
     */
    private function user_wants_add_current_device_on_trusted_device_list()
    {
        $remember_device = $this->request->get_from_post(self::TWOFAS_REMEMBER_DEVICE);

        return !empty($remember_device);
    }

    /**
     * @param WP_User $wp_user
     */
    private function remember_device(WP_User $wp_user)
    {
        $this->storage->get_userdata()->set_user_id($wp_user->ID);

        TwoFAS_Trusted_Device::add_trusted_device($this->storage);
    }

    private function redirect_to_login_page()
    {
        $url           = wp_login_url();
        $interim_login = $this->request->get_from_request('interim-login');

        if ($interim_login) {
            $url .= '?interim-login=1';
        }

        wp_safe_redirect($url);
        exit();
    }

    /**
     * @return bool
     */
    private function is_external_login()
    {
        $url = get_site_url() . $_SERVER['REQUEST_URI'];

        return false === strpos($url, 'wp-login.php');
    }

    /**
     * @return string
     */
    private function get_redirect_to_url()
    {
        $url         = wp_login_url();
        $redirect_to = '';
        $wc_redirect = $this->request->get_from_post('redirect');
        $remember_me = $this->request->get_from_request('rememberme');

        if ($wc_redirect) {
            $redirect_to = $wc_redirect;
        } elseif (function_exists('wc_get_page_permalink')) {
            $redirect_to = wc_get_page_permalink('myaccount');
        }

        if ($redirect_to) {
            $redirect_to = urlencode($redirect_to);
            $url = add_query_arg('redirect_to', $redirect_to, $url);
        }

        if ($remember_me) {
            $url = add_query_arg('rememberme', $remember_me, $url);
        }

        return $url;
    }
}
