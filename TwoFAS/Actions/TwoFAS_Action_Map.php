<?php

namespace TwoFAS\Actions;

class TwoFAS_Action_Map
{
    const TWOFAS_ACTION_LOGIN           = 'twofas-login';
    const TWOFAS_ACTION_LOGOUT          = 'twofas-logout';
    const TWOFAS_ACTION_RESET_PASSWORD  = 'twofas-reset-password';
    const TWOFAS_ACTION_CREATE_ACCOUNT  = 'twofas-create-account';

    const TWOFAS_ACTION_ENABLE_TOTP_GLOBALLY        = 'twofas-enable-totp-globally';
    const TWOFAS_ACTION_DISABLE_TOTP_GLOBALLY       = 'twofas-disable-totp-globally';
    const TWOFAS_ACTION_FORCE_DISABLE_TOTP_GLOBALLY = 'twofas-force-disable-totp-globally';
    const TWOFAS_ACTION_ENABLE_SMS_GLOBALLY         = 'twofas-enable-sms-globally';
    const TWOFAS_ACTION_DISABLE_SMS_GLOBALLY        = 'twofas-disable-sms-globally';
    const TWOFAS_ACTION_FORCE_DISABLE_SMS_GLOBALLY  = 'twofas-force-disable-sms-globally';
    const TWOFAS_ACTION_ENABLE_CALL_GLOBALLY        = 'twofas-enable-call-globally';
    const TWOFAS_ACTION_DISABLE_CALL_GLOBALLY       = 'twofas-disable-call-globally';
    const TWOFAS_ACTION_FORCE_DISABLE_CALL_GLOBALLY = 'twofas-force-disable-call-globally';

    const TWOFAS_ACTION_ENABLE_TOTP = 'twofas-enable-totp';
    const TWOFAS_ACTION_ENABLE_SMS  = 'twofas-enable-sms';
    const TWOFAS_ACTION_ENABLE_CALL = 'twofas-enable-call';

    const TWOFAS_ACTION_DISABLE_TOTP = 'twofas-disable-totp';
    const TWOFAS_ACTION_DISABLE_SMS  = 'twofas-disable-sms';
    const TWOFAS_ACTION_DISABLE_CALL = 'twofas-disable-call';

    const TWOFAS_ACTION_DEFAULT                = 'twofas-default';
    const TWOFAS_ACTION_REMOVE_TRUSTED_MACHINE = 'twofas-remove-trusted-machine';

    const TWOFAS_ACTION_CONFIGURE_TOTP            = 'twofas-configure-totp';
    const TWOFAS_ACTION_CONFIGURE_SMS             = 'twofas-configure-sms';
    const TWOFAS_ACTION_CONFIGURE_CALL            = 'twofas-configure-call';
    const TWOFAS_ACTION_REMOVE_CALL_CONFIGURATION = 'twofas-remove-call-configuration';
    const TWOFAS_ACTION_REMOVE_SMS_CONFIGURATION  = 'twofas-remove-sms-configuration';
    const TWOFAS_ACTION_REMOVE_TOTP_CONFIGURATION = 'twofas-remove-totp-configuration';

    const TWOFAS_SUBMENU_ADMIN   = 'twofas-submenu-admin';
    const TWOFAS_SUBMENU_CHANNEL = 'twofas-submenu-channel';

