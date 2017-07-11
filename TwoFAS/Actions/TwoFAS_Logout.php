<?php

namespace TwoFAS\Actions;

use TwoFAS_Uninstaller;
use TwoFAS\TwoFAS_App;

class TwoFAS_Logout extends TwoFAS_Action
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_LOGOUT;

    /**
     * @param TwoFAS_App $app
     *
     * @return Result\TwoFAS_Action_Result_Redirect
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $result_factory = $app->get_result_factory();

        if ($app->get_request()->is_valid_action_call($this->action_id)
        ) {
            $uninstaller = new TwoFAS_Uninstaller();
            $uninstaller->uninstall();
            
            $result_factory->add_notification_key('s-logout');
        }
        
        return $result_factory->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN, TwoFAS_Action_Map::TWOFAS_ACTION_CREATE_ACCOUNT);
    }
}
