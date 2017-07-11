<?php

namespace TwoFAS\Actions;

use TwoFAS\TwoFAS_App;
use TwoFAS\Actions\Result\TwoFAS_Action_Result;

abstract class TwoFAS_Action
{
    /**
     * @var string
     */
    protected $action_id = null;

    /**
     * @var string
     */
    protected $viewable_result = '';

    /**
     * @return string
     */
    public function get_action_id()
    {
        return $this->action_id;
    }

    /**
     * @param string $html
     */
    public function set_viewable_result($html)
    {
        $this->viewable_result = $html;
    }

    /**
     * @return string
     */
    public function get_viewable_result()
    {
        return $this->viewable_result;
    }

    /**
     * @param TwoFAS_App $app
     * 
     * @return TwoFAS_Action_Result
     */
    public abstract function execute_own_strategy(TwoFAS_App $app);

    /**
     * @return string
     */
    public function get_current_url()
    {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * @param string $url
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
        die();
    }

    /**
     * @param string $action
     */
    public function redirect_to_action($action = '')
    {
        $url = $_SERVER['REQUEST_URI'];
        $url = preg_replace('/&?twofas-action=[^&]*/', '', $url);

        if ($action) {
            $url .= '&twofas-action=' . $action;
        }

        header('Location: ' . $url);
        die();
    }
}
