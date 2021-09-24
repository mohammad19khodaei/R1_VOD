<?php

namespace App\Providers;

use App\Events\UserUpdated;
use App\Listeners\DisableUserListener;
use App\Listeners\NotifyUserForChargeListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserUpdated::class => [
            NotifyUserForChargeListener::class,
            DisableUserListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
