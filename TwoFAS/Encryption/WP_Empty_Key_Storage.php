<?php

namespace TwoFAS\Encryption;

use TwoFAS\Encryption\Interfaces\Key;
use TwoFAS\Encryption\Interfaces\KeyStorage;

class WP_Empty_Key_Storage implements KeyStorage
{
    /**
     * @param Key $key
     */
    public function storeKey(Key $key)
    {
    }

    /**
     * @return string
     */
    public function retrieveKeyValue()
    {
        return '';
    }
}
