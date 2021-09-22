<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\User;
use App\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function deposit(User $user, int $amount)
    {
        $user->transactions()->create([
            'type' => TransactionType::DEPOSIT,
            'amount' => $amount,
        ]);
        $user->update([
            'charge' => DB::raw('charge + ' . $amount),
        ]);
    }
}