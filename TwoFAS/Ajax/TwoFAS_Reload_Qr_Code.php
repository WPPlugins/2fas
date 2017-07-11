<?php

namespace TwoFAS\Ajax;

use TwoFAS\Api\TotpSecretGenerator;
use TwoFAS\SDK\QR_Code;

class TwoFAS_Reload_Qr_Code extends TwoFAS_Ajax_Action
{
    public function handle()
    {
        if ($this->is_ajax_request()) {
            $action_name = $this->app->get_request()->get_from_post('action_name');

            // Verify nonce
            check_ajax_referer($action_name, 'security');

            $secret_generator = new TotpSecretGenerator();
            $secret           = $secret_generator->generate();
            $qr_code          = QR_Code::generate($secret);
            $response         = array(
                'totp_private_key' => $secret,
                'qr_code'          => $qr_code
            );

            wp_send_json($response);
        }

        die();
    }
}
