<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\Result\TwoFAS_Action_Result;
use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Api\Authentication;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\Exception\Exception;
use TwoFAS\Authentication\TwoFAS_Code_State;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\TwoFAS_App;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;

abstract class TwoFAS_Configure_Phone_Channel extends TwoFAS_Configure_Channel
{
    /**
     * @var null
     */
    protected $action_id = null;
    
    /**
     * @var string
     */
    protected $success_notification_key = '';

    /**
     * @var string
     */
    protected $error_notification_key = '';

    /**
     * @return string
     */
    public abstract function get_template_name();

    /**
     * @return string
     */
    public abstract function get_authentication_method();

    /**
     * @return string
     */
    public abstract function get_notification_class();

    /**
     * @param TwoFAS_Authentication_Channels $authentication_channels
     *
     * @return bool
     */
    public abstract function method_can_be_configured(TwoFAS_Authentication_Channels $authentication_channels);

    /**
     * @param TwoFAS_WP_Userdata_Storage $userdata
     */
    public abstract function set_fields_in_database(TwoFAS_WP_Userdata_Storage $userdata);

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $result_factory = $app->get_result_factory();

        if (!$app->get_storage()->client_completed_registration()) {
            return $result_factory->get_result_redirect();
        }

        try {
            $authentication_channels = $app->get_authentication_channels();
        } catch (User_Zone_Exception $e) {
            return $result_factory->add_notification_key('e-generic-error')->get_result_redirect();
        }

        if (!$this->method_can_be_configured($authentication_channels)) {
            $this->redirect_to_action();
        }
            
        $authentication_id = null;
        $phone_number      = null;

        // Handle submitted token
        $twofas_client_phone_number = $app->get_user()->fetch_from_2fas()->get_phone_number();
        if ($this->is_token_submitted($app)) {
            $request           = $app->get_request();
            $authentication_id = $request->get_param('authentication_id');
            $code              = $request->get_param('code');
            $phone_number      = $request->get_param('phone-number');

            $code_state = new TwoFAS_Code_State($code);
            $code_state->set_twofas($app->get_sdk_bridge()->get_api());

            $authentication  = new Authentication($authentication_id);
            $authentications = new AuthenticationCollection();
            $authentications->add($authentication);

            $user = $app->get_user();

            if ($code_state->is_empty()) {
                $result_factory->add_notification_key('e-configure-phone-empty-code');
            } elseif ($code_state->is_valid($authentications)) {
                try {
                    $user->fetch_from_2fas()
                        ->set_active_method($this->get_authentication_method())
                        ->set_phone_number($phone_number)
                        ->push_to_2fas();
                } catch (Exception $e) {
                    $result_factory->add_notification_key('e-generic-error');
                }

                $this->set_fields_in_database($app->get_storage()->get_userdata());

                if ($twofas_client_phone_number !== $phone_number) {
                    $this->logout_on_other_devices($app->get_storage()->get_userdata());
                }

                $this->add_device_as_trusted($app->get_storage(), $result_factory);
                return $result_factory->add_notification_key($this->success_notification_key)->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_CHANNEL, null);
            } elseif ($code_state->rejected_can_retry($authentications)) {
                $result_factory->add_notification_key('e-configure-phone-invalid-code-can-retry');
            } elseif ($code_state->rejected_cannot_retry($authentications)) {
                $result_factory->add_notification_key('e-configure-phone-invalid-code-cannot-retry');
                $authentication_id  = null;
            } elseif ($code_state->error()) {
                $result_factory->add_notification_key('e-generic-error');
                $authentication_id  = null;
            }
        }

        $viewable_phone_number = is_null($phone_number) ? $twofas_client_phone_number : $phone_number;

        return $result_factory->add_notifications_from_url()->get_result_render($this->get_template_name(),
            array(
                'authentication_id'    => $authentication_id,
                'phone_number'         => $viewable_phone_number
            ));
    }

    /**
     * @param TwoFAS_App $app
     *
     * @return bool
     */
    private function is_token_submitted(TwoFAS_App $app)
    {
        return $app->get_request()->has_param('code')
            && $app->get_request()->has_param('authentication_id')
            && $app->get_request()->has_param('phone-number')
            && $app->get_request()->is_valid_action_call($this->get_action_id());
    }
}
