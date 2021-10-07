<?php

namespace App\Services;

use App\Mail\LowUserCharge;
use App\User;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendLowChargeEmail(User $user): void
    {
        $message = (new LowUserCharge($user))
            ->onConnection('redis')
            ->onQueue('email');
        
        Mail::to($user->email)->queue($message);
    }
}