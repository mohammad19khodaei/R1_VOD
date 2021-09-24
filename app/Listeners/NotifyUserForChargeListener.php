<?php

namespace App\Listeners;

use App\Events\UserUpdated;
use App\Mail\LowUserCharge;
use Illuminate\Support\Facades\Mail;

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
        if ($user->isDirty('charge') && $user->notifyIsRequired()) {
            $message = (new LowUserCharge($user))
                ->onConnection('redis')
                ->onQueue('email');
            Mail::to($user->email)->queue($message);
            $user->notifications()->create();
        }
    }
}
