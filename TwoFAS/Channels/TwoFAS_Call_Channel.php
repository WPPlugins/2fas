<?php

namespace TwoFAS\Channels;

class TwoFAS_Call_Channel extends TwoFAS_Phone_Channel
{
    /**
     * @return string
     */
    public function get_channel_message()
    {
        return 'Please wait for our call with your code at:';
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
        return 'Call me again';
    }

    public function request_authentication($phone_number)
    {
        return $this->twofas->requestAuthViaCall($phone_number);
    }
}
