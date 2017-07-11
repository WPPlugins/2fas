<?php

namespace TwoFAS\Update;

use TwoFAS\Update\Migrations\TwoFAS_Generate_Oauth_Tokens;
use TwoFAS\Update\Migrations\TwoFAS_Update_Encryption_Key;

class TwoFAS_Updater
{
    /**
     * @var TwoFAS_Plugin_Version
     */
    private $plugin_version;

    /**
     * @var TwoFAS_Update_Encryption_Key
     */
    private $update_encryption_key;

    /**
     * @var TwoFAS_Generate_Oauth_Tokens
     */
    private $generate_oauth_tokens;

    /**
     * @param TwoFAS_Plugin_Version        $plugin_version
     * @param TwoFAS_Update_Encryption_Key $update_encryption_key
     * @param TwoFAS_Generate_Oauth_Tokens $generate_oauth_tokens
     */
    public function __construct(
        TwoFAS_Plugin_Version $plugin_version,
        TwoFAS_Update_Encryption_Key $update_encryption_key,
        TwoFAS_Generate_Oauth_Tokens $generate_oauth_tokens
    ) {
        $this->plugin_version        = $plugin_version;
        $this->update_encryption_key = $update_encryption_key;
        $this->generate_oauth_tokens = $generate_oauth_tokens;
    }

    public function update_plugin()
    {
        // Get database version of the plugin and current version from constant
        $database_version = $this->plugin_version->get_database_version();
        $plugin_version   = $this->plugin_version->get_plugin_version();

        // If this is a fresh installation, update plugin version in WordPress database
        if (is_null($database_version)) {
            $this->plugin_version->update_version();
            return false;
        }

        // Don't update if database version of the plugin equals to current version
        if (version_compare($database_version, $plugin_version, '=')) {
            return false;
        }

        // If database version is 1.0.0, update encryption key
        if (version_compare($database_version, '1.0.0', '=')) {
            $this->update_encryption_key->run();
        }

        // If database version is less than 1.2.0, generate OAuth tokens and delete 2FAS password
        if (version_compare($database_version, '1.2.0', '<')) {
            $this->generate_oauth_tokens->run();
        }

        $this->plugin_version->update_version();

        return true;
    }
}
