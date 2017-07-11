<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;

class TwoFAS_Disable_TOTP extends TwoFAS_Disable_Channel
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_DISABLE_TOTP;
    
    /**
     * @var string
     */
    protected $success_notification_key = 's-disable-totp';

    /**
     * @var string
     */
    protected $error_notification_key = 'e-disable-totp';
    
    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     *
     * @return bool
     */
    public function is_method_configured_enabled_and_used(TwoFAS_WP_Userdata_Storage $user_data)
    {
        return $user_data->get_totp_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_ENABLED
            && $user_data->get_auth_method_name() === TwoFAS_WP_Userdata_Storage::TOTP;
    }

    /**
     * @return string
     */
    public function get_channel_name()
    {
        return 'totp';
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     */
    public function set_method_as_configured_disabled(TwoFAS_WP_Userdata_Storage $user_data)
    {
        $user_data->set_totp_as_configured_disabled();
    }
}
