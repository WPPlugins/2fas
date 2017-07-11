<?php

namespace TwoFAS\Actions\Result;

use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Notifications\TwoFAS_Notification_Renderer;
use TwoFAS\Notifications\TwoFAS_Notifications_Collection;
use TwoFAS\Templates\TwoFAS_Template;

class TwoFAS_Action_Result_Render implements TwoFAS_Action_Result
{
    /**
     * @var
     */
    private $html;
    /**
     * @var
     */
    private $notifications_html;

    /**
     * @var TwoFAS_Notification_Renderer
     */
    private $twofas_notification_renderer;

    const TWOFAS_TEMPLATE_NOTIFICATIONS_KEY = 'twofas_notifications';

    /**
     * TwoFAS_Result_Render constructor.
     *
     * @param TwoFAS_Notification_Renderer $twofas_notification_renderer
     */
    public function __construct(TwoFAS_Notification_Renderer $twofas_notification_renderer)
    {
        $this->twofas_notification_renderer = $twofas_notification_renderer;
    }

    /**
     * @param TwoFAS_Notifications_Collection $collection
     *
     * @return $this
     */
    public function consume_notifications(TwoFAS_Notifications_Collection $collection)
    {
        $this->notifications_html = $this->twofas_notification_renderer->get_html_from_notification_collection($collection);
        return $this;
    }

    /**
     * @param TwoFAS_Action $action
     */
    public function handle(TwoFAS_Action $action)
    {
        $action->set_viewable_result($this->html);
    }

    /**
     * @param TwoFAS_Template $template
     * @param                 $template_name
     * @param                 $arguments
     */
    public function render_view(TwoFAS_Template $template, $template_name, $arguments)
    {
        if($this->notifications_html) {
            $arguments[self::TWOFAS_TEMPLATE_NOTIFICATIONS_KEY] = $this->notifications_html;
        }
        
        $this->html = $template->render_template($template_name, $arguments);
    }
}
