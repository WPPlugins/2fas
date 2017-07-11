<?php

namespace TwoFAS\Storage;

use TwoFAS\Encryption\AESKey;
use TwoFAS\Encryption\Interfaces\Key;
use TwoFAS\Encryption\Interfaces\KeyStorage;

class TwoFAS_WP_DB_Options_Storage implements KeyStorage
{
    const TWOFAS_INTEGRATION_LOGIN = 'twofas_login';
    const TWOFAS_KEY_TOKEN         = 'twofas_key';
    const TWOFAS_ENABLED           = 'twofas_enabled';
    const TWOFAS_ENCRYPTION_KEY    = 'twofas_encryption_key';
    const TWOFAS_EMAIL             = 'twofas_email';
    const TWOFAS_PASSWORD          = 'twofas_password';
    const TWOFAS_MIGRATIONS        = 'twofas_migrations';
    const TWOFAS_PLUGIN_VERSION    = 'twofas_plugin_version';

    /**
     * @return string|null
     */
    public function get_twofas_email()
    {
        return get_option(self::TWOFAS_EMAIL, null);
    }

    /**
     * @return string|null
     */
    public function get_twofas_password()
    {
        return get_option(self::TWOFAS_PASSWORD, null);
    }

    /**
     * @param string $email
     */
    public function set_twofas_email($email)
    {
        update_option(self::TWOFAS_EMAIL, $email);
    }

    public function delete_twofas_password()
    {
        delete_option(self::TWOFAS_PASSWORD);
    }

    public function delete_twofas_enabled()
    {
        delete_option(self::TWOFAS_ENABLED);
    }

    /**
     * @return string|null
     */
    public function get_twofas_integration_login()
    {
        return get_option(self::TWOFAS_INTEGRATION_LOGIN, null);
    }

    /**
     * @param string $value
     */
    public function set_twofas_integration_login($value)
    {
        update_option(self::TWOFAS_INTEGRATION_LOGIN, $value);
    }

    /**
     * @return string|null
     */
    public function get_twofas_key_token()
    {
        return get_option(self::TWOFAS_KEY_TOKEN, null);
    }

    /**
     * @param string $value
     */
    public function set_twofas_key_token($value)
    {
        update_option(self::TWOFAS_KEY_TOKEN, $value);
    }

    /**
     * @return string|false
     */
    public function get_twofas_encryption_key()
    {
        return get_option(self::TWOFAS_ENCRYPTION_KEY);
    }

    /**
     * @param string $value
     */
    public function set_twofas_encryption_key($value)
    {
        update_option(self::TWOFAS_ENCRYPTION_KEY, $value);
    }

    /**
     * @return string|false
     */
    public function get_twofas_plugin_version()
    {
        return get_option(self::TWOFAS_PLUGIN_VERSION);
    }

    /**
     * @param string $version
     */
    public function set_twofas_plugin_version($version)
    {
        update_option(self::TWOFAS_PLUGIN_VERSION, $version);
    }

    /**
     * @param Key $key
     */
    public function storeKey(Key $key)
    {
        $this->set_twofas_encryption_key(base64_encode($key->getValue()));
    }

    public function save_aes_key()
    {
        $this->storeKey(new AESKey);
    }

    /**
     * @return string
     */
    public function retrieveKeyValue()
    {
        return base64_decode($this->get_twofas_encryption_key());
    }
}
