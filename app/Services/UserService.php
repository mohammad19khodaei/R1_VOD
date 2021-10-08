<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\User;
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

            (new TransactionService($user))->deposit(setting(SettingKey::REGISTRATION_DEPOSIT));

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
            $newCharge = $user->charge + $amount;
            $attributes = ['charge' => $newCharge];

            if (
                $newCharge > setting(SettingKey::NOTIFY_USER_CHARGE_THRESHOLD) &&
                $lastNotification = $user->emailHistories()->where('in_progress', 1)->first()
            ) {
                $lastNotification->update(['in_progress' => 0]);
            }

            // enable user
            if ($newCharge > 0 && $user->isDisabled()) {
                $attributes['disabled_at'] = null;
            }

            (new TransactionService($user))->deposit($amount);

            $user->update($attributes);

            DB::commit();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollback();
        }
    }
}