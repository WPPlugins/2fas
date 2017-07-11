<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;

class TwoFAS_Disable_Call extends TwoFAS_Disable_Channel
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_DISABLE_CALL;

    /**
     * @var string
     */
    protected $success_notification_key = 's-disable-call';

    /**
     * @var string
     */
    protected $error_notification_key = 'e-disable-call';
    
    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     *
     * @return bool
     */
    public function is_method_configured_enabled_and_used(TwoFAS_WP_Userdata_Storage $user_data)
    {
        return $user_data->get_call_status() === TwoFAS_WP_Userdata_Storage::TWOFAS_METHOD_CONFIGURED_ENABLED
            && $user_data->get_auth_method_name() === TwoFAS_WP_Userdata_Storage::CALL;
    }

    /**
     * @return string
     */
    public function get_channel_name()
    {
        return 'call';
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     */
    public function set_method_as_configured_disabled(TwoFAS_WP_Userdata_Storage $user_data)
    {
        $user_data->set_call_as_configured_disabled();
    }
}
