<?php

namespace TwoFAS\User;

use TwoFAS\Encryption\Interfaces\KeyStorage;
use TwoFAS\Api\Exception\Exception;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Storage\TwoFAS_WP_Userdata_Storage;

class TwoFAS_User
{
    /**
     * @var IntegrationUser
     */
    private $integration_user;

    /**
     * @var TwoFAS
     */
    private $api;

    /**
     * @var TwoFAS_WP_Userdata_Storage
     */
    private $userdata;

    /**
     * @var KeyStorage
     */
    private $key_storage;

    /**
     * @var bool
     */
    private $is_bind;

    /**
     * TwoFAS_User constructor.
     *
     * @param TwoFAS_WP_Userdata_Storage $userdata
     * @param KeyStorage                 $key_storage
     * @param TwoFAS                     $api
     */
    public function __construct(
        TwoFAS_WP_Userdata_Storage $userdata,
        KeyStorage                 $key_storage,
        TwoFAS                     $api
    ) {
        $this->api         = $api;
        $this->key_storage = $key_storage;
        $this->userdata    = $userdata;
    }

    /**
     *
     */
    private function init_integration_user()
    {
        $this->integration_user = new IntegrationUser();
        $this->integration_user->setExternalId($this->userdata->get_user_id());
        $this->is_bind = false;
    }

    /**
     * @return null|string
     */
    public function get_external_id()
    {
        return $this->integration_user->getExternalId();
    }

    /**
     * @return null|string
     */
    public function get_active_method()
    {
        return $this->integration_user->getActiveMethod();
    }

    /**
     * @return null|string
     */
    public function get_phone_number()
    {
        return $this->integration_user->getPhoneNumber()->phoneNumber();
    }

    /**
     * @return null|string
     */
    public function get_totp_secret()
    {
        return $this->integration_user->getTotpSecret();
    }
    
    /**
     * @param $token
     *
     * @return $this
     */
    public function set_totp_secret($token)
    {
        $this->integration_user->setTotpSecret($token);
        return $this;
    }

    /**
     * @param $phone
     *
     * @return $this
     */
    public function set_phone_number($phone)
    {
        $this->integration_user->setPhoneNumber($phone);
        return $this;
    }

    /**
     * @param $email
     *
     * @return $this
     */
    public function set_email($email)
    {
        $this->integration_user->setEmail($email);
        return $this;
    }

    /**
     * @param $active_method
     *
     * @return $this
     */
    public function set_active_method($active_method)
    {
        $this->integration_user->setActiveMethod($active_method);
        return $this;
    }

    /**
     * @return bool
     */
    public function is_bind()
    {
        return $this->is_bind;
    }

    /**
     * @return $this
     */
    public function fetch_from_2fas()
    {
        try {
            $this->integration_user = $this->api->getIntegrationUserByExternalId($this->key_storage, $this->userdata->get_user_id());
            $this->is_bind          = true;
            return $this;
        } catch (Exception $e) {
            $this->init_integration_user();
            return $this;
        }
    }

    /**
     * @return $this
     * @throws \TwoFAS\Api\Exception\Exception
     */
    public function push_to_2fas()
    {
        if (!$this->is_bind) {
            $this->api->addIntegrationUser($this->key_storage, $this->integration_user);
            $this->is_bind = true;
            return $this;
        }

        $this->api->updateIntegrationUser($this->key_storage, $this->integration_user);
        return $this;
    }
}
