<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\Result\TwoFAS_Action_Result;
use TwoFAS\Actions\Result\TwoFAS_Action_Result_Factory;
use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Exception\Exception as Api_Exception;
use TwoFAS\Api\TotpSecretGenerator;
use TwoFAS\Authentication\TwoFAS_Authentication;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\SDK\QR_Code;
use TwoFAS\Templates\Views\TwoFAS_Views;
use TwoFAS\TwoFAS_App;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;

class TwoFAS_Configure_TOTP extends TwoFAS_Configure_Channel
{
    const TWOFAS_TOTP_SECRET_FRESH = 'recently_generated';

    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_CONFIGURE_TOTP;

    /**
     * @var string
     */
    private $totp_private_key_status;

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $request        = $app->get_request();
        $result_factory = $app->get_result_factory();

        if (!$app->get_storage()->client_completed_registration()) {
            return $result_factory->get_result_redirect();
        }

        try {
            $totp_status = $app->get_authentication_channels()->get_totp_status();
        } catch (User_Zone_Exception $e) {
            return $result_factory->add_notification_key('e-generic-error')->get_result_redirect();
        }

        if ($totp_status !== TwoFAS_Authentication_Channels::CHANNEL_STATUS_ENABLED) {
            return $result_factory->get_result_redirect();
        }

        // Handle submitted token
        if ($request->is_valid_action_call($this->action_id) && $request->has_params(array(
            'totp-token',
            'totp-private-key',
            'totp-private-key-status'
        ))) {
            return $this->handle_form_submission($app);
        }

        $totp_private_key = $this->generate_totp_private_key($app);

        if (is_null($totp_private_key)) {
            return $result_factory->add_notification_key('e-totp-unexpected')->get_result_redirect();
        }

        // Create QR code
        $qr_code = QR_Code::generate($totp_private_key);

        return $result_factory->add_notifications_from_url()->get_result_render(TwoFAS_Views::CONFIGURE_TOTP, array(
            'qr_code'                 => $qr_code,
            'totp_private_key'        => $totp_private_key,
            'totp_private_key_status' => $this->totp_private_key_status
        ));
    }

    /**
     * @param TwoFAS_Action_Result_Factory $result_factory
     * @param                              $totp_private_key
     *
     * @return \TwoFAS\Actions\Result\TwoFAS_Action_Result_Render
     */
    private function return_render_result_with_params(TwoFAS_Action_Result_Factory $result_factory, $totp_private_key, $notification) 
    {
        return $result_factory->add_notification_key($notification)->get_result_render(TwoFAS_Views::CONFIGURE_TOTP, array(
            'qr_code'                 => QR_Code::generate($totp_private_key),
            'totp_private_key'        => $totp_private_key,
            'totp_private_key_status' => $this->totp_private_key_status
        ));
    }

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result
     */
    private function handle_form_submission(TwoFAS_App $app)
    {
        $request         = $app->get_request();
        $api             = $app->get_sdk_bridge()->get_api();
        $twofas_user     = $app->get_user();
        $storage         = $app->get_storage();
        $user_data       = $storage->get_userdata();
        $result_factory  = $app->get_result_factory();
        
        $totp_token                    = $request->get_param('totp-token');
        $totp_private_key              = $request->get_param('totp-private-key');
        $this->totp_private_key_status = $request->get_param('totp-private-key-status');

        // Validate TOTP private key format
        if (!preg_match("/[A-Z0-9]{16}/", $totp_private_key) === 1) {
            return $this->return_render_result_with_params($result_factory, $totp_private_key, 'e-totp-unexpected');
        }

        // Try to validate token via 2FAS api
        $result = null;
        try {
            $authentication = $api->requestAuthViaTotp($totp_private_key);
            $result = TwoFAS_Authentication::get_instance($api)->validate($authentication->id(), $totp_token);
        } catch (Api_Exception $e) {
            return $this->return_render_result_with_params($result_factory, $totp_private_key, 'e-generic-error');
        }

        // Wrong token provided
        if (!($result instanceof AcceptedCode)) {
            return $this->return_render_result_with_params($result_factory, $totp_private_key, 'e-totp-invalid-token');
        }

        //  Try setting user TOTP secret
        try {
            $twofas_user->fetch_from_2fas()->set_totp_secret($totp_private_key)->set_active_method('totp')->push_to_2fas();
        } catch (Api_Exception $e) {
            return $this->return_render_result_with_params($result_factory, $totp_private_key, 'e-generic-error');
        }
        
        $result_factory->add_notification_key('s-totp');
        $user_data->set_totp_as_auth_method();
        $user_data->set_totp_as_configured_enabled();
        
        //  Logout on other devices if new TOTP private key has been generated
        if ($this->totp_private_key_status === TwoFAS_Configure_TOTP::TWOFAS_TOTP_SECRET_FRESH ) {
            $this->logout_on_other_devices($user_data);
        }

        // Add current device to trusted device list
        // if it hasn't already been added
        $this->add_device_as_trusted($storage, $result_factory);

        return $result_factory->get_result_redirect();
    }

    /**
     * @param TwoFAS_App $app
     *
     * @return null|string
     */
    private function generate_totp_private_key(TwoFAS_App $app)
    {
        // Generate TOTP key
        if (!$app->get_request()->has_param('totp-private-key')) {
            $user = $app->get_user()->fetch_from_2fas();
            $totp_secret = $user->get_totp_secret();
            
            if (!$totp_secret) {
                $totp_secret = TotpSecretGenerator::generate();
                $this->totp_private_key_status = self::TWOFAS_TOTP_SECRET_FRESH;
                $user->set_totp_secret($totp_secret);
            }
            
            return $totp_secret;
        }

        return $app->get_request()->get_param('totp-private-key');
    }
}
