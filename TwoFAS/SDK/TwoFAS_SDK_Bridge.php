<?php

namespace TwoFAS\SDK;

use TwoFAS\Api\TwoFAS;
use TwoFAS\UserZone\UserZone;

class TwoFAS_SDK_Bridge
{
    /**
     * @var TwoFAS
     */
    private $twofas;

    /**
     * @var UserZone
     */
    private $user_zone;

    /**
     * @param TwoFAS   $twofas
     * @param UserZone $user_zone
     */
    public function __construct(TwoFAS $twofas, UserZone $user_zone)
    {
        $this->twofas    = $twofas;
        $this->user_zone = $user_zone;
    }

    /**
     * @return TwoFAS
     */
    public function get_api()
    {
        return $this->twofas;
    }

    /**
     * @return UserZone
     */
    public function get_user_zone()
    {
        return $this->user_zone;
    }
}
