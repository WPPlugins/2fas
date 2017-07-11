<?php

namespace TwoFAS\Actions\UserChannel;

use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Channels\TwoFAS_Authentication_Channels;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;

class TwoFAS_Configure_SMS extends TwoFAS_Configure_Phone_Channel
{
    /**
     * @var string
     */
    protected $action_id = TwoFAS_Action_Map::TWOFAS_ACTION_CONFIGURE_SMS;

    /**
     * @var string
     */
    protected $template_name = 'configure_sms.html';

    /**
     * @var string
     */
    protected $method_name = 'sms';

    /**
     * @var string
     */
    protected $notification_class_name = 'configure_sms';

    /**
     * @var string
     */
    protected $success_notification_key = 's-configure-sms';

    /**
     * @var string
     */
    protected $error_notification_key = 'e-configure-sms';
    
    /**
     * @param TwoFAS_Authentication_Channels $authentication_channels
     *
     * @return bool
     */
    public function method_can_be_configured(TwoFAS_Authentication_Channels $authentication_channels)
    {
        return $authentication_channels->get_sms_status() === TwoFAS_Authentication_Channels::CHANNEL_STATUS_ENABLED;
    }

    /**
     * @return string
     */
    public function get_template_name()
    {
        return $this->template_name;
    }

    /**
     * @return string
     */
    public function get_authentication_method()
    {
        return $this->method_name;
    }

    /**
     * @return string
     */
    public function get_notification_class()
    {
        return $this->notification_class_name;
    }

    /**
     * @param TwoFAS_WP_Userdata_Storage $userdata
     */
    public function set_fields_in_database(TwoFAS_WP_Userdata_Storage $userdata)
    {
        $userdata->set_sms_as_auth_method();
        $userdata->set_sms_as_configured_enabled();
    }
}
