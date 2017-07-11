<?php

namespace TwoFAS\Update\Deprecated\UserZone\Response;

use TwoFAS\Update\Deprecated\UserZone\Errors;
use TwoFAS\Update\Deprecated\UserZone\HttpCodes;
use TwoFAS\Update\Deprecated\UserZone\Exception\Exception;
use TwoFAS\Update\Deprecated\UserZone\Exception\NotFoundException;
use TwoFAS\Update\Deprecated\UserZone\Exception\ValidationException;
use TwoFAS\Update\Deprecated\UserZone\Exception\AuthorizationException;

class Response
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var integer
     */
    private $code;

    /**
     * @param array   $data
     * @param integer $code
     */
    public function __construct(array $data, $code)
    {
        $this->data = $data;
        $this->code = $code;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Exception
     */
    public function getError()
    {
        if ($this->matchesHttpAndErrorCode(HttpCodes::BAD_REQUEST, Errors::USER_INPUT_ERROR)) {
            return new ValidationException($this->data['error']['msg']);
        } else if ($this->isJwtError()) {
            return new AuthorizationException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::NOT_FOUND, Errors::MODEL_NOT_FOUND)) {
            return new NotFoundException((string) $this->data['error']['msg']);
        }

        if (isset($this->data['error']['msg'])) {
            return new Exception('Unsupported response, original message: ' . $this->data['error']['msg']);
        }

        return new Exception('Unsupported response');
    }

    /**
     * @param integer $httpCode
     * @param integer $errorCode
     *
     * @return bool
     */
    public function matchesHttpAndErrorCode($httpCode, $errorCode)
    {
        if ($this->matchesHttpCode($httpCode)
            && $this->matchesErrorCode($errorCode)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param integer $httpCode
     *
     * @return bool
     */
    public function matchesHttpCode($httpCode)
    {
        return $this->code === $httpCode;
    }

    /**
     * @param integer $errorCode
     *
     * @return bool
     */
    public function matchesErrorCode($errorCode)
    {
        if (isset($this->data['error']['code'])
            && $errorCode === $this->data['error']['code']
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isJwtError()
    {
        if (
            $this->matchesHttpAndErrorCode(HttpCodes::BAD_REQUEST, Errors::JWT_TOKEN_NOT_PROVIDED) ||
            $this->matchesHttpAndErrorCode(HttpCodes::UNAUTHORIZED, Errors::JWT_TOKEN_EXPIRED) ||
            $this->matchesHttpAndErrorCode(HttpCodes::SERVER_ERROR, Errors::JWT_TOKEN_INVALID) ||
            $this->matchesHttpAndErrorCode(HttpCodes::NOT_FOUND, Errors::JWT_USER_NOT_FOUND) ||
            $this->matchesHttpAndErrorCode(HttpCodes::SERVER_ERROR, Errors::JWT_TOKEN_COULD_NOT_CREATE) ||
            $this->matchesHttpAndErrorCode(HttpCodes::UNAUTHORIZED, Errors::JWT_INVALID_CREDENTIALS)
        ) {
           return true;
        }

        return false;
    }

}
