<?php

namespace TwoFAS\Actions;

use TwoFAS\TwoFAS_App;
use TwoFAS\Devices\TwoFAS_Trusted_Device;
use TwoFAS\Actions\Result\TwoFAS_Action_Result_Redirect;

class TwoFAS_Remove_Trusted_Device extends TwoFAS_Action
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_REMOVE_TRUSTED_MACHINE;

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result_Redirect
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $result_factory = $app->get_result_factory();
        $request        = $app->get_request();

        if ($app->get_request()->is_valid_action_call($this->action_id)
            && $request->has_param('id')
        ) {
            $device_id = $request->get_param('id');

            if (!TwoFAS_Trusted_Device::remove_trusted_device($app->get_storage(), $device_id)) {
                $result_factory->add_notification_key('e-generic-error');
            } else {
                $result_factory->add_notification_key('s-device-removed');
            }
        } else {
            $result_factory->add_notification_key('e-could-not-remove-device');
        }

        return $result_factory->get_result_redirect();
    }
}
