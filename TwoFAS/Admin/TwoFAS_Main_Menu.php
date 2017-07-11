<?php

namespace TwoFAS\Admin;

use TwoFAS\TwoFAS_App;
use TwoFAS\Roles\TwoFAS_Role;
use TwoFAS\Admin\UI\Submenus\TwoFAS_Submenu_Admin;
use TwoFAS\Admin\UI\Submenus\TwoFAS_Submenu_Channel;

class TwoFAS_Main_Menu implements TwoFAS_Menu
{
    /**
     * @var string
     */
    private $menu_id = 'twofas-submenu-channel';

    /**
     * @var string
     */
    private $menu_name = '2FAS';

    /**
     * @var string
     */
    private $menu_title = '2FAS';

    /**
     * @var string
     */
    private $capability = TwoFAS_Role::TWOFAS_USER;

    /**
     * @var TwoFAS_App
     */
    private $app;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var TwoFAS_Submenu_Admin
     */
    private $submenu_main;

    /**
     * @var TwoFAS_Submenu_Channel
     */
    private $submenu_channel;

    /**
     * @param TwoFAS_App $app
     */
    public function __construct(TwoFAS_App $app)
    {
        $this->icon = 'none';
        $this->app  = $app;
        
        $this->submenu_main = new TwoFAS_Submenu_Admin(
            $this,
            TwoFAS_Role::TWOFAS_ADMIN,
            $app
        );

        $this->submenu_channel = new TwoFAS_Submenu_Channel(
            $this,
            TwoFAS_Role::TWOFAS_USER,
            $app
        );
        
        add_action('admin_menu', array($this, 'init_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_filter('custom_menu_order', function($menu_ord) {
            global $submenu;
            
            if (!isset($submenu['twofas-submenu-channel'])) {
                return $menu_ord;
            }
            
            $arr   = array();
            $arr[] = $submenu['twofas-submenu-channel'][1];
            $arr[] = $submenu['twofas-submenu-channel'][0];

            $submenu['twofas-submenu-channel'] = $arr;
            
            return $menu_ord;
        });
    }

    public function init_admin_menu()
    {
        add_menu_page(
            $this->menu_title,
            $this->menu_name,
            $this->capability,
            $this->menu_id,
            null,
            $this->icon
        );

        $this->submenu_channel->init();
        $this->submenu_main->init();
    }

    public function init_settings() { }

    /**
     * @return string
     */
    public function getMenuId()
    {
        return $this->menu_id;
    }
}
