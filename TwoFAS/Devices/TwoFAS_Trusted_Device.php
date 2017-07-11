<?php

namespace TwoFAS\Devices;

use TwoFAS\Storage\TwoFAS_Storage;

class TwoFAS_Trusted_Device
{
    /**
     * @param TwoFAS_Storage $storage
     *
     * @return bool
     */
    public static function is_device_trusted(TwoFAS_Storage $storage)
    {
        $trusted_devices = $storage->get_userdata()->get_trusted_devices();

        if (is_null($trusted_devices)) {
            return false;
        }

        foreach ($trusted_devices as $cookie_name => $device_data) {
            $cookie_value = $storage->get_cookie_storage()->get_cookie_value($cookie_name);

            if ($cookie_value === $device_data['cookie_value']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TwoFAS_Storage $storage
     */
    public static function add_trusted_device(TwoFAS_Storage $storage)
    {
        $cookie_value    = md5(uniqid('', true));
        $cookie_name     = $storage->get_cookie_storage()->set_trusted_device_cookie($cookie_value);
        $ip              = $_SERVER['REMOTE_ADDR'];
        $trusted_devices = $storage->get_userdata()->get_trusted_devices();

        if (is_null($trusted_devices)) {
            $trusted_devices = array();
        }

        $cookie_names[] = $cookie_name;
        $time           = time();

        $trusted_devices[$cookie_name] = array(
            'cookie_value' => $cookie_value,
            'IP'          => $ip,
            'time'        => $time,
            'cookie_name' => $cookie_name,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT']
        );

        $storage->get_userdata()->set_trusted_devices($trusted_devices);
    }

    /**
     * @param TwoFAS_Storage $storage
     * @param                $device_id
     *
     * @return bool
     */
    public static function remove_trusted_device(TwoFAS_Storage $storage, $device_id)
    {
        $devices   = $storage->get_userdata()->get_trusted_devices();

        if (!isset($devices[$device_id])) {
            return false;
        }

        $cookie_to_be_removed = $device_id;
        $storage->get_cookie_storage()->unset_cookie($cookie_to_be_removed);
        
        
        if (isset($_COOKIE[$cookie_to_be_removed])) {
            setcookie($cookie_to_be_removed, '', time() - 3600, '/');
        }

        unset($devices[$device_id]);
        $storage->get_userdata()->set_trusted_devices($devices);
        
        return true;
    }
    
}
