<?php

namespace App\Services;

use App\Mail\LowUserBalance;
use App\User;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendLowBalanceEmail(User $user): void
    {
        $message = (new LowUserBalance($user))
            ->onConnection('redis')
            ->onQueue('email');
        
        Mail::to($user->email)->queue($message);
    }
}