<?php

namespace TwoFAS\Actions\AdminChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\UserZone\Integration;

class TwoFAS_Force_Disable_Call_Globally extends TwoFAS_Force_Disable_Channel_Globally
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_FORCE_DISABLE_CALL_GLOBALLY;
    
    /**
     * @var string
     */
    protected $success_notification_key = 's-force-disable-call-globally';

    /**
     * @var string
     */
    protected $error_notification_key = 'e-force-disable-call-globally';

    /**
     * @param TwoFAS_Authentication_Channels $authentication_channels
     * @param TwoFAS_WP_Userdata_Storage     $user_data
     *
     * @return Integration
     */
    protected function force_disable_channel(
        TwoFAS_Authentication_Channels $authentication_channels,
        TwoFAS_WP_Userdata_Storage $user_data
    ) {
        $user_data->disable_call_for_all_users();

        return $authentication_channels->force_disable_call();
    }
}
