<?php

namespace TwoFAS\Actions\AdminChannel;

use TwoFAS\Actions\Result\TwoFAS_Action_Result_Redirect;
use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;
use TwoFAS\TwoFAS_App;
use TwoFAS\UserZone\Exception\Exception;
use TwoFAS\UserZone\Integration;

abstract class TwoFAS_Enable_Channel_Globally extends TwoFAS_Action
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
     * @param TwoFAS_Authentication_Channels $authentication_channels
     * @param TwoFAS_WP_Userdata_Storage     $user_data
     *
     * @return Integration
     */
    protected abstract function enable_channel(
        TwoFAS_Authentication_Channels $authentication_channels,
        TwoFAS_WP_Userdata_Storage $user_data
    );

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result_Redirect
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        if ($app->get_request()->is_valid_action_call($this->action_id)) {
            try {
                $integration = $this->enable_channel(
                    $app->get_authentication_channels(),
                    $app->get_storage()->get_userdata()
                );

                $app->get_sdk_bridge()->get_user_zone()->updateIntegration($integration);

                return $app->get_result_factory()
                    ->add_notification_key($this->success_notification_key)
                    ->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN);
            } catch (Exception $e) {
                return $app->get_result_factory()
                    ->add_notification_key($this->error_notification_key)
                    ->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN);
            }
        }
        
        return $app->get_result_factory()->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN);
    }
}
