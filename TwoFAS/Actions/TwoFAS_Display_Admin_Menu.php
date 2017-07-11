<?php

namespace TwoFAS\Actions;

use TwoFAS\Actions\Result\TwoFAS_Action_Result;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\Templates\Views\TwoFAS_Views;
use TwoFAS\TwoFAS_App;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;

class TwoFAS_Display_Admin_Menu extends TwoFAS_Action
{
    /**
     * @var string
     */
    protected $action_id = 'twofas-display-admin-menu';

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        if (!$app->get_storage()->client_completed_registration()) {
            return $app->get_result_factory()->get_result_redirect(null, TwoFAS_Action_Map::TWOFAS_ACTION_CREATE_ACCOUNT);
        }

        $referer         = wp_get_referer();
        $data            = array();
        $options_storage = $app->get_storage()->get_options();
        $twofas_email    = $options_storage->get_twofas_email();

        $data['twofas_email']        = $twofas_email;
        $data['wizard_modal_status'] = (bool) preg_match('/twofas-create-account/', $referer);

        $user_data     = $app->get_storage()->get_userdata();
        $statistics    = $this->get_configured_channels_statistics($user_data);
        $data          = array_merge($data, $statistics);
        $admin_actions = $this->get_admin_actions();
        $data          = array_merge($data, $admin_actions);

        try {
            $authentication_channels = $app->get_authentication_channels();
        } catch (User_Zone_Exception $e) {
            return $app->get_result_factory()
                ->add_notification_key('e-cannot-connect-to-2fas')
                ->get_result_render(TwoFAS_Views::ADMIN_MENU, $data);
        }

        $data['totp_channel_status'] = $authentication_channels->get_totp_status();

        $card_status = $authentication_channels->client_has_card();

        if ($card_status) {
            $data['sms_channel_status']  = $authentication_channels->get_sms_status();
            $data['call_channel_status'] = $authentication_channels->get_call_status();
        } else {
            $data['sms_channel_status']  = 'NOT_CONFIGURED';
            $data['call_channel_status'] = 'NOT_CONFIGURED';
        }

        $data['client_card_status']            = $card_status;
        $data['client_custom_password_status'] = $authentication_channels->client_has_custom_password();
        $data['api_connection_status']         = true;

        return $app->get_result_factory()
            ->add_notifications_from_url()
            ->get_result_render(TwoFAS_Views::ADMIN_MENU, $data);
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     *
     * @return array
     */
    private function get_configured_channels_statistics(TwoFAS_WP_Userdata_Storage $user_data)
    {
        $totp_enabled  = $user_data->count_totp_configured_enabled();
        $totp_disabled = $user_data->count_totp_configured_disabled();
        $totp_total    = $totp_enabled + $totp_disabled;
        $sms_enabled   = $user_data->count_sms_configured_enabled();
        $sms_disabled  = $user_data->count_sms_configured_disabled();
        $sms_total     = $sms_enabled + $sms_disabled;
        $call_enabled  = $user_data->count_call_configured_enabled();
        $call_disabled = $user_data->count_call_configured_disabled();
        $call_total    = $call_enabled + $call_disabled;

        return array(
            'number_of_accounts_with_configured_and_enabled_totp_channel'  => $totp_enabled,
            'number_of_accounts_with_configured_and_disabled_totp_channel' => $totp_disabled,
            'number_of_accounts_with_configured_totp_channel'              => $totp_total,
            'number_of_accounts_with_configured_and_enabled_sms_channel'   => $sms_enabled,
            'number_of_accounts_with_configured_and_disabled_sms_channel'  => $sms_disabled,
            'number_of_accounts_with_configured_sms_channel'               => $sms_total,
            'number_of_accounts_with_configured_and_enabled_call_channel'  => $call_enabled,
            'number_of_accounts_with_configured_and_disabled_call_channel' => $call_disabled,
            'number_of_accounts_with_configured_call_channel'              => $call_total,
        );
    }

    /**
     * @return array
     */
    private function get_admin_actions()
    {
        return array(
            'admin_action_name'                 => TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN,
            'enable_totp_globally_action_name'  => TwoFAS_Action_Map::TWOFAS_ACTION_ENABLE_TOTP_GLOBALLY,
            'disable_totp_globally_action_name' => TwoFAS_Action_Map::TWOFAS_ACTION_DISABLE_TOTP_GLOBALLY,
            'enable_sms_globally_action_name'   => TwoFAS_Action_Map::TWOFAS_ACTION_ENABLE_SMS_GLOBALLY,
            'disable_sms_globally_action_name'  => TwoFAS_Action_Map::TWOFAS_ACTION_DISABLE_SMS_GLOBALLY,
            'enable_call_globally_action_name'  => TwoFAS_Action_Map::TWOFAS_ACTION_ENABLE_CALL_GLOBALLY,
            'disable_call_globally_action_name' => TwoFAS_Action_Map::TWOFAS_ACTION_DISABLE_CALL_GLOBALLY,
        );
    }
}
