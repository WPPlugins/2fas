<?php

namespace TwoFAS\Update\Deprecated\UserZone;

class IntegrationCollection
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $currentSlice;

    /**
     * @var int
     */
    private $lastSlice;

    /**
     * @var int
     */
    private $from;

    /**
     * @var int
     */
    private $to;

    /**
     * @var Integration[]
     */
    private $integrations = array();

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $total
     *
     * @return IntegrationCollection
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentSlice()
    {
        return $this->currentSlice;
    }

    /**
     * @param int $currentSlice
     *
     * @return IntegrationCollection
     */
    public function setCurrentSlice($currentSlice)
    {
        $this->currentSlice = $currentSlice;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastSlice()
    {
        return $this->lastSlice;
    }

    /**
     * @param int $lastSlice
     *
     * @return IntegrationCollection
     */
    public function setLastSlice($lastSlice)
    {
        $this->lastSlice = $lastSlice;
        return $this;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param int $from
     *
     * @return IntegrationCollection
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return int
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param mixed $to
     *
     * @return IntegrationCollection
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @param Integration $integration
     */
    public function addIntegration(Integration $integration)
    {
        if ($this->hasIntegration($integration->getId())) {
            throw new \InvalidArgumentException('Integration already exists');
        }

        $this->integrations[$integration->getId()] = $integration;
    }

    /**
     * @param int $integrationId
     *
     * @return Integration
     */
    public function getIntegration($integrationId)
    {
        if (!$this->hasIntegration($integrationId)) {
            throw new \InvalidArgumentException('Integration not exists');
        }

        return $this->integrations[$integrationId];
    }

    /**
     * @param int $integrationId
     *
     * @return bool
     */
    public function hasIntegration($integrationId)
    {
        return array_key_exists($integrationId, $this->integrations);
    }

    /**
     * @return Integration[]
     */
    public function getIntegrations()
    {
        return $this->integrations;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->integrations);
    }

    /**
     * @param \Closure $userFunc
     *
     * @return Integration|null
     */
    public function first(\Closure $userFunc)
    {
        foreach ($this->integrations as $integration) {
            if ($userFunc($integration)) {
                return $integration;
            }
        }

        return null;
    }
}