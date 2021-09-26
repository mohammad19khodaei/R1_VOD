<?php

namespace App\Listeners;

use App\Events\UserUpdated;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DisableUserListener
{
    /**
     * Handle the event.
     *
     * @param UserUpdated $event
     * @return void
     */
    public function handle(UserUpdated $event)
    {
        // some hackery to prevent infinite loop on save inside updated event
        $original = $event->user;
        $user = clone $original;
        $user->syncOriginal();

        $charge = optional($user->fresh())->getAttribute('charge');
        if ($original->isDirty('charge') && $charge < 0) {
            $user->disabled_at = now();
            $user->save();
        }
    }
}