<?php

namespace TwoFAS\Notifications;

use TwoFAS\Roles\TwoFAS_Role;
use TwoFAS\Storage\TwoFAS_WP_DB_Options_Storage;
use TwoFAS\Actions\TwoFAS_Action_Map;
use TwoFAS\Templates\TwoFAS_Template;

class TwoFAS_Admin_Notification
{
    /**
     * @param string $message
     */
    public static function set_admin_error_notice($message)
    {
        add_action('admin_notices', create_function('', "echo '<div class=\"error\"><p>" . $message . "</p></div>';"));
    }

    /**
     * @param TwoFAS_WP_DB_Options_Storage $options_storage
     */
    public static function display_no_2fas_account_admin_notice(TwoFAS_WP_DB_Options_Storage $options_storage)
    {
        $page_is_admin_submenu = false;
        $submenu_admin         = 'twofas-submenu-admin';
        
        if (isset($_GET['page'])) {
            $page_is_admin_submenu = $_GET['page'] === $submenu_admin;
        }
        
        if (!$options_storage->get_twofas_email()
            && TwoFAS_Role::user_has_admin_capability()
            && !$page_is_admin_submenu
        ) {
            $message = 'Please click '. TwoFAS_Template::generate_link_to_action(TwoFAS_Action_Map::TWOFAS_ACTION_CREATE_ACCOUNT, 'here', $submenu_admin).' to go to the <strong>2FAS Admin</strong>';
            self::set_admin_error_notice($message);
        }
    }
}
