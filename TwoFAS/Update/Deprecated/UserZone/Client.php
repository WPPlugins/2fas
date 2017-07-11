<?php

namespace TwoFAS\Update\Deprecated\UserZone;

final class Client
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var bool
     */
    private $hasCard;

    /**
     * @var bool
     */
    private $hasGeneratedPassword;

    /**
     * @param int $id
     *
     * @return Client
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @param string $email
     *
     * @return Client
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return boolean
     */
    public function hasCard()
    {
        return $this->hasCard;
    }

    /**
     * @param boolean $hasCard
     *
     * @return $this
     */
    public function setHasCard($hasCard)
    {
        $this->hasCard = (bool) $hasCard;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasGeneratedPassword()
    {
        return $this->hasGeneratedPassword;
    }

    /**
     * @param boolean $hasGeneratedPassword
     *
     * @return $this
     */
    public function setHasGeneratedPassword($hasGeneratedPassword)
    {
        $this->hasGeneratedPassword = (bool) $hasGeneratedPassword;
        return $this;
    }
}
