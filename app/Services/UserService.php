<?php

namespace App\Services;

use App\User;
use App\Enums\TransactionAmount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function createUser(array $parameters)
    {
        $user = null;
        DB::beginTransaction();
        try {
            $user = User::create($parameters);

            (new TransactionService())->deposit($user, TransactionAmount::REGISTRATION_DEPOSIT);

            DB::commit();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollback();
        }
        return $user;
    }

    public function chargeUser(User $user, int $amount)
    {
        DB::beginTransaction();
        try {
            $user->update([
                'charge' => DB::raw('charge + ' . $amount)
            ]);

            // remove last notification in_progress status
            $newCharge = $user->fresh()->charge;
            if (
                $newCharge > User::NOTIFY_USER_CHARGE_THRESHOLD &&
                $lastNotification = $user->notifications()->where('in_progress', 1)->first()
            ) {
                $lastNotification->update(['in_progress' => 0]);
            }

            // enable user
            if ($newCharge > 0 && $user->isDisabled()) {
                $user->update([
                    'disabled_at' => null,
                ]);
            }

            DB::commit();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollback();
        }

    }
}