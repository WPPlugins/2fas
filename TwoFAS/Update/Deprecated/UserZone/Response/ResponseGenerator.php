<?php

namespace TwoFAS\Update\Deprecated\UserZone\Response;

use TwoFAS\Update\Deprecated\UserZone\Exception\Exception;

class ResponseGenerator
{
    /**
     * @param string  $body
     * @param integer $code
     *
     * @return Response
     *
     * @throws Exception
     */
    public static function createFrom($body, $code)
    {
        if ('' === $body) {
            return new Response(array(), $code);
        }

        $decoded = @json_decode($body, true);

        if (is_null($decoded)) {
            throw new Exception('Invalid response. Json expected.');
        }

        return new Response($decoded, $code);
    }
}
