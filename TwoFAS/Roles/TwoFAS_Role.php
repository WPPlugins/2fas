<?php

namespace TwoFAS\Roles;

class TwoFAS_Role
{
    const TWOFAS_ADMIN = 'level_10';
    const TWOFAS_USER  = 'level_0';

    /**
     * @return bool
     */
    public static function user_has_admin_capability() 
    {
        return current_user_can(self::TWOFAS_ADMIN);
    }

    /**
     * @return bool
     */
    public static function user_has_user_capability()
    {
        return current_user_can(self::TWOFAS_USER);
    }
}
