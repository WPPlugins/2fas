<?php

namespace TwoFAS\Channels;

use TwoFAS\UserZone\Client;
use TwoFAS\UserZone\Integration;

class TwoFAS_Authentication_Channels
{
    const CHANNEL_STATUS_ENABLED  = 'ENABLED';
    const CHANNEL_STATUS_DISABLED = 'DISABLED';
    const CHANNEL_TOTP            = 'totp';
    const CHANNEL_SMS             = 'sms';
    const CHANNEL_CALL            = 'call';

    /**
     * @var Integration
     */
    private $integration;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Integration $integration
     * @param Client      $client
     */
    public function __construct(Integration $integration, Client $client)
    {
        $this->integration = $integration;
        $this->client      = $client;
    }

    /**
     * @return string
     */
    public function get_totp_status()
    {
        return $this->get_channel_status(self::CHANNEL_TOTP);
    }

    /**
     * @return string
     */
    public function get_sms_status()
    {
        return $this->get_channel_status(self::CHANNEL_SMS);
    }

    /**
     * @return string
     */
    public function get_call_status()
    {
        return $this->get_channel_status(self::CHANNEL_CALL);
    }

    /**
     * @return bool
     */
    public function is_totp_enabled()
    {
        return $this->get_totp_status() === self::CHANNEL_STATUS_ENABLED;
    }

    /**
     * @return bool
     */
    public function is_sms_enabled()
    {
        return $this->get_sms_status() === self::CHANNEL_STATUS_ENABLED;
    }

    /**
     * @return bool
     */
    public function is_call_enabled()
    {
        return $this->get_call_status() === self::CHANNEL_STATUS_ENABLED;
    }

    /**
     * @return Integration
     */
    public function enable_totp()
    {
        return $this->enable_channel(self::CHANNEL_TOTP);
    }

    /**
     * @return Integration
     */
    public function enable_sms()
    {
        return $this->enable_channel(self::CHANNEL_SMS);
    }

    /**
     * @return Integration
     */
    public function enable_call()
    {
        return $this->enable_channel(self::CHANNEL_CALL);
    }

    /**
     * @return Integration
     */
    public function disable_totp()
    {
        return $this->disable_channel(self::CHANNEL_TOTP);
    }

    /**
     * @return Integration
     */
    public function force_disable_totp()
    {
        return $this->force_disable_channel(self::CHANNEL_TOTP);
    }

    /**
     * @return Integration
     */
    public function disable_sms()
    {
        return $this->disable_channel(self::CHANNEL_SMS);
    }

    /**
     * @return Integration
     */
    public function force_disable_sms()
    {
        return $this->force_disable_channel(self::CHANNEL_SMS);
    }

    /**
     * @return Integration
     */
    public function disable_call()
    {
        return $this->disable_channel(self::CHANNEL_CALL);
    }

    /**
     * @return Integration
     */
    public function force_disable_call()
    {
        return $this->force_disable_channel(self::CHANNEL_CALL);
    }

    /**
     * @return bool
     */
    public function client_has_custom_password()
    {
        return !$this->client->hasGeneratedPassword();
    }

    /**
     * @return bool
     */
    public function client_has_card()
    {
        return $this->client->hasCard();
    }

    /**
     * @param string $channel
     *
     * @return Integration
     */
    private function enable_channel($channel)
    {
        if (!$this->integration->getChannel($channel)) {
            $this->integration->enableChannel($channel);
        }

        return $this->integration;
    }

    /**
     * @param string $channel
     *
     * @return Integration
     */
    private function disable_channel($channel)
    {
        if ($this->integration->getChannel($channel)) {
            $this->integration->disableChannel($channel);
        }

        return $this->integration;
    }

    /**
     * @param string $channel
     *
     * @return Integration
     */
    private function force_disable_channel($channel)
    {
        if ($this->integration->getChannel($channel)) {
            $this->integration->forceDisableChannel($channel);
        }

        return $this->integration;
    }

    /**
     * @param string $channel
     *
     * @return string
     */
    private function get_channel_status($channel)
    {
        return $this->integration->getChannel($channel) ? self::CHANNEL_STATUS_ENABLED : self::CHANNEL_STATUS_DISABLED;
    }
}
