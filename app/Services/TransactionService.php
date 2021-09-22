<?php

namespace App\Services;

use App\User;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function deposit(User $user, int $amount): bool
    {
        DB::beginTransaction();
        try {
            $user->transactions()->create([
                'type' => Transaction::DEPOSIT_TYPE,
                'amount' => $amount,
            ]);
            $user->update([
                'charge' => DB::raw('charge + ' . $amount),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollback();
            return false;
        }

        return true;
    }
}