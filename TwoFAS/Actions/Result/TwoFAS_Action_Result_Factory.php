<?php

namespace TwoFAS\Actions\Result;

use TwoFAS\Notifications\TwoFAS_Notification_Renderer;
use TwoFAS\Notifications\TwoFAS_Notifications_Collection;
use TwoFAS\Request\TwoFAS_Request;
use TwoFAS\Templates\TwoFAS_Template;

class TwoFAS_Action_Result_Factory implements TwoFAS_Notifications_Collection
{
    const TWOFAS_NOTIFICATIONS_URL_KEY = 'twofas-notifications';

    /**
     * @var array
     */
    private $keys = array();

    /**
     * @var TwoFAS_Template
     */
    private $twofas_template;

    /**
     * @var TwoFAS_Request
     */
    private $twofas_request;

    /**
     * @param TwoFAS_Template $twofas_template
     * @param TwoFAS_Request  $twofas_request
     */
    public function __construct(TwoFAS_Template $twofas_template, TwoFAS_Request $twofas_request)
    {
        $this->twofas_request  = $twofas_request;
        $this->twofas_template = $twofas_template;
    }

    /**
     * @param string|null $page
     * @param string|null $action
     *
     * @return TwoFAS_Action_Result_Redirect
     */
    public function get_result_redirect($page = null, $action = null)
    {
        $redirect = new TwoFAS_Action_Result_Redirect();
        $redirect->consume_notifications($this);
        $redirect->set_redirection_target($page, $action);
        return $redirect;
    }

    /**
     * @return $this
     */
    public function add_notifications_from_url()
    {
        $notifications_keys_from_url = $this->twofas_request->get_from_get(self::TWOFAS_NOTIFICATIONS_URL_KEY);

        if (!is_array($notifications_keys_from_url)) {
            return $this;
        }
        
        foreach ($notifications_keys_from_url as $value) {
           $this->add_notification_key($value);
        }
        
        return $this;
    }

    /**
     * @param string $template_name
     * @param array  $arguments
     *
     * @return TwoFAS_Action_Result_Render
     */
    public function get_result_render($template_name, array $arguments = array())
    {
        $render = new TwoFAS_Action_Result_Render(new TwoFAS_Notification_Renderer());
        $render->consume_notifications($this);
        $render->render_view($this->twofas_template, $template_name, $arguments);
        return $render;
    }

    /**
     * @param $key
     *
     * @return $this
     */
    public function add_notification_key($key)
    {
        if (is_string($key)
            && preg_match('/^[se]-[a-z-\d]+$/', $key) === 1
        ) {
            $this->keys[] = $key;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function get_notifications_as_keys()
    {
        return $this->keys;
    }
}
