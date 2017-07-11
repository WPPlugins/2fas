<?php

namespace TwoFAS\Update\Deprecated\UserZone;

class Hydrator
{
    /**
     * @param array $responseData
     *
     * @return Client
     */
    public function getClientFromResponseData(array $responseData)
    {
        $client = new Client();
        $client
            ->setId($responseData['id'])
            ->setEmail($responseData['email'])
            ->setHasCard($responseData['has_card'])
            ->setHasGeneratedPassword($responseData['has_generated_password']);

        return $client;
    }

    /**
     * @param array $responseData
     *
     * @return Integration
     */
    public function getIntegrationFromResponseData(array $responseData)
    {
        $integration = new Integration();
        $integration
            ->setId($responseData['id'])
            ->setLogin($responseData['login'])
            ->setName($responseData['name'])
            ->setChannels(array(
                'sms'   => $responseData['channel_sms'],
                'call'  => $responseData['channel_call'],
                'email' => $responseData['channel_email'],
                'totp'  => $responseData['channel_totp'],
            ));

        return $integration;
    }

    /**
     * @param array $responseData
     *
     * @return IntegrationCollection
     */
    public function getIntegrationsFromResponseData(array $responseData)
    {
        $collection = new IntegrationCollection();
        $collection
            ->setTotal($responseData['total'])
            ->setCurrentSlice($responseData['current_page'])
            ->setLastSlice($responseData['last_page'])
            ->setFrom($responseData['from'])
            ->setTo($responseData['to']);

        foreach ($responseData['data'] as $data) {
            $integration = $this->getIntegrationFromResponseData($data);
            $collection->addIntegration($integration);
        }

        return $collection;
    }
}