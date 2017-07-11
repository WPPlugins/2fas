<?php

namespace TwoFAS\Channels;

use InvalidArgumentException;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Storage\TwoFAS_Storage;
use TwoFAS\Api\Methods;

class TwoFAS_Channel_Factory
{
    /**
     * @var TwoFAS
     */
    private $twofas;

    /**
     * @var TwoFAS_Storage
     */
    private $storage;

    /**
     * @param TwoFAS         $twofas
     * @param TwoFAS_Storage $storage
     */
    public function __construct(TwoFAS $twofas, TwoFAS_Storage $storage)
    {
        $this->twofas  = $twofas;
        $this->storage = $storage;
    }

    /**
     * @param IntegrationUser $integration_user
     *
     * @return TwoFAS_Channel
     *
     * @throws InvalidArgumentException
     */
    public function create(IntegrationUser $integration_user)
    {
        $active_method = $integration_user->getActiveMethod();

        switch ($active_method) {
            case Methods::TOTP:
                return new TwoFAS_Totp_Channel($this->twofas, $this->storage, $integration_user);
            case Methods::SMS:
                return new TwoFAS_Sms_Channel($this->twofas, $this->storage, $integration_user);
            case Methods::CALL:
                return new TwoFAS_Call_Channel($this->twofas, $this->storage, $integration_user);
        }

        throw new InvalidArgumentException;
    }
}
