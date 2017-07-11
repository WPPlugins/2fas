<?php

namespace TwoFAS\Notifications;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Templates\TwoFAS_Template;

class TwoFAS_Notification_Renderer
{
    /**
     * @var string
     */
    private $default_error_notification = "<div class=\"notice notice-error is-dismissible error\"><p>Something went wrong. Please try again.</p></div>";

    /**
     * @var array
     */
    private $dictionary;

    /**
     * TwoFAS_Notification_Renderer constructor.
     */
    public function __construct()
    {
        // Only because some content has to be dynamically created
        $this->dictionary = array(
            //  TwoFAS validator messages
            'e-email-required'   => 'Please enter your e-mail',
            'e-email-validation' => 'E-mail is invalid',
            'e-email-unique'     => 'E-mail already exists, click '.TwoFAS_Template::generate_link_to_action( TwoFAS_Action_Map::TWOFAS_ACTION_RESET_PASSWORD, 'here', 'twofas-submenu-admin').' to reset your password',
            
            'e-channel-status'            => 'Could not get channel details. Please try reload this page.',
            'e-2fa-disabled'              => 'Two Factor Authentication is disabled',
            'e-account-required'          => 'Before starting to use Two Factor Authentication Service plugin, you have to create 2FAS account or log in to the existing one',
            'e-login-email-required'      => 'Please enter your 2FAS e-mail',
            'e-login-password-required'   => 'Please enter your 2FAS password',
            'e-login-invalid-credentials' => 'Invalid credentials entered',
            
            //  Update password
            'e-empty-password'   => 'Please enter your password',
            's-update-password'  => 'Your password has been updated',
            'e-invalid-password' => 'Wrong password provided',
            
            //  Disable channels
            's-disable-call-globally' => 'Voice call channel has been disabled globally',
            'e-disable-call-globally' => 'Could not change Voice call channel status. Please try again.',
            's-disable-sms-globally'  => 'Text message channel has been disabled globally',
            'e-disable-sms-globally'  => 'Could not change Text message channel status. Please try again.',
            's-disable-totp-globally' => 'TOTP channel has been disabled globally',
            'e-disable-totp-globally' => 'Could not change TOTP channel status. Please try again.',

            //  Force disable channels globally
            's-force-disable-call-globally' => 'Voice call channel has been disabled globally',
            'e-force-disable-call-globally' => 'Could not change Voice call channel status. Please try again.',
            's-force-disable-sms-globally'  => 'Text message channel has been disabled globally',
            'e-force-disable-sms-globally'  => 'Could not change Text message channel status. Please try again.',
            's-force-disable-totp-globally' => 'TOTP channel has been disabled globally',
            'e-force-disable-totp-globally' => 'Could not change TOTP channel status. Please try again.',
            
            //  Enable channels globally
            's-enable-call-globally' => 'Voice call channel has been enabled globally',
            'e-enable-call-globally' => 'Could not change Voice call channel status. Please try again.',
            's-enable-sms-globally'  => 'Text message channel has been enabled globally',
            'e-enable-sms-globally'  => 'Could not change Text message channel status. Please try again.',
            's-enable-totp-globally' => 'TOTP channel has been enabled globally',
            'e-enable-totp-globally' => 'Could not change TOTP channel status. Please try again.',
            
            //  Action logout
            's-logout' => 'You have been logged out from 2FAS',
            
            //  Trusted devices
            's-trusted-device-added'    => 'Your browser has been added to the trusted device list',
            's-device-removed'          => 'Trusted device has been removed',
            'e-could-not-remove-device' => 'Could not remove trusted device. Please try again.',
            
            //  Disable channels by user
            's-disable-call' => 'You have disabled call authentication for your account',
            'e-disable-call' => 'Something went wrong. Please reload this page before taking next action',
            's-disable-sms'  => 'You have disabled Text message authentication for your account',
            'e-disable-sms'  => 'Something went wrong. Please reload this page before taking next action',
            's-disable-totp' => 'You have disabled TOTP for your account',
            'e-disable-totp' => 'Something went wrong. Please reload this page before taking next action',

            //  Enable channels by user
            's-enable-call' => 'You have enabled Two Factor Authentication via Voice call',
            'e-enable-call' => 'Could not enable Two Factor Authentication via Voice call',
            's-enable-sms'  => 'You have enabled Two Factor Authentication via Text message',
            'e-enable-sms'  => 'Could not enable Two Factor Authentication via Text message',
            's-enable-totp' => 'You have enabled Two Factor Authentication via TOTP',
            'e-enable-totp' => 'Could not enable Two Factor Authentication via TOTP',
            
            //  Remove configuration
            's-configuration-removed' => 'Configuration has been removed successfully',
            
            //  Reset password
            's-reset-password' => 'If you entered a valid email, you will receive the instructions to reset your password. Please check your inbox.',
            
            //  Totp configuration
            'e-totp-unexpected'    => 'Something went wrong, please try to reload the QR code below',
            'e-totp-token-format'  => 'Wrong token format, please check entered token',
            'e-totp-invalid-token' => 'Wrong token entered, please scan the QR code again',
            's-totp'               => 'TOTP has been configured successfully',
            
            //  Phone configuration
            'e-empty-phone-number'                        => 'Please enter your phone number',
            's-phone-channel-call-code-sent'              => 'Please wait for our call with your code',
            's-phone-channel-sms-code-sent'               => 'Code has been sent',
            'e-invalid-phone-number'                      => 'Phone number is invalid',
            's-configure-call'                            => 'Two Factor Authentication via voice call has been configured and enabled',
            'e-configure-call'                            => 'Wrong code entered, please try again',
            's-configure-sms'                             => 'Two Factor Authentication via text message has been configured and enabled',
            'e-configure-sms'                             => 'Wrong code entered, please try again',
            'e-configure-phone-invalid-code-can-retry'    => 'Wrong code entered, please try again',
            'e-configure-phone-invalid-code-cannot-retry' => 'Wrong code has been entered. Please take note that token has limited lifetime. You can enter your phone number again.',

            'e-cannot-connect-to-2fas' => 'Could not connect to 2FAS. Please refresh this page.',

            // Default error
            'e-generic-error' => 'Something went wrong. Please try again.',
        );
    }

