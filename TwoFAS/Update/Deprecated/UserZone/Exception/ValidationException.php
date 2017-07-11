<?php

namespace TwoFAS\Update\Deprecated\UserZone\Exception;

use TwoFAS\Update\Deprecated\UserZone\Errors;

class ValidationException extends Exception
{
    /**
     * @var array
     */
    private $errors = array();

    /**
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct('Validation exception', Errors::USER_INPUT_ERROR);

        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->errors);
    }

    /**
     * @param string $key
     *
     * @return array|null
     */
    public function getError($key)
    {
        if (!$this->hasKey($key)) {
            return null;
        }

        return $this->errors[$key];
    }
}
