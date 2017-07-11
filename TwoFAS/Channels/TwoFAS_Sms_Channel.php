<?php

namespace TwoFAS\Channels;

use TwoFAS\Api\Authentication;

class TwoFAS_Sms_Channel extends TwoFAS_Phone_Channel
{
    /**
     * @return string
     */
    public function get_channel_message()
    {
        return 'Your code has been sent to:';
    }

    /**
     * @return string
     */
    public function get_request_for_code_message()
    {
        return 'code';
    }

    /**
     * @return string
     */
    public function get_resend_code_message()
    {
        return 'Resend my code';
    }

    /**
     * @param string $phone_number
     *
     * @return Authentication
     */
    public function request_authentication($phone_number)
    {
        return $this->twofas->requestAuthViaSms($phone_number);
    }
}
