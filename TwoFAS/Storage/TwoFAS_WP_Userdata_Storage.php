<?php

namespace TwoFAS\Storage;

use Exception;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use WP_User;

class TwoFAS_WP_Userdata_Storage
{
    const TWOFAS_TRUSTED_DEVICES                 = 'twofas_trusted_devices';
    const TWOFAS_STEP_TOKEN                      = 'twofas_step_token';
    const TWOFAS_USER_AUTHENTICATION_STATUS_OPEN = 'OPEN';
    const TWOFAS_USER_AUTHENTICATION_STATUS_NONE = 'NONE';
    const TWOFAS_AUTHENTICATION_VALID_UNTIL      = 'twofas_authentication_valid_until';
    const TWOFAS_AUTHENTICATION_STATUS           = 'twofas_is_authentication_open';
    const TWOFAS_AUTHENTICATION_ID               = 'twofas_authentication_id';
    const TWOFAS_USER_BLOCKED_UNTIL              = 'twofas_blocked_until';
    const TWOFAS_AUTHENTICATION_METHOD           = 'twofas_authentication_method';
    const TWOFAS_TOTP_CONFIGURED                 = 'twofas_totp_configured';
    const TWOFAS_TOTP_STATUS                     = 'twofas_totp_status';
    const TWOFAS_SMS_STATUS                      = 'twofas_sms_status';
    const TWOFAS_CALL_STATUS                     = 'twofas_call_status';
    const TWOFAS_METHOD_NOT_CONFIGURED           = 'NOT_CONFIGURED';
    const TWOFAS_METHOD_CONFIGURED_DISABLED      = 'CONFIGURED_DISABLED';
    const TWOFAS_METHOD_CONFIGURED_ENABLED       = 'CONFIGURED_ENABLED';
    const TWOFAS_TRUE                            = 'TRUE';
    const TWOFAS_FALSE                           = 'FALSE';
    const TWOFAS_NULL                            = 'NULL';
    const TOTP                                   = 'TOTP';
    const SMS                                    = 'SMS';
    const CALL                                   = 'CALL';

    /**
     * @var int
     */
    protected $user_id;

    public function __construct()
    {
        $this->user_id = get_current_user_id();
    }

