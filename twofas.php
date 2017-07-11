<?php
/*
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Plugin Name:       2FAS - Two Factor Authentication Service
 * Plugin URI:        https://wordpress.org/plugins/2fas
 * Description:       2FAS strengthens WordPress admin security by requiring an additional verification code on untrusted devices.
 * Version:           1.2.2
 * Author:            Two Factor Authentication Service Inc.
 * Author URI:        http://2fas.com
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       twofas
 */

defined('ABSPATH') or die();

register_activation_hook( __FILE__, 'twofas_init' );
add_action('init', 'twofas_init');

function twofas_init() {
    if (version_compare( PHP_VERSION, '5.3', '<' )) {
        add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>".__('2FAS plugin requires PHP 5.3 to function properly. Please upgrade PHP or deactivate 2FAS plugin. After PHP upgrade please deactivate and activate 2FAS plugin again', 'twofas') ."</p></div>';" ) );
        return;
    } else if (!extension_loaded('curl')) {
        add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>".__('2FAS plugin requires cURL extension loaded to function properly. Please install cURL extension or deactivate 2FAS plugin. After cURL installation please deactivate and activate 2FAS plugin again', 'twofas') ."</p></div>';" ) );
        return;
    } else {
        include 'TwoFAS_Init.php';

	    $init = new TwoFAS_Init();
	    $init->start();
    }
}
