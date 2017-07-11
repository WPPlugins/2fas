<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;

class TwoFAS_Enable_Call extends TwoFAS_Enable_Channel
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_ENABLE_CALL;

    /**
     * @var string
     */
    protected $success_notification_key = 's-enable-call';

    /**
     * @var string
     */
    protected $error_notification_key = 'e-enable-call';
    
    /**
     * @param TwoFAS_Authentication_Channels $authentication_channels
     *
     * @return bool
     */
    public function is_method_active(TwoFAS_Authentication_Channels $authentication_channels)
    {
        return $authentication_channels->get_call_status() === TwoFAS_Authentication_Channels::CHANNEL_STATUS_ENABLED;
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $userdata
     *
     * @return bool
     */
    public function is_method_configured_disabled(TwoFAS_WP_Userdata_Storage $userdata)
    {
        return $userdata->get_call_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_DISABLED;
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

        if ($userdata->get_totp_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_ENABLED) {
            $userdata->set_totp_as_configured_disabled();
        }
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     */
    public function enable_channel(TwoFAS_WP_Userdata_Storage $user_data)
    {
        $user_data->set_call_as_configured_enabled();
        $user_data->set_call_as_auth_method();
    }

    /**
     * @return string
     */
    public function get_channel_name()
    {
        return 'call';
    }
}

