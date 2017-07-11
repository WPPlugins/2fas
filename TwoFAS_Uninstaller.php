<?php

class TwoFAS_Uninstaller
{
    /**
     * @var array
     */
    private $twofas_options = array(
        'twofas_login',
        'twofas_key',
        'twofas_encryption_key',
        'twofas_email',
        'twofas_password',
        'twofas_plugin_version',
        'twofas_enabled',
        'twofas_integration_id',
        'twofas_oauth_token_setup',
        'twofas_oauth_token_wordpress',
    );

    public function uninstall()
    {
        $this->clear_wp_options();
        $this->clear_wp_usermeta();
        $this->delete_cookies();
    }

    /**
     * @param string $except
     */
    public function uninstall_except($except)
    {
        $this->clear_wp_options($except);
        $this->clear_wp_usermeta();
        $this->delete_cookies();
    }

    /**
     * @param string $except
     */
    private function clear_wp_options($except = null)
    {
        foreach ($this->twofas_options as $option_name) {
            if ($except !== $option_name) {
                delete_option($option_name);
            }
        }
    }

    private function clear_wp_usermeta()
    {
        global $wpdb;

        $table_name = $wpdb->usermeta;

        $wpdb->query("DELETE FROM {$table_name} WHERE meta_key LIKE 'twofas\_%'");
    }

    private function delete_cookies()
    {
        $cookies = $this->get_cookies();

        foreach ($cookies as $cookie) {
            foreach ($_COOKIE as $cookie_name => $cookie_value) {
                if (strpos($cookie_name, $cookie) !== false) {
                    setcookie($cookie_name, '', time() - 3600, '/');
                }
            }
        }
    }

    /**
     * @return array
     */
    private function get_cookies()
    {
        return array(
            'twofas_step_token',
            'twofas_remember_me',
            'twofas_trusted_device',
        );
    }
}
