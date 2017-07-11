<?php

namespace TwoFAS\Update\Deprecated\UserZone;

final class Integration
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $channels = array(
        'sms'   => null,
        'call'  => null,
        'email' => null,
        'totp'  => null
    );

    /**
     * @param int $id
     *
     * @return Integration
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * @param string $login
     *
     * @return Integration
     */
    public function setLogin($login)
    {
        $this->login = (string) $login;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return Integration
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @param array $channels
     *
     * @return Integration
     */
    public function setChannels(array $channels)
    {
        foreach ($channels as $name => $value) {
            if (!$this->hasChannel($name)) {
                throw new \InvalidArgumentException('Invalid channel name');
            }

            $this->channels[$name] = (bool) $value;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function getChannel($name)
    {
        if (!$this->hasChannel($name)) {
            throw new \InvalidArgumentException('Invalid channel name');
        }

        return $this->channels[$name];
    }

    /**
     * @param string $name
     */
    public function enableChannel($name)
    {
        if (!$this->hasChannel($name)) {
            throw new \InvalidArgumentException('Invalid channel name');
        }

        $this->channels[$name] = true;
    }

    /**
     * @param string $name
     */
    public function disableChannel($name)
    {
        if (!$this->hasChannel($name)) {
            throw new \InvalidArgumentException('Invalid channel name');
        }

        $this->channels[$name] = false;
    }

    /**
     * @param string $name
     */
    public function forceDisableChannel($name)
    {
        if (!$this->hasChannel($name)) {
            throw new \InvalidArgumentException('Invalid channel name');
        }

        $this->disableChannel($name);
        $this->channels[$name . '_force_disable'] = true;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(array(
            'id'    => $this->getId(),
            'login' => $this->getLogin(),
            'name'  => $this->getName()
        ),
            $this->getChannelsWithPrefix()
        );
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function hasChannel($name)
    {
        return (array_key_exists($name, $this->channels));
    }

    /**
     * @return array
     */
    private function getChannelsWithPrefix()
    {
        return array_combine(
            array_map(
                function($key) {
                    return 'channel_' . $key;
                },
                array_keys($this->channels)
            ),
            $this->channels
        );
    }
}