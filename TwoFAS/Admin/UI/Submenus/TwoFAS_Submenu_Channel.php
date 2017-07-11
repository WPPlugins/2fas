<?php

namespace TwoFAS\Admin\UI\Submenus;

use TwoFAS\TwoFAS_App;
use TwoFAS\Admin\TwoFAS_Menu;

class TwoFAS_Submenu_Channel extends TwoFAS_Submenu
{
    /**
     * @var string
     */
    private $submenu_page_title = 'Your 2FA Channel';

    /**
     * @var string
     */
    private $submenu_title = 'Your 2FA Channel';

    /**
     * @var string
     */
    private $submenu_id = 'twofas-submenu-channel';

    /**
     * @param TwoFAS_Menu $menu
     * @param string      $capability
     * @param TwoFAS_App  $app
     */
    public function __construct(TwoFAS_Menu $menu, $capability, TwoFAS_App $app)
    {
        parent::__construct(
            $menu,
            $this->submenu_page_title,
            $this->submenu_title,
            $capability,
            $this->submenu_id,
            $app
        );
    }

    public function render()
    {
        echo $this->app->get_action()->get_viewable_result();
    }
}
