<?php

namespace TwoFAS\SDK;

use TwoFAS\Api\QrCode\QrClientFactory;
use TwoFAS\Api\QrCodeGenerator;

class QR_Code
{
    /**
     * @param  string $secret
     * @return string
     */
    public static function generate($secret)
    {
        // TOTP metadata
        $qr_code_generator = new QrCodeGenerator(QrClientFactory::getInstance());
        $blog_name         = urlencode(get_option('blogname', 'WordPress Account'));
        $user              = wp_get_current_user();
        $user_email        = $user->user_email;
        $description       = urlencode('WordPress Account');
        $site_url          = get_option('siteurl');

        if ($site_url) {
            $parsed = parse_url($site_url);

            if (isset($parsed['host'])) {
                $description = $parsed['host'];
            }
        }

        $message = "otpauth://totp/{$description}:$user_email?secret={$secret}&issuer={$blog_name}";

        return $qr_code_generator->generateBase64($message);
    }
}
