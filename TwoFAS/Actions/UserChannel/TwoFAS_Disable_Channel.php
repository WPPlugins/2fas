<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\Result\TwoFAS_Action_Result_Redirect;
use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Api\Exception\Exception;
use TwoFAS\Api\Exception\Exception as Api_Exception;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\TwoFAS_App;
use TwoFAS\User\TwoFAS_User;

abstract class TwoFAS_Disable_Channel extends TwoFAS_Action
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_DISABLE_TOTP;

    /**
     * @var string
     */
    protected $success_notification_key = '';

    /**
     * @var string
     */
    protected $error_notification_key = '';
    
    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     * @param TwoFAS_User                $user
     *
     * @throws Api_Exception
     */
    public function disable_method(TwoFAS_WP_Userdata_Storage $user_data, TwoFAS_User $user)
    {
        $user          = $user->fetch_from_2fas();
        $active_method = $user->get_active_method();

        if ($active_method == $this->get_channel_name()) {
            $user->set_active_method(null)->push_to_2fas();
        }

        $this->set_method_as_configured_disabled($user_data);
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     *
     * @return bool
     */
    public abstract function is_method_configured_enabled_and_used(TwoFAS_WP_Userdata_Storage $user_data);

    /**
     * @return string
     */
    public abstract function get_channel_name();

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     */
    public abstract function set_method_as_configured_disabled(TwoFAS_WP_Userdata_Storage $user_data);

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result_Redirect
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $result_factory = $app->get_result_factory();
        $user_data      = $app->get_storage()->get_userdata();

        if (!$app->get_storage()->client_completed_registration()) {
            return $result_factory->get_result_redirect();
        }

        if ($this->is_method_configured_enabled_and_used($user_data)
            && $app->get_request()->is_valid_action_call($this->action_id)
        ) {
            try {
                $this->disable_method($user_data, $app->get_user());
                $user_data->disable_auth_method();
                $result_factory->add_notification_key($this->success_notification_key);
            } catch (Exception $e) {
                $result_factory->add_notification_key($this->error_notification_key);
            }
        } else {
            $result_factory->add_notification_key($this->error_notification_key);
        }
        
        return $result_factory->get_result_redirect();
    }
}
