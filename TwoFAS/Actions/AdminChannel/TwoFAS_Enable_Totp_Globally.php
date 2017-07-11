<?php

namespace TwoFAS\Actions\AdminChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\UserZone\Integration;

class TwoFAS_Enable_Totp_Globally extends TwoFAS_Enable_Channel_Globally
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_ENABLE_TOTP_GLOBALLY;

    /**
     * @var string
     */
    protected $success_notification_key = 's-enable-totp-globally';

    /**
     * @var string
     */
    protected $error_notification_key = 'e-enable-totp-globally';

    /**
     * @param TwoFAS_Authentication_Channels $authentication_channels
     * @param TwoFAS_WP_Userdata_Storage     $user_data
     *
     * @return Integration
     */
    protected function enable_channel(
        TwoFAS_Authentication_Channels $authentication_channels,
        TwoFAS_WP_Userdata_Storage $user_data
    ) {
        return $authentication_channels->enable_totp();
    }
}
