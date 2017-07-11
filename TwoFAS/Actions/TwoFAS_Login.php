<?php

namespace TwoFAS\Actions;

use TwoFAS\Templates\Views\TwoFAS_Views;
use TwoFAS\TwoFAS_App;
use TwoFAS\UserZone\Exception\AuthorizationException;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;
use TwoFAS\UserZone\Exception\ValidationException;

class TwoFAS_Login extends TwoFAS_User_Creation_Action
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_LOGIN;

    /**
     * @param TwoFAS_App $app
     *
     * @return Result\TwoFAS_Action_Result
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        $result_factory   = $app->get_result_factory();
        $userdata_storage = $app->get_storage()->get_userdata();
        $request          = $app->get_request();

        // Form submitted
        if ($app->get_request()->is_valid_action_call($this->action_id)
            && $request->has_params(array('twofas-email', 'twofas-password'))
        ) {
            $twofas_email    = $request->get_param('twofas-email');
            $twofas_password = $request->get_param('twofas-password');

            try {
                $this->init_twofas_integration($app, $twofas_email, $twofas_password);

                return $result_factory->get_result_redirect();
            } catch (ValidationException $e) {
                $notification = $this->map_validation_error_to_notification_key($e->getErrors());
                return $app->get_result_factory()->add_notification_key($notification)->get_result_redirect(null, $this->action_id);
            } catch (AuthorizationException $e) {
                return $result_factory->add_notification_key('e-login-invalid-credentials')->get_result_redirect(null, $this->action_id);
            } catch (User_Zone_Exception $e) {
                return $result_factory->add_notification_key('e-generic-error')->get_result_redirect(null, $this->action_id);
            }
        }

        return $result_factory
            ->add_notifications_from_url()
            ->get_result_render(TwoFAS_Views::LOGIN_FORM, array(
                'email' => $userdata_storage->get_mail()
            ));
    }
}
