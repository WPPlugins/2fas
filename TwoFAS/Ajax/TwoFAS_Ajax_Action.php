<?php

namespace TwoFAS\Ajax;

use TwoFAS\TwoFAS_App;

abstract class TwoFAS_Ajax_Action
{
    /**
     * @var TwoFAS_App
     */
    protected $app;

    /**
     * @param TwoFAS_App $app
     */
    public function __construct(TwoFAS_App $app)
    {
        $this->app = $app;
    }

    public abstract function handle();

    /**
     * @return bool
     */
    protected function is_ajax_request()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && 'xmlhttprequest' === strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
}
