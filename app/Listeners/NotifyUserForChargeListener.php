<?php

namespace App\Listeners;

use App\Events\UserUpdated;
use App\Services\EmailService;
use App\Services\UserChargeService;

class NotifyUserForChargeListener
{
    /**
     * Handle the event.
     *
     * @param UserUpdated $event
     * @return void
     */
    public function handle(UserUpdated $event)
    {
        $user = $event->user;
        $userChargeService = new UserChargeService($user);

        if ($user->isDirty('charge') && $userChargeService->notifyIsRequired()) {
            (new EmailService())->sendLowChargeEmail($user);
            $user->emailHistories()->create();
        }
    }
}
