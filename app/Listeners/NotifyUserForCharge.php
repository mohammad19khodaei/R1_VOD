<?php

namespace App\Listeners;

use App\Events\UserUpdated;

class NotifyUserForCharge
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
        if ($user->isDirty('charge') && $user->notifyIsRequired()) {
            info('send email');
            $user->notifications()->create();
        }
    }
}
