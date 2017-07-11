<?php

namespace TwoFAS\Actions;

use TwoFAS\Actions\Result\TwoFAS_Action_Result;
use TwoFAS\Templates\Views\TwoFAS_Views;
use TwoFAS\UserZone\Exception\Exception;
use TwoFAS\UserZone\Exception\NotFoundException;
use TwoFAS\UserZone\Exception\ValidationException;
use TwoFAS\TwoFAS_App;

class TwoFAS_Reset_Password extends TwoFAS_User_Creation_Action
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_RESET_PASSWORD;

    /**
     * @param TwoFAS_App $app
     *
     * @return TwoFAS_Action_Result
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $request        = $app->get_request();
        $result_factory = $app->get_result_factory();
        $user_zone      = $app->get_sdk_bridge()->get_user_zone();

        // Form submitted
        if ($app->get_request()->is_valid_action_call($this->action_id)) {
            // User 2FAS credentials
            $twofas_email = $request->get_param('twofas-email');
            try {
                $user_zone->resetPassword($twofas_email);
                $result_factory->add_notification_key('s-reset-password');
                return $result_factory->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN, TwoFAS_Action_Map::TWOFAS_ACTION_LOGIN);
            } catch (NotFoundException $e) {
                $result_factory->add_notification_key('s-reset-password');
                return $result_factory->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN, TwoFAS_Action_Map::TWOFAS_ACTION_LOGIN);
            } catch (ValidationException $e) {
                $key = $this->map_validation_error_to_notification_key($e->getErrors());
                $result_factory->add_notification_key($key);
            } catch (Exception $e) {
                $result_factory->add_notification_key('e-generic-error');
            }
            return $result_factory->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN, $this->action_id);
        }

        return $result_factory->add_notifications_from_url()->get_result_render(TwoFAS_Views::RESET_PASSWORD,
            array(
                'email' => $app->get_storage()->get_userdata()->get_mail()
            ));
    }
}
