<?php

namespace App\Listeners;

use App\Enums\NotificationType;
use App\Events\UserUpdated;
use App\Services\EmailService;
use App\Services\UserBalanceService;

class NotifyUserForLowBalanceListener
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

        if ($user->isDirty('balance') && (new UserBalanceService($user))->chargeNotifyIsRequired()) {
            (new EmailService())->sendLowBalanceEmail($user);
            $user->notificationLog()->create(['type' => NotificationType::LOW_BALANCE_TYPE]);
        }
    }
}
