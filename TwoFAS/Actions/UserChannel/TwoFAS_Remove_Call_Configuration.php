<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\User\TwoFAS_User;

class TwoFAS_Remove_Call_Configuration extends TwoFAS_Remove_Configuration
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_REMOVE_CALL_CONFIGURATION;

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

        if ($active_method == 'call') {
            $user->set_active_method(null);
        }

        $sms_status = $storage->get_sms_status();

        if ($sms_status != 'CONFIGURED_ENABLED' && $sms_status != 'CONFIGURED_DISABLED') {
            $user->set_phone_number(null);
        }
        
        $user->push_to_2fas();

        $storage->clear_call_configuration();
    }
}
