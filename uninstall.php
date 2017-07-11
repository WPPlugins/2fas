<?php

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once 'TwoFAS_Uninstaller.php';

$uninstaller = new TwoFAS_Uninstaller();
$uninstaller->uninstall();
