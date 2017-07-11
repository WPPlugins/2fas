<?php

namespace TwoFAS\Ajax;

use TwoFAS\Api\Exception\InvalidNumberException;
use TwoFAS\Authentication\TwoFAS_Authentication;
use TwoFAS\Notifications\TwoFAS_Notification_Renderer;

class TwoFAS_Authenticate_Via_Phone extends TwoFAS_Ajax_Action
{
    public function handle()
    {
        if ($this->is_ajax_request()) {
            $notification_renderer    = new TwoFAS_Notification_Renderer();
            $notifications_dictionary = $notification_renderer->get_dictionary();
            $user                     = $this->app->get_user();
            $api                      = $this->app->get_sdk_bridge()->get_api();
            $request                  = $this->app->get_request();
            $phone_number             = $request->get_from_post('phone_number');
            $channel                  = $request->get_from_post('channel');
            $action_name              = $request->get_from_post('action_name');
            $authentication_id        = null;
            $error_message            = '';
            $success_message          = '';
            $succeed                  = false;
            $notification_key         = $request->get_from_post('notification_key');
            

            // Verify nonce
            if (check_ajax_referer($action_name, 'security', false) === false) {
                wp_send_json(array(
                    'error_message' => $notifications_dictionary['e-generic-error']
                ));
            }

            $user->fetch_from_2fas()->set_phone_number($phone_number)->set_active_method($channel);

            if (empty($phone_number)) {
                $error_message = $notifications_dictionary['e-empty-phone-number'];
            } else {
                try {
                    $authentication_id = TwoFAS_Authentication::get_instance($api)->create($user);
                    $succeed           = true;
                    
                    if ($notification_key === '') {
                        $success_message = $notifications_dictionary['s-phone-channel-call-code-sent'];
                    } else {
                        $success_message = $notifications_dictionary['s-phone-channel-sms-code-sent'];
                    }
                } catch (InvalidNumberException $e) {
                    $error_message = $notifications_dictionary['e-invalid-phone-number'];
                } catch (\Exception $e) {
                    $error_message = $notifications_dictionary['e-generic-error'];
                }
            }

            $response = array(
                'authentication_id' => $authentication_id,
                'succeed'           => $succeed,
                'error_message'     => $error_message,
                'success_message'   => $success_message
            );

            wp_send_json($response);
        }

        die();
    }
}
