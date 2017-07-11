<?php

namespace TwoFAS\Update\Migrations;

use TwoFAS\Api\TwoFAS;
use TwoFAS\Encryption\AESKey;
use TwoFAS\Api\Exception\Exception as Api_Exception;
use TwoFAS\Encryption\WP_Empty_Key_Storage;
use TwoFAS\Storage\TwoFAS_Storage;

class TwoFAS_Update_Encryption_Key
{
    /**
     * @var TwoFAS_Storage
     */
    private $storage;

    /**
     * @var TwoFAS
     */
    private $twofas;

    /**
     * @param TwoFAS_Storage $storage
     * @param TwoFAS         $twofas
     */
    public function __construct(TwoFAS_Storage $storage, TwoFAS $twofas)
    {
        $this->storage = $storage;
        $this->twofas  = $twofas;
    }

    /**
     * @return bool
     */
    public function run()
    {
        if ($this->storage->get_options()->get_twofas_encryption_key()) {
            return false;
        }

        $key               = new AESKey;
        $options_storage   = $this->storage->get_options();
        $empty_key_storage = new WP_Empty_Key_Storage;
        $users             = get_users();
        $result            = true;

        $options_storage->storeKey($key);

        foreach ($users as $user) {
            try {
                $integration_user = $this->twofas->getIntegrationUserByExternalId($empty_key_storage, $user->ID);
                $this->twofas->updateIntegrationUser($options_storage, $integration_user);
            } catch (Api_Exception $e) {
                $result = false;
                continue;
            }
        }

        return $result;
    }
}