    /**
     * @return array
     */
    public function get_dictionary()
    {
        return $this->dictionary;
    }

    /**
     * @param array $dictionary
     */
    public function set_dictionary(array $dictionary)
    {
        $this->dictionary = $dictionary;
    }

    /**
     * @param TwoFAS_Notifications_Collection $notification_collection
     *
     * @return string
     */
    public function get_html_from_notification_collection(TwoFAS_Notifications_Collection $notification_collection)
    {
        $keys = $notification_collection->get_notifications_as_keys();

        $result = '';

        foreach ($keys as $key) {
            $result .= $this->get_notification_as_html($key);
        }

        return $result;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function get_notification_as_html($key)
    {
        if (!is_string($key)
            || preg_match('/^[se]-[a-z-\d]+$/', $key) !== 1 // TODO:This regular expression occurs in 3 places!
            || !isset($this->dictionary[$key])
        ) {
            return $this->default_error_notification;
        }
        
        $message = $this->dictionary[$key];

        if ($key[0] === 's') {
            return $this->wrap_message_in_success_notification($message);
        }
        
        if ($key[0] === 'e') {
            return $this->wrap_message_in_error_notification($message);
        }
        
        return '';
    }

    /**
     * @param $message
     *
     * @return string
     */
    private function wrap_message_in_success_notification($message)
    {
        return "<div class=\"notice notice-success is-dismissible updated\"><p>".$message."</p></div>"; 
    }

    /**
     * @param $message
     *
     * @return string
     */
    private function wrap_message_in_error_notification($message)
    {
        return "<div class=\"notice notice-error is-dismissible error\"><p>".$message."</p></div>";
    }
}
