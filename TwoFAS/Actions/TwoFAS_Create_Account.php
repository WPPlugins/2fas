<?php

namespace TwoFAS\Actions;

use TwoFAS\Request\TwoFAS_Request;
use TwoFAS\TwoFAS_App;
use TwoFAS\UserZone\Exception\ValidationException;
use TwoFAS\UserZone\Exception\Exception as User_Zone_Exception;

class TwoFAS_Create_Account extends TwoFAS_User_Creation_Action
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_CREATE_ACCOUNT;

    /**
     * @var string
     */
    private $template_file = 'create_account.html';

    /**
     * @param TwoFAS_App $app
     *
     * @return Result\TwoFAS_Action_Result
     */
    public function execute_own_strategy(TwoFAS_App $app)
    {
        if ($this->form_is_submitted($app->get_request())) {
            return $this->handle_submitted_form($app);
        }
        
        return $app->get_result_factory()
            ->add_notifications_from_url()
            ->get_result_render($this->template_file, array(
                'email' => $app->get_storage()->get_userdata()->get_mail()
            ));
    }

    /**
     * @param TwoFAS_App $app
     *
     * @return Result\TwoFAS_Action_Result_Redirect
     */
    private function handle_submitted_form(TwoFAS_App $app)
    {
        $user_zone       = $app->get_sdk_bridge()->get_user_zone();
        $twofas_email    = $app->get_request()->get_param('twofas-email');
        $twofas_password = md5(uniqid());

        try {
            $user_zone->createClient($twofas_email, $twofas_password, $twofas_password, 'wordpress');
            $this->init_twofas_integration($app, $twofas_email, $twofas_password);
            return $app->get_result_factory()->get_result_redirect();
        } catch (ValidationException $e) {
            $notification = $this->map_validation_error_to_notification_key($e->getErrors());
            return $app->get_result_factory()->add_notification_key($notification)->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN, $this->action_id);
        } catch (User_Zone_Exception $e) {
            return $app->get_result_factory()->add_notification_key('e-generic-error')->get_result_redirect(TwoFAS_Action_Map::TWOFAS_SUBMENU_ADMIN, $this->action_id);
        }
    }

    /**
     * @param TwoFAS_Request $request
     * 
     * @return bool
     */
    private function form_is_submitted(TwoFAS_Request $request)
    {
        return $request->is_valid_action_call($this->action_id)
            && $request->has_param('twofas-email');
    }
}
