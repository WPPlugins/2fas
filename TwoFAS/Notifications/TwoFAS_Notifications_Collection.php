<?php

namespace TwoFAS\Notifications;

interface TwoFAS_Notifications_Collection
{
    /**
     * @param string $key
     */
    public function add_notification_key($key);

    /**
     * @return array
     */
    public function get_notifications_as_keys();
}
