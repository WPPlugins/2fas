<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\Result\TwoFAS_Action_Result_Redirect;
use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Api\Exception\Exception;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\TwoFAS_App;
use TwoFAS\User\TwoFAS_User;

abstract class TwoFAS_Enable_Channel extends TwoFAS_Action
{
    /**
     * @var string
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
     * @param TwoFAS_WP_Userdata_Storage $user_data
     */
    public abstract function enable_channel(TwoFAS_WP_Userdata_Storage $user_data);

    /**
     * @return string
     */
    public abstract function get_channel_name();

    /**
     * @param TwoFAS_WP_Userdata_Storage $user_data
     * @param TwoFAS_User                $user
     *
     * @throws \TwoFAS\Api\Exception\Exception
     */
    public function set_method_as_used_and_configured_enabled(TwoFAS_WP_Userdata_Storage $user_data, TwoFAS_User $user)
    {
        $user->fetch_from_2fas()
            ->set_active_method($this->get_channel_name())
            ->push_to_2fas();

        $this->enable_channel($user_data);
    }

    
    /**
     * @param TwoFAS_WP_Userdata_Storage $userdata
     *
     * @return bool
     */
    public abstract function is_method_configured_disabled(TwoFAS_WP_Userdata_Storage $userdata);

    /**
     * @param TwoFAS_WP_Userdata_Storage $userdata
     */
    public abstract function set_other_methods_as_disabled(TwoFAS_WP_Userdata_Storage $userdata);

    /**
     * @param TwoFAS_Authentication_Channels $authentication_channels
     *
     * @return bool
     */
    public abstract function is_method_active(TwoFAS_Authentication_Channels $authentication_channels);

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result_Redirect
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $result_factory = $app->get_result_factory();

        if (!$app->get_storage()->client_completed_registration()) {
            return $result_factory->get_result_redirect();
        }

        $user_data = $app->get_storage()->get_userdata();

        try {
            $authentication_channels = $app->get_authentication_channels();
        } catch (\TwoFAS\UserZone\Exception\Exception $e) {
            return $result_factory->add_notification_key('e-generic-error')->get_result_redirect();
        }

        if ($this->is_method_configured_disabled($user_data)
            && $this->is_method_active($authentication_channels)
            && $app->get_request()->is_valid_action_call($this->action_id)
        ) {
            try {
                $this->set_method_as_used_and_configured_enabled($user_data, $app->get_user());
                $this->set_other_methods_as_disabled($user_data);
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
