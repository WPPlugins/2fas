<?php

namespace TwoFAS\Actions\Result;

use TwoFAS\Actions\TwoFAS_Action;
use TwoFAS\Notifications\TwoFAS_Notifications_Collection;

interface TwoFAS_Action_Result
{
    /**
     * @param TwoFAS_Action $action
     */
    public function handle(TwoFAS_Action $action);

    /**
     * @param TwoFAS_Notifications_Collection $collection
     */
    public function consume_notifications(TwoFAS_Notifications_Collection $collection);
}
