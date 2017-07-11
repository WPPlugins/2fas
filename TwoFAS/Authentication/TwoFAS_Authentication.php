<?php

namespace TwoFAS\Authentication;

use TwoFAS\Api\Authentication;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\TwoFAS;
use TwoFAS\User\TwoFAS_User;

class TwoFAS_Authentication
{
    /**
     * @var TwoFAS_Authentication
     */
    private static $instance;

    /**
     * @var TwoFAS
     */
    private $api;

    /**
     * @param TwoFAS $api
     */
    private function __construct(TwoFAS $api) 
    {
        $this->api = $api;   
    }

    /**
     * @param TwoFAS $api
     *
     * @return TwoFAS_Authentication
     */
    public static function get_instance(TwoFAS $api)
    {
        if (!self::$instance) {
            self::$instance = new TwoFAS_Authentication($api);
        }
        
        return self::$instance;
    }

    /**
     * @param TwoFAS_User $user
     *
     * @return null|string
     *
     * @throws \TwoFAS\Api\Exception\Exception
     */
    public function create(TwoFAS_User $user)
    {
        $auth_method       = $user->get_active_method();
        $authentication_id = null;

        if ($auth_method === 'totp') {
            $authentication_id = $this->api->requestAuthViaTotp($user->get_totp_secret())->id();
        }
        
        if ($auth_method === 'sms') {
            $authentication_id = $this->api->requestAuthViaSms($user->get_phone_number())->id();
        }
        
        if ($auth_method === 'call') {
            $authentication_id = $this->api->requestAuthViaCall($user->get_phone_number())->id();
        }
        
        return $authentication_id;
    }

    /**
     * @param string $authentication_id
     * @param string $code
     *
     * @return \TwoFAS\Api\Code\Code
     *
     * @throws \TwoFAS\Api\Exception\Exception
     */
    public function validate($authentication_id, $code)
    {
        $authentications = new AuthenticationCollection();
        $authentications->add(new Authentication($authentication_id));

        $result = $this->api->checkCode($authentications, $code);
        return $result;
    }
}
