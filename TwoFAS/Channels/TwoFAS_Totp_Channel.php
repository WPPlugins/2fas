<?php

namespace TwoFAS\Channels;

use TwoFAS\Api\Exception\Exception;

class TwoFAS_Totp_Channel extends TwoFAS_Channel
{
    const AUTHENTICATION_LIFETIME = 120; // in seconds

    /**
     * @return bool
     */
    public function is_premium()
    {
        return false;
    }

    public function is_totp()
    {
        return true;
    }

    /**
     * @return string
     */
    public function get_request_for_code_message()
    {
        return 'token';
    }

    /**
     * @throws Exception
     */
    public function open_authentication()
    {
        $secret             = $this->integration_user->getTotpSecret();
        $authentication     = $this->twofas->requestAuthViaTotp($secret);
        $authentication_ids = array($authentication->id());

        $this->save_authentication($authentication_ids, self::AUTHENTICATION_LIFETIME);
    }
}