    /**
     * @param int $user_id
     */
    public function set_user_id($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return int
     */
    public function get_user_id()
    {
        return $this->user_id;
    }

    /**
     * @return bool
     */
    public function is_totp_enabled_for_the_user()
    {
        return self::TWOFAS_METHOD_CONFIGURED_ENABLED === $this->get_user_meta(self::TWOFAS_TOTP_STATUS, self::TWOFAS_METHOD_NOT_CONFIGURED);
    }

    /**
     * @return bool
     */
    public function is_sms_enabled_for_the_user()
    {
        return self::TWOFAS_METHOD_CONFIGURED_ENABLED === $this->get_user_meta(self::TWOFAS_SMS_STATUS, self::TWOFAS_METHOD_NOT_CONFIGURED);
    }

    /**
     * @return bool
     */
    public function is_call_enabled_for_the_user()
    {
        return self::TWOFAS_METHOD_CONFIGURED_ENABLED === $this->get_user_meta(self::TWOFAS_CALL_STATUS, self::TWOFAS_METHOD_NOT_CONFIGURED);
    }

    /**
     * @return bool
     */
    public function totp_not_configured()
    {
        return $this->get_user_meta(self::TWOFAS_TOTP_STATUS, self::TWOFAS_METHOD_NOT_CONFIGURED) === self::TWOFAS_METHOD_NOT_CONFIGURED;
    }

    /**
     * @return mixed|null
     */
    public function get_totp_status()
    {
        return $this->get_user_meta(self::TWOFAS_TOTP_STATUS, self::TWOFAS_METHOD_NOT_CONFIGURED);
    }

    /**
     * @throws Exception
     */
    public function set_totp_as_auth_method()
    {
        $this->set_user_meta(self::TWOFAS_AUTHENTICATION_METHOD, 'TOTP');
    }

    /**
     * @throws Exception
     */
    public function set_totp_as_configured_enabled()
    {
        $this->set_user_meta(self::TWOFAS_TOTP_STATUS, self::TWOFAS_METHOD_CONFIGURED_ENABLED);

        if ($this->get_sms_status() === self::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $this->set_sms_as_configured_disabled();
        }

        if ($this->get_call_status() === self::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $this->set_call_as_configured_disabled();
        }
    }

    /**
     * @throws Exception
     */
    public function set_totp_as_configured_disabled()
    {
        $this->set_user_meta(self::TWOFAS_TOTP_STATUS, self::TWOFAS_METHOD_CONFIGURED_DISABLED);
    }

    public function clear_sms_configuration()
    {
        $this->clear_method_configuration(self::TWOFAS_SMS_STATUS, self::SMS);
    }

    public function clear_totp_configuration()
    {
        $this->clear_method_configuration(self::TWOFAS_TOTP_STATUS, self::TOTP);
    }

    public function clear_call_configuration()
    {
        $this->clear_method_configuration(self::TWOFAS_CALL_STATUS, self::CALL);
    }

    private function clear_method_configuration($status_field, $method_name)
    {
        $this->set_user_meta($status_field, self::TWOFAS_METHOD_NOT_CONFIGURED);

        if ($this->get_auth_method_name() === $method_name) {
            $this->disable_auth_method();
        }
    }

    public function get_sms_status()
    {
        return $this->get_user_meta(self::TWOFAS_SMS_STATUS, self::TWOFAS_METHOD_NOT_CONFIGURED);
    }

    public function set_sms_as_auth_method()
    {
        $this->set_user_meta(self::TWOFAS_AUTHENTICATION_METHOD, self::SMS);
    }

    public function set_sms_as_configured_enabled()
    {
        $this->set_user_meta(self::TWOFAS_SMS_STATUS, self::TWOFAS_METHOD_CONFIGURED_ENABLED);

        if ($this->get_call_status() === self::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $this->set_call_as_configured_disabled();
        }

        if ($this->get_totp_status() === self::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $this->set_totp_as_configured_disabled();
        }
    }

    public function set_sms_as_configured_disabled()
    {
        $this->set_user_meta(self::TWOFAS_SMS_STATUS, self::TWOFAS_METHOD_CONFIGURED_DISABLED);
    }

    public function get_call_status()
    {
        return $this->get_user_meta(self::TWOFAS_CALL_STATUS, self::TWOFAS_METHOD_NOT_CONFIGURED);
    }

    public function set_call_as_auth_method()
    {
        $this->set_user_meta(self::TWOFAS_AUTHENTICATION_METHOD, self::CALL);
    }

    public function set_call_as_configured_enabled()
    {
        $this->set_user_meta(self::TWOFAS_CALL_STATUS, self::TWOFAS_METHOD_CONFIGURED_ENABLED);

        if ($this->get_sms_status() === self::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $this->set_sms_as_configured_disabled();
        }

        if ($this->get_totp_status() === self::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $this->set_totp_as_configured_disabled();
        }
    }

    public function set_call_as_configured_disabled()
    {
        $this->set_user_meta(self::TWOFAS_CALL_STATUS, self::TWOFAS_METHOD_CONFIGURED_DISABLED);
    }

    /**
     * @return mixed|null
     */
    public function get_trusted_devices()
    {
        return $this->get_user_meta(self::TWOFAS_TRUSTED_DEVICES);
    }

    /**
     * @param array $remembered_machines
     *
     * @return bool
     *
     * @throws Exception
     */
    public function set_trusted_devices(array $remembered_machines)
    {
        return $this->set_user_meta(self::TWOFAS_TRUSTED_DEVICES, $remembered_machines);
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function delete_trusted_devices()
    {
        return $this->set_user_meta(self::TWOFAS_TRUSTED_DEVICES, array());
    }

    /**
     * @return string
     */
    public function get_mail()
    {
        if (!get_userdata($this->user_id)) {
            return '';
        }

        return get_userdata($this->user_id)->user_email;
    }

    /**
     * @param $step_token
     *
     * @return null|WP_User
     */
    public function get_user_by_step_token($step_token)
    {
        $users = get_users(array(
            'meta_key'   => TwoFAS_Cookie_Storage::TWOFAS_STEP_TOKEN,
            'meta_value' => $step_token,
            'fields'     => 'all'
        ));

        if (empty($users)) {
            return null;
        }

        return $users[0];
    }

    /**
     * @param $authentication_id
     *
     * @return bool
     *
     * @throws Exception
     */
    public function set_authentication_id($authentication_id)
    {
        return $this->set_user_meta(self::TWOFAS_AUTHENTICATION_ID, $authentication_id);
    }

    /**
     * @return mixed|null
     */
    public function get_authentication_id()
    {
        return $this->get_user_meta(self::TWOFAS_AUTHENTICATION_ID);
    }

    /**
     * @return mixed|null
     */
    public function get_auth_method_name()
    {
        return $this->get_user_meta(self::TWOFAS_AUTHENTICATION_METHOD, "None");
    }

    /**
     * @param $token
     *
     * @return bool
     *
     * @throws Exception
     */
    public function set_step_token($token)
    {
        return $this->set_user_meta(self::TWOFAS_STEP_TOKEN, $token);
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function open_authentication()
    {
        return $this->set_user_meta(self::TWOFAS_AUTHENTICATION_STATUS, self::TWOFAS_USER_AUTHENTICATION_STATUS_OPEN);
    }

    /**
     * @param int $timestamp
     *
     * @return bool
     *
     * @throws Exception
     */
    public function save_authentication_valid_until($timestamp)
    {
        return $this->set_user_meta(self::TWOFAS_AUTHENTICATION_VALID_UNTIL, $timestamp);
    }

    /**
     * @return int|null
     */
    public function get_authentication_valid_until()
    {
        return $this->get_user_meta(self::TWOFAS_AUTHENTICATION_VALID_UNTIL);
    }

    /**
     * @return bool
     */
    public function has_open_authentication()
    {
        return $this->get_user_meta(self::TWOFAS_AUTHENTICATION_STATUS) === self::TWOFAS_USER_AUTHENTICATION_STATUS_OPEN;
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function reset_authentication()
    {
        return $this->set_user_meta(self::TWOFAS_AUTHENTICATION_STATUS, self::TWOFAS_USER_AUTHENTICATION_STATUS_NONE);
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function block_user()
    {
        return $this->set_user_meta(self::TWOFAS_USER_BLOCKED_UNTIL, time() + 300);
    }

    /**
     * @return bool
     */
    public function is_user_blocked()
    {
        $blocked_until = $this->get_user_meta(self::TWOFAS_USER_BLOCKED_UNTIL);

        if (is_null($blocked_until) || (time() > (int) $blocked_until)) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function disable_auth_method()
    {
        $this->set_user_meta(
            self::TWOFAS_AUTHENTICATION_METHOD,
            ''
        );
    }

    /**
     * @return null|int
     */
    public function count_totp_configured_enabled()
    {
        return $this->count_usermeta_rows(
            TwoFAS_WP_Userdata_Storage::TWOFAS_TOTP_STATUS,
            TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_ENABLED
        );
    }

    /**
     * @return null|int
     */
    public function count_totp_configured_disabled()
    {
        return $this->count_usermeta_rows(
            TwoFAS_WP_Userdata_Storage::TWOFAS_TOTP_STATUS,
            TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_DISABLED
        );
    }

    /**
     * @return null|int
     */
    public function count_sms_configured_enabled()
    {
        return $this->count_usermeta_rows(
            TwoFAS_WP_Userdata_Storage::TWOFAS_SMS_STATUS,
            TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_ENABLED
        );
    }

    /**
     * @return null|int
     */
    public function count_sms_configured_disabled()
    {
        return $this->count_usermeta_rows(
            TwoFAS_WP_Userdata_Storage::TWOFAS_SMS_STATUS,
            TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_DISABLED
        );
    }

    /**
     * @return null|int
     */
    public function count_call_configured_enabled()
    {
        return $this->count_usermeta_rows(
            TwoFAS_WP_Userdata_Storage::TWOFAS_CALL_STATUS,
            TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_ENABLED
        );
    }

    /**
     * @return null|int
     */
    public function count_call_configured_disabled()
    {
        return $this->count_usermeta_rows(
            TwoFAS_WP_Userdata_Storage::TWOFAS_CALL_STATUS,
            TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_DISABLED
        );
    }

    public function enable_totp_for_all_users()
    {
        $this->enable_method_globally(TwoFAS_Authentication_Channels::CHANNEL_TOTP);
    }

    public function disable_totp_for_all_users()
    {
        $this->disable_method_globally(TwoFAS_Authentication_Channels::CHANNEL_TOTP);
    }

    public function enable_sms_for_all_users()
    {
        $this->enable_method_globally(TwoFAS_Authentication_Channels::CHANNEL_SMS);
    }

    public function disable_sms_for_all_users()
    {
        $this->disable_method_globally(TwoFAS_Authentication_Channels::CHANNEL_SMS);
    }

    public function enable_call_for_all_users()
    {
        $this->enable_method_globally(TwoFAS_Authentication_Channels::CHANNEL_CALL);
    }

    public function disable_call_for_all_users()
    {
        $this->disable_method_globally(TwoFAS_Authentication_Channels::CHANNEL_CALL);
    }

    /**
     * @return bool
     */
    public function totp_authentication_can_be_opened()
    {
        return $this->is_totp_enabled_for_the_user()
        && $this->get_auth_method_name() === self::TOTP;
    }

    /**
     * @return bool
     */
    public function sms_authentication_can_be_opened()
    {
        return $this->is_sms_enabled_for_the_user()
        && $this->get_auth_method_name() === self::SMS;
    }

    /**
     * @return bool
     */
    public function call_authentication_can_be_opened()
    {
        return $this->is_call_enabled_for_the_user()
        && $this->get_auth_method_name() === self::CALL;
    }

    private function enable_method_globally($method)
    {
        global $wpdb;

        $table = $wpdb->usermeta;

        $data = array(
            'meta_value' => 'CONFIGURED_ENABLED'
        );

        $where = array(
            'meta_key'   => 'twofas_' . $method . '_status',
            'meta_value' => 'CONFIGURED_DISABLED'
        );

        $wpdb->update($table, $data, $where);
    }

    private function disable_method_globally($method)
    {
        global $wpdb;

        $table = $wpdb->usermeta;

        $data = array(
            'meta_value' => 'CONFIGURED_DISABLED'
        );

        $where = array(
            'meta_key'   => 'twofas_' . $method . '_status',
            'meta_value' => 'CONFIGURED_ENABLED'
        );

        $wpdb->update($table, $data, $where);

        $data = array(
            'meta_value' => ''
        );

        $where = array(
            'meta_key'   => TwoFAS_WP_Userdata_Storage::TWOFAS_AUTHENTICATION_METHOD,
            'meta_value' => $method
        );

        $wpdb->update($table, $data, $where);
    }

    /**
     * @param string $meta_key
     * @param string $meta_value
     *
     * @return int
     */
    protected function count_usermeta_rows($meta_key, $meta_value)
    {
        global $wpdb;

        $result = $wpdb->get_var($wpdb->prepare(
            "
                SELECT COUNT(*)
                FROM $wpdb->usermeta
                WHERE meta_value = %s 
                AND meta_key = %s
            ",
            $meta_value,
            $meta_key
        ));

        return intval($result);
    }

    /**
     * @param string     $meta_key
     * @param mixed|null $default_value
     *
     * @return mixed|null
     */
    protected function get_user_meta($meta_key, $default_value = null)
    {
        $meta_value = get_user_meta($this->user_id, $meta_key, true);

        if (empty($meta_value)) {
            return $default_value;
        }

        return $meta_value;
    }

    /**
     * @param string $meta_key
     * @param string $meta_value
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function set_user_meta($meta_key, $meta_value)
    {
        if (!$this->user_id) {
            throw new Exception('User id not set');
        }

        $result = update_user_meta($this->user_id, $meta_key, $meta_value);

        if ($result === false) {
            return false;
        }

        return true;
    }
}
