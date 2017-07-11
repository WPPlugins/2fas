<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\TwoFAS_App;
use TwoFAS\User\TwoFAS_User;
use TwoFAS\Api\Exception\Exception as Api_Exception;

abstract class TwoFAS_Remove_Configuration extends TwoFAS_Action
{
    /**
     * @var string
     */
    protected $action_id = null;

    /**
     * @param TwoFAS_WP_Userdata_Storage $storage
     * @param TwoFAS_User                $user
     *
     * @throws Api_Exception
     */
    public abstract function remove_configuration(TwoFAS_WP_Userdata_Storage $storage, TwoFAS_User $user);

    public function execute_own_strategy(TwoFAS_App $app)
    {
        $result_factory = $app->get_result_factory();
        
        if ($app->get_request()->is_valid_action_call($this->action_id)) {
            $this->remove_configuration($app->get_storage()->get_userdata(), $app->get_user());
            $result_factory->add_notification_key('s-configuration-removed');
        }

        return $result_factory->get_result_redirect();
    }
}