    /**
     * @var array
     */
    private $map = array(
        self::TWOFAS_SUBMENU_ADMIN   => array(
            self::TWOFAS_ACTION_LOGIN                       => 'TwoFAS\Actions\TwoFAS_Login',
            self::TWOFAS_ACTION_LOGOUT                      => 'TwoFAS\Actions\TwoFAS_Logout',
            self::TWOFAS_ACTION_RESET_PASSWORD              => 'TwoFAS\Actions\TwoFAS_Reset_Password',
            self::TWOFAS_ACTION_CREATE_ACCOUNT              => 'TwoFAS\Actions\TwoFAS_Create_Account',
            self::TWOFAS_ACTION_DEFAULT                     => 'TwoFAS\Actions\TwoFAS_Display_Admin_Menu',
            self::TWOFAS_ACTION_ENABLE_TOTP_GLOBALLY        => 'TwoFAS\Actions\AdminChannel\TwoFAS_Enable_Totp_Globally',
            self::TWOFAS_ACTION_DISABLE_TOTP_GLOBALLY       => 'TwoFAS\Actions\AdminChannel\TwoFAS_Disable_Totp_Globally',
            self::TWOFAS_ACTION_FORCE_DISABLE_TOTP_GLOBALLY => 'TwoFAS\Actions\AdminChannel\TwoFAS_Force_Disable_Totp_Globally',
            self::TWOFAS_ACTION_ENABLE_SMS_GLOBALLY         => 'TwoFAS\Actions\AdminChannel\TwoFAS_Enable_Sms_Globally',
            self::TWOFAS_ACTION_DISABLE_SMS_GLOBALLY        => 'TwoFAS\Actions\AdminChannel\TwoFAS_Disable_Sms_Globally',
            self::TWOFAS_ACTION_FORCE_DISABLE_SMS_GLOBALLY  => 'TwoFAS\Actions\AdminChannel\TwoFAS_Force_Disable_Sms_Globally',
            self::TWOFAS_ACTION_ENABLE_CALL_GLOBALLY        => 'TwoFAS\Actions\AdminChannel\TwoFAS_Enable_Call_Globally',
            self::TWOFAS_ACTION_DISABLE_CALL_GLOBALLY       => 'TwoFAS\Actions\AdminChannel\TwoFAS_Disable_Call_Globally',
            self::TWOFAS_ACTION_FORCE_DISABLE_CALL_GLOBALLY => 'TwoFAS\Actions\AdminChannel\TwoFAS_Force_Disable_Call_Globally',
        ),
        self::TWOFAS_SUBMENU_CHANNEL => array(
            self::TWOFAS_ACTION_ENABLE_TOTP               => 'TwoFAS\Actions\UserChannel\TwoFAS_Enable_TOTP',
            self::TWOFAS_ACTION_ENABLE_SMS                => 'TwoFAS\Actions\UserChannel\TwoFAS_Enable_SMS',
            self::TWOFAS_ACTION_ENABLE_CALL               => 'TwoFAS\Actions\UserChannel\TwoFAS_Enable_Call',
            self::TWOFAS_ACTION_DISABLE_TOTP              => 'TwoFAS\Actions\UserChannel\TwoFAS_Disable_TOTP',
            self::TWOFAS_ACTION_DISABLE_SMS               => 'TwoFAS\Actions\UserChannel\TwoFAS_Disable_SMS',
            self::TWOFAS_ACTION_DISABLE_CALL              => 'TwoFAS\Actions\UserChannel\TwoFAS_Disable_Call',
            self::TWOFAS_ACTION_CONFIGURE_TOTP            => 'TwoFAS\Actions\UserChannel\TwoFAS_Configure_TOTP',
            self::TWOFAS_ACTION_CONFIGURE_SMS             => 'TwoFAS\Actions\UserChannel\TwoFAS_Configure_SMS',
            self::TWOFAS_ACTION_CONFIGURE_CALL            => 'TwoFAS\Actions\UserChannel\TwoFAS_Configure_Call',
            self::TWOFAS_ACTION_REMOVE_TOTP_CONFIGURATION => 'TwoFAS\Actions\UserChannel\TwoFAS_Remove_TOTP_Configuration',
            self::TWOFAS_ACTION_REMOVE_SMS_CONFIGURATION  => 'TwoFAS\Actions\UserChannel\TwoFAS_Remove_SMS_Configuration',
            self::TWOFAS_ACTION_REMOVE_CALL_CONFIGURATION => 'TwoFAS\Actions\UserChannel\TwoFAS_Remove_Call_Configuration',
            self::TWOFAS_ACTION_DEFAULT                   => 'TwoFAS\Actions\TwoFAS_Display_User_Menu',
            self::TWOFAS_ACTION_REMOVE_TRUSTED_MACHINE    => 'TwoFAS\Actions\TwoFAS_Remove_Trusted_Device',
        )
    );

    /**
     * @param $page
     * @param $action
     *
     * @return bool
     */
    public function validate_page_action_pair($page, $action) {
        return is_string($page) 
            && is_string($action)
            && preg_match('/^twofas-[a-z-]+$/', $page) === 1
            && preg_match('/^twofas-[a-z-]+$/', $page) === 1
            && isset($this->map[$page]);
    }

    /**
     * @param string $page
     * @param string $action
     *
     * @return null|TwoFAS_Action
     */
    public function get_action($page, $action)
    {
        if (!$this->validate_page_action_pair($page, $action)) {
            return null;
        }
        
        $actions_on_page = isset($this->map[$page]) ? $this->map[$page] : null;
        
        if (is_string($action)) {
            $action_name = isset($actions_on_page[$action]) ? $action : self::TWOFAS_ACTION_DEFAULT;
        } else {
            $action_name = self::TWOFAS_ACTION_DEFAULT;
        }

        return new $actions_on_page[$action_name];
    }
}
