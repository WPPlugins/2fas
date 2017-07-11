<?php

namespace TwoFAS\Authentication;

use BadMethodCallException;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\Exception\Exception;
use TwoFAS\Api\TwoFAS;

class TwoFAS_Code_State
{
    const ACCEPTED              = 'code_accepted';
    const REJECTED_CAN_RETRY    = 'code_rejected_can_retry';
    const REJECTED_CANNOT_RETRY = 'code_rejected_cannot_retry';
    const UNDEFINED             = 'undefined_code_state';
    const ERROR                 = 'error_occurred';

    /**
     * @var string
     */
    private $code;

    /**
     * @var TwoFAS
     */
    private $twofas;

    /**
     * @var bool
     */
    private $code_checked;

    /**
     * @var string
     */
    private $state;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code         = $code;
        $this->state        = self::UNDEFINED;
        $this->code_checked = false;
    }

    /**
     * @param TwoFAS $twofas
     */
    public function set_twofas(TwoFAS $twofas)
    {
        $this->twofas = $twofas;
    }

    /**
     * @return bool
     */
    public function is_empty()
    {
        return empty($this->code);
    }

    /**
     * @return bool
     */
    public function has_valid_pattern()
    {
        if ($this->is_empty()) {
            return false;
        }

        return 1 === preg_match("/^[0-9]{6}$/", $this->code);
    }

    /**
     * @param AuthenticationCollection $authentications
     *
     * @return bool
     */
    public function is_valid(AuthenticationCollection $authentications)
    {
        if ($this->is_empty()) {
            return false;
        }

        if (!$this->code_checked) {
            $this->check_code($authentications);
        }

        return self::ACCEPTED === $this->state;
    }

    /**
     * @param AuthenticationCollection $authentications
     *
     * @return bool
     */
    public function rejected_can_retry(AuthenticationCollection $authentications)
    {
        if ($this->is_empty()) {
            return false;
        }

        if (!$this->code_checked) {
            $this->check_code($authentications);
        }

        return self::REJECTED_CAN_RETRY === $this->state;
    }

    /**
     * @param AuthenticationCollection $authentications
     *
     * @return bool
     */
    public function rejected_cannot_retry(AuthenticationCollection $authentications)
    {
        if ($this->is_empty()) {
            return false;
        }

        if (!$this->code_checked) {
            $this->check_code($authentications);
        }

        return self::REJECTED_CANNOT_RETRY === $this->state;
    }

    /**
     * @return bool
     */
    public function error()
    {
        return self::ERROR === $this->state;
    }

    /**
     * @param AuthenticationCollection $authentications
     */
    private function check_code(AuthenticationCollection $authentications)
    {
        if (!$this->twofas) {
            throw new BadMethodCallException;
        }

        $this->state = self::UNDEFINED;

        try {
            $state = $this->twofas->checkCode($authentications, $this->code);

            if (is_a($state, '\TwoFAS\Api\Code\AcceptedCode')) {
                $this->state = self::ACCEPTED;
            }

            if (is_a($state, '\TwoFAS\Api\Code\RejectedCodeCanRetry')) {
                $this->state = self::REJECTED_CAN_RETRY;
            }

            if (is_a($state, '\TwoFAS\Api\Code\RejectedCodeCannotRetry')) {
                $this->state = self::REJECTED_CANNOT_RETRY;
            }
        } catch (Exception $e) {
            $this->state = self::ERROR;
        }

        $this->code_checked = true;
    }
}
