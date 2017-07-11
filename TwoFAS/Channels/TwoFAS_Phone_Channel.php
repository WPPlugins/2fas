<?php

namespace TwoFAS\Channels;

use TwoFAS\Api\Exception\Exception as Api_Exception;
use TwoFAS\Api\Authentication;

abstract class TwoFAS_Phone_Channel extends TwoFAS_Channel
{
    const AUTHENTICATION_LIFETIME = 900; // in seconds
    const AUTHENTICATION_LIMIT    = 5;

    /**
     * @param string $phone_number
     *
     * @return Authentication
     *
     * @throws Api_Exception
     */
    public abstract function request_authentication($phone_number);

    /**
     * @return bool
     */
    public function is_premium()
    {
        return true;
    }
    
    /**
     * @throws TwoFAS_Authentication_Limit_Exceeded_Exception
     * @throws Api_Exception
     */
    public function open_authentication()
    {
        $authentication_ids = $this->get_authentication_ids();

        if (count($authentication_ids) === self::AUTHENTICATION_LIMIT) {
            throw new TwoFAS_Authentication_Limit_Exceeded_Exception;
        }

        $phone_number         = $this->integration_user->getPhoneNumber();
        $authentication       = $this->request_authentication($phone_number->phoneNumber());
        $authentication_ids[] = $authentication->id();

        $this->save_authentication($authentication_ids, self::AUTHENTICATION_LIFETIME);
    }
}
