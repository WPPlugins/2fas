<?php

namespace TwoFAS\Authentication;

class TwoFAS_Login_Form_Footer
{
    public function init()
    {
        add_action('login_footer', array($this, 'twofas_footer'));
    }
    
    public function twofas_footer()
    {
        $src = TWOFAS_PLUGIN_PATH . '/includes/img/2fas.png';

        if (isset($_REQUEST['interim-login']) && $_REQUEST['interim-login'] == 1) {
            return;
        }
?>
        <div class="twofas-login-footer">
            <div class="twofas-login-footer-logo"><img src="<?php echo $src; ?>" alt="2FAS logo"/></div>
            <span class="twofas-login-footer-tooltip">This site is secured by 2FAS</span>
        </div>
        <script>
            var query = window.location.search;

            if (query.indexOf('interim-login=1') >= 0 || query.indexOf('interim-login=true') >= 0) {
                jQuery(document).ready(function() {
                    jQuery('.twofas-login-footer').css('display', 'none');
                });
            }
        </script>
<?php
    }
}
