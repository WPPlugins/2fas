<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\User\TwoFAS_User;

class TwoFAS_Enable_TOTP extends TwoFAS_Enable_Channel
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_ENABLE_TOTP;

    /**
     * @var string
     */
    protected $success_notification_key = 's-enable-totp';

    /**
     * @var string
     */
    protected $error_notification_key = 'e-enable-totp';
    
    /**
     * @param TwoFAS_Authentication_Channels $authentication_channels
     *
     * @return bool
     */
    public function is_method_active(TwoFAS_Authentication_Channels $authentication_channels)
    {
        return $authentication_channels->get_totp_status() === TwoFAS_Authentication_Channels::CHANNEL_STATUS_ENABLED;
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $userdata
     *
     * @return bool
     */
    public function is_method_configured_disabled(TwoFAS_WP_Userdata_Storage $userdata)
    {
        return $userdata->get_totp_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_DISABLED;
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     * @param TwoFAS_User                $user
     *
     * @return mixed|void
     * @throws \TwoFAS\Api\Exception\Exception
     */
    public function set_method_as_used_and_configured_enabled(TwoFAS_WP_Userdata_Storage $user_data, TwoFAS_User $user)
    {
        $user->fetch_from_2fas()
            ->set_active_method('totp')
            ->push_to_2fas();

        $user_data->set_totp_as_configured_enabled();
        $user_data->set_totp_as_auth_method();
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $userdata
     * @return mixed|void
     */
    public function set_other_methods_as_disabled(TwoFAS_WP_Userdata_Storage $userdata)
    {
        if ($userdata->get_sms_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $userdata->set_sms_as_configured_disabled();
        }
        
        if ($userdata->get_call_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $userdata->set_call_as_configured_disabled();
        }
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     */
    public function enable_channel(TwoFAS_WP_Userdata_Storage $user_data)
    {
        $user_data->set_totp_as_configured_enabled();
        $user_data->set_totp_as_auth_method();
    }

    /**
     * @return string
     */
    public function get_channel_name()
    {
        return 'totp';
    }
}
