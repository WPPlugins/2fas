<?php

namespace TwoFAS\Storage;

class TwoFAS_Cookie_Storage
{
    const TWOFAS_STEP_TOKEN              = 'twofas_step_token';
    const TWOFAS_REMEMBER_MACHINE_PREFIX = 'twofas_trusted_device';

    /**
     * @return null|string
     */
    public function get_step_token()
    {
        return $this->get_cookie_value(self::TWOFAS_STEP_TOKEN);
    }

    /**
     * @param  string $token
     * @return bool
     */
    public function set_step_token($token)
    {
        return setcookie(self::TWOFAS_STEP_TOKEN, $token, time() + 3600, '/');
    }

    /**
     * @return bool
     */
    public function unset_step_token()
    {
        return setcookie(self::TWOFAS_STEP_TOKEN, '', time() - 3600, '/');
    }

    /**
     * @param  string $cookie_name
     * @return bool
     */
    public function unset_cookie($cookie_name)
    {
        return setcookie($cookie_name, '', time() - 3600, '/');
    }

    /**
     * @param  string $cookie_value
     * @return string
     */
    public function set_trusted_device_cookie($cookie_value)
    {
        $name = self::TWOFAS_REMEMBER_MACHINE_PREFIX . '_' . md5(uniqid('', true));
        setcookie($name, $cookie_value, 2147483647, '/');
        return $name;
    }
    
    /**
     * @param  string $name
     * @return bool
     */
    private function cookie_exists($name)
    {
        if (isset($_COOKIE[$name])) {
            return true;
        }

        return false;
    }

    /**
     * @param  string $name
     * @return null|string
     */
    public function get_cookie_value($name)
    {
        if ($this->cookie_exists($name)) {
            return $_COOKIE[$name];
        }

        return null;
    }
}   
