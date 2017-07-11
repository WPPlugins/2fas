<?php

namespace TwoFAS\Admin\UI\Submenus;

use TwoFAS\TwoFAS_App;
use TwoFAS\Admin\TwoFAS_Menu;

class TwoFAS_Submenu_Admin extends TwoFAS_Submenu
{
    /**
     * @var string
     */
    private $submenu_page_title = '2FAS Admin';

    /**
     * @var string
     */
    private $submenu_title = '2FAS Admin';

    /**
     * @var string
     */
    private $submenu_id = 'twofas-submenu-admin';

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
