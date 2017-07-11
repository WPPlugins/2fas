<?php

namespace TwoFAS\Channels;

use TwoFAS\Api\Exception\Exception;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Storage\TwoFAS_Storage;

abstract class TwoFAS_Channel
{
    /**
     * @var TwoFAS
     */
    protected $twofas;

    /**
     * @var TwoFAS_Storage
     */
    protected $storage;

    /**
     * @var IntegrationUser
     */
    protected $integration_user;

    /**
     * @param TwoFAS          $twofas
     * @param TwoFAS_Storage  $storage
     * @param IntegrationUser $integration_user
     */
    public function __construct(TwoFAS $twofas, TwoFAS_Storage $storage, IntegrationUser $integration_user)
    {
        $this->twofas           = $twofas;
        $this->storage          = $storage;
        $this->integration_user = $integration_user;
    }

    /**
     * @return string
     */
    public function get_channel_message()
    {
        return '';
    }
    
    /**
     * @return string
     */
    public function get_resend_code_message()
    {
        return '';
    }
    
    public function is_totp()
    {
        return false;
    }

    /**
     * @return bool
     */
    public abstract function is_premium();

    /**
     * @return string
     */
    public abstract function get_request_for_code_message();

    /**
     * @throws Exception
     */
    public abstract function open_authentication();

    public function close_authentication()
    {
        $this->storage->get_userdata()->set_user_id($this->integration_user->getExternalId());
        $this->storage->get_userdata()->reset_authentication();
        $this->storage->get_userdata()->set_authentication_id(array());
    }

    /**
     * @return string
     */
    public function get_phone_number()
    {
        $phone_number = $this->integration_user->getPhoneNumber()->phoneNumber();

        if ($phone_number) {
            return $phone_number;
        }

        return '';
    }

    /**
     * @return array|null
     */
    protected function get_authentication_ids()
    {
        $this->storage->get_userdata()->set_user_id($this->integration_user->getExternalId());

        $authentication_ids = $this->storage->get_userdata()->get_authentication_id();

        if (!is_array($authentication_ids) && !is_null($authentication_ids)) {
            $authentication_ids = array();
        }

        return $authentication_ids;
    }

    /**
     * @param array $authentication_ids
     * @param int   $lifetime
     */
    protected function save_authentication(array $authentication_ids, $lifetime)
    {
        $this->storage->get_userdata()->set_user_id($this->integration_user->getExternalId());
        $this->storage->get_userdata()->open_authentication();
        $this->storage->get_userdata()->set_authentication_id($authentication_ids);
        $this->storage->get_userdata()->save_authentication_valid_until(time() + $lifetime);
    }
}
