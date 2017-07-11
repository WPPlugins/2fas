<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\Result\TwoFAS_Action_Result_Factory;
use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Devices\TwoFAS_Trusted_Device;
use TwoFAS\Storage\TwoFAS_Storage;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use WP_User_Meta_Session_Tokens;

abstract class TwoFAS_Configure_Channel extends TwoFAS_Action
{
    /**
     * @param  string $totp_token
     * @return bool
     */
    protected function validate_token_format($totp_token)
    {
        return is_string($totp_token) 
            && preg_match('/\\d{6}/', $totp_token) === 1;
    }

    protected function add_device_as_trusted(TwoFAS_Storage $storage, TwoFAS_Action_Result_Factory $result_factory)
    {
        if (!TwoFAS_Trusted_Device::is_device_trusted($storage)) {
            TwoFAS_Trusted_Device::add_trusted_device($storage);
            $result_factory->add_notification_key('s-trusted-device-added');
        }
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $userdata
     */
    protected function logout_on_other_devices(TwoFAS_WP_Userdata_Storage $userdata)
    {
        if (class_exists('WP_User_Meta_Session_Tokens')) {
            $session_tokens = WP_User_Meta_Session_Tokens::get_instance($userdata->get_user_id());
            $session_token  = wp_get_session_token();
            $session_tokens->destroy_others($session_token);
        }

        $userdata->delete_trusted_devices();
    }
}
