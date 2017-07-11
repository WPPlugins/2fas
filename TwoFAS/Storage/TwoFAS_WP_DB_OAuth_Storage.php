<?php

namespace TwoFAS\Storage;

use TwoFAS\UserZone\OAuth\Interfaces\TokenStorage;
use TwoFAS\UserZone\OAuth\Token;
use TwoFAS\UserZone\OAuth\TokenNotFoundException;

class TwoFAS_WP_DB_OAuth_Storage implements TokenStorage
{
    const TWOFAS_OAUTH_TOKEN_BASE = 'twofas_oauth_token_';
    const ACCESS_TOKEN_KEY        = 'access_token';
    const INTEGRATION_ID_KEY      = 'integration_id';

    /**
     * @inheritDoc
     */
    public function retrieveToken($type)
    {
        $tokenArray = get_option($this->get_meta_name($type));

        if (is_array($tokenArray)) {
            return new Token($type, $tokenArray[self::ACCESS_TOKEN_KEY], $tokenArray[self::INTEGRATION_ID_KEY]);
        }

        throw new TokenNotFoundException;
    }

    /**
     * @inheritDoc
     */
    public function storeToken(Token $token)
    {
        update_option($this->get_meta_name($token->getType()), array(
            self::ACCESS_TOKEN_KEY   => $token->getAccessToken(),
            self::INTEGRATION_ID_KEY => $token->getIntegrationId(),
        ));
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function get_meta_name($type)
    {
        return self::TWOFAS_OAUTH_TOKEN_BASE . $type;
    }
}
