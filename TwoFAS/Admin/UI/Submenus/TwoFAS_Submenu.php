<?php

namespace TwoFAS\Admin\UI\Submenus;

use TwoFAS\TwoFAS_App;
use TwoFAS\Admin\TwoFAS_Menu;

abstract class TwoFAS_Submenu
{
    /**
     * @var string
     */
    private $menu_id;

    /**
     * @var string
     */
    private $submenu_id;

    /**
     * @var string
     */
    private $submenu_page_title;

    /**
     * @var string
     */
    private $submenu_title;

    /**
     * @var string
     */
    private $capability;

    /**
     * @var TwoFAS_App
     */
    protected $app;

    /**
     * @param TwoFAS_Menu $menu
     * @param string      $submenu_page_title
     * @param string      $submenu_title
     * @param string      $capability
     * @param string      $submenu_id
     * @param TwoFAS_App  $app
     */
    public function __construct(
        TwoFAS_Menu $menu,
        $submenu_page_title,
        $submenu_title,
        $capability,
        $submenu_id,
        TwoFAS_App $app
    ) {
        $this->menu_id            = $menu->getMenuId();
        $this->submenu_page_title = $submenu_page_title;
        $this->submenu_title      = $submenu_title;
        $this->capability         = $capability;
        $this->submenu_id         = $submenu_id;
        $this->app                = $app;
    }

    public function init()
    {
        add_submenu_page(
            $this->menu_id,
            $this->submenu_page_title,
            $this->submenu_title,
            $this->capability,
            $this->submenu_id,
            array($this, 'render')
        );
    }

    /**
     * @return string
     */
    public function getMenuId()
    {
        return $this->submenu_id;
    }

    public function render() { }
}
