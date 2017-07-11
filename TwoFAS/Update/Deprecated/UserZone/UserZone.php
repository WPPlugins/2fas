<?php

namespace TwoFAS\Update\Deprecated\UserZone;

use TwoFAS\Update\Deprecated\UserZone\Exception\Exception;
use TwoFAS\Update\Deprecated\UserZone\Exception\AuthorizationException;
use TwoFAS\Update\Deprecated\UserZone\HttpClient\ClientInterface;
use TwoFAS\Update\Deprecated\UserZone\HttpClient\CurlClient;
use TwoFAS\Update\Deprecated\UserZone\Response\Response;

class UserZone
{
    /**
     * @var string
     */
    const VERSION = '1.1.1';

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string
     */
    private $baseUrl = 'https://twofas-server.herokuapp.com';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var array
     */
    private $headers = array(
        'Content-Type' => 'application/json',
        'Sdk-Version'  => self::VERSION
    );

    /**
     * @param string|null $email
     * @param string|null $password
     * @param array       $headers
     */
    public function __construct($email = null, $password = null, array $headers = array())
    {
        $this->email      = $email;
        $this->password   = $password;
        $this->httpClient = new CurlClient();
        $this->hydrator   = new Hydrator();

        $this->addHeaders($headers);
    }

    /**
     * @param  string $url
     *
     * @return UserZone
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * @param ClientInterface $httpClient
     *
     * @return UserZone
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    public function getClient()
    {
        $response = $this->call(
            'GET',
            $this->createEndpoint('/me')
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getClientFromResponseData($response->getData());
        }

        throw $response->getError();
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $source
     *
     * @return Client
     *
     * @throws Exception
     */
    public function createClient($email, $password, $passwordConfirmation, $source)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/me'),
            array(
                'email'                 => $email,
                'password'              => $password,
                'password_confirmation' => $passwordConfirmation,
                'source'                => $source
            )
        );

        if ($response->matchesHttpCode(HttpCodes::CREATED)) {
            $this->email    = $email;
            $this->password = $password;
            return $this->hydrator->getClientFromResponseData($response->getData());
        }

        throw $response->getError();
    }

    /**
     * @param int $integrationId
     *
     * @return Integration
     *
     * @throws Exception
     */
    public function getIntegration($integrationId)
    {
        $response = $this->call(
            'GET',
            $this->createEndpoint('/integrations/' . $integrationId)
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getIntegrationFromResponseData($response->getData());
        }

        throw $response->getError();
    }

    /**
     * @param int $page
     *
     * @return IntegrationCollection
     *
     * @throws Exception
     */
    public function getIntegrations($page = 1)
    {
        $response = $this->call(
            'GET',
            $this->createEndpoint('/integrations?page=' . (int) $page)
            );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getIntegrationsFromResponseData($response->getData());
        }

        throw $response->getError();
    }

    /**
     * @param string $name
     *
     * @return Integration
     *
     * @throws Exception
     */
    public function createIntegration($name)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/integrations'),
            array(
                'name' => $name
            )
        );

        if ($response->matchesHttpCode(HttpCodes::CREATED)) {
            return $this->hydrator->getIntegrationFromResponseData($response->getData());
        }

        throw $response->getError();
    }


    /**
     * @param Integration $integration
     *
     * @return Integration
     *
     * @throws Exception
     */
    public function updateIntegration(Integration $integration)
    {
        $response = $this->call(
            'PUT',
            $this->createEndpoint('/integrations/' . $integration->getId()),
            $integration->toArray()
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $integration;
        }

        throw $response->getError();
    }

    /**
     * @param int    $integrationId
     * @param string $name
     *
     * @return Key
     *
     * @throws Exception
     */
    public function createKey($integrationId, $name)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint("/integrations/{$integrationId}/keys"),
            array(
                'name' => $name,
                'type' => 'production'
            )
        );

        if ($response->matchesHttpCode(HttpCodes::CREATED)) {
            $responseData = $response->getData();
            return new Key($responseData['token']);
        }

        throw $response->getError();
    }

    /**
     * @param  string $email
     *
     * @return NoContent
     *
     * @throws Exception
     */
    public function resetPassword($email)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/me/password-reset'),
            array('email' => $email)
        );

        if ($response->matchesHttpCode(HttpCodes::NO_CONTENT)) {
            return new NoContent();
        }

        throw $response->getError();
    }

    /**
     * @param array $headers
     */
    private function addHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {

            $key = $this->normalizeHeader($header);

            if (array_key_exists($key, $this->headers)) {
                throw new \InvalidArgumentException('Existing header could not be changed: ' . $key);
            }

            $this->headers[$key] = $value;
        }
    }

    /**
     * @param $header
     *
     * @return string
     */
    private function normalizeHeader($header)
    {
        $parts = explode('-',trim($header));

        foreach ($parts as &$part) {
            $part = ucfirst(strtolower($part));
        }

        return implode('-',$parts);
    }

    /**
     * @param string $suffix
     *
     * @return string
     */
    private function createEndpoint($suffix)
    {
        return $this->baseUrl . $suffix;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array  $data
     *
     * @return Response
     */
    private function call($method, $endpoint, array $data = array())
    {
        if (!array_key_exists('Authorization', $this->headers)) {
            $this->generateToken();
        }

        return $this->httpClient->request($method, $endpoint, $data, $this->headers);
    }

    /**
     * @throws AuthorizationException
     */
    private function generateToken()
    {
        if (is_null($this->email) || is_null($this->password)) {
            return;
        }

        $response = $this->httpClient->request(
            'POST',
            $this->createEndpoint('/me/login'),
            array(
                'email'    => $this->email,
                'password' => $this->password
            ),
            $this->headers
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            $responseData                   = $response->getData();
            $this->headers['Authorization'] = 'Bearer ' . $responseData['token'];
            return;
        }

        throw $response->getError();
    }
}
