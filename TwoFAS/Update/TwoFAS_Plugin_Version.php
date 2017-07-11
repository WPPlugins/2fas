<?php

namespace TwoFAS\Update;

use TwoFAS\Storage\TwoFAS_WP_DB_Options_Storage;

class TwoFAS_Plugin_Version
{
    /**
     * @var TwoFAS_WP_DB_Options_Storage
     */
    private $twofas_options;

    /**
     * @param TwoFAS_WP_DB_Options_Storage $twofas_options
     */
    public function __construct(TwoFAS_WP_DB_Options_Storage $twofas_options)
    {
        $this->twofas_options = $twofas_options;
    }

    /**
     * @return string|null
     */
    public function get_database_version()
    {
        $version = $this->twofas_options->get_twofas_plugin_version();

        if ($version) {
            return $version;
        }

        if ($this->is_fresh_install()) {
            return null;
        }

        return '1.0.0';
    }

    /**
     * @return string
     */
    public function get_plugin_version()
    {
        return TWOFAS_PLUGIN_VERSION;
    }

    public function update_version()
    {
        $this->twofas_options->set_twofas_plugin_version($this->get_plugin_version());
    }

    /**
     * @return bool
     */
    private function is_fresh_install()
    {
        $required_options = array(
            TwoFAS_WP_DB_Options_Storage::TWOFAS_EMAIL,
            TwoFAS_WP_DB_Options_Storage::TWOFAS_PASSWORD,
            TwoFAS_WP_DB_Options_Storage::TWOFAS_INTEGRATION_LOGIN,
            TwoFAS_WP_DB_Options_Storage::TWOFAS_KEY_TOKEN,
            TwoFAS_WP_DB_Options_Storage::TWOFAS_ENCRYPTION_KEY,
            TwoFAS_WP_DB_Options_Storage::TWOFAS_ENABLED,
        );

        foreach ($required_options as $option) {
            if (get_option($option)) {
                return false;
            }
        }

        return true;
    }
}
