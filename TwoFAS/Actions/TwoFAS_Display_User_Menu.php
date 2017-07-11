<?php

namespace TwoFAS\Actions;

use TwoFAS\Actions\Result\TwoFAS_Action_Result;
use TwoFAS\Templates\Views\TwoFAS_Views;
use TwoFAS\TwoFAS_App;
use TwoFAS\Roles\TwoFAS_Role;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;

class TwoFAS_Display_User_Menu extends TwoFAS_Action
{
    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $result_factory = $app->get_result_factory()->add_notifications_from_url();

        if (!$app->get_storage()->client_completed_registration()) {
            if (TwoFAS_Role::user_has_admin_capability()) {
                return $result_factory->add_notification_key('e-account-required')
                    ->get_result_redirect(
                        TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN,
                        TwoFAS_Action_Map::TWOFAS_ACTION_CREATE_ACCOUNT
                    );
            }
            return $result_factory->get_result_render(TwoFAS_Views::NOT_ENABLED);
        }

        $data = array();

        try {
            $authentication_channels    = $app->get_authentication_channels();
            $data['totp_global_status'] = $authentication_channels->get_totp_status();
            $data['sms_global_status']  = $authentication_channels->get_sms_status();
            $data['call_global_status'] = $authentication_channels->get_call_status();
        } catch (User_Zone_Exception $e) {}

        $user_data               = $app->get_storage()->get_userdata();
        $data['totp_status']     = $user_data->get_totp_status();
        $data['sms_status']      = $user_data->get_sms_status();
        $data['call_status']     = $user_data->get_call_status();
        $data['remove_action']   = null;
        $data['phone_number']    = $app->get_user()->fetch_from_2fas()->get_phone_number();
        $data['trusted_devices'] = $user_data->get_trusted_devices();
        $data['auth_method']     = $user_data->get_auth_method_name();

        if ($app->get_request()->has_param('remove-action')) {
            $data['remove_action'] = $app->get_request()->get_param('remove-action');
        }

        return $result_factory->get_result_render(TwoFAS_Views::USER_MENU, $data);
    }
}
