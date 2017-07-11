<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\User\TwoFAS_User;

class TwoFAS_Remove_TOTP_Configuration extends TwoFAS_Remove_Configuration
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_REMOVE_TOTP_CONFIGURATION;

    /**
     * @param TwoFAS_WP_Userdata_Storage $storage
     * @param TwoFAS_User                $user
     *
     * @throws \TwoFAS\Api\Exception\Exception
     */
    public function remove_configuration(TwoFAS_WP_Userdata_Storage $storage, TwoFAS_User $user)
    {
        $user          = $user->fetch_from_2fas();
        $active_method = $user->get_active_method();

        if ($active_method == 'totp') {
            $user->set_active_method(null);
        }

        $user->set_totp_secret(null);
        $user->push_to_2fas();
        $storage->clear_totp_configuration();
    }
}
