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

    public function enableUser(User $user): void
    {
        if ($user->balance < 0 || !$user->isDisabled()) {
            return;
        }

        $user->update(['disabled_at' => null]);
    }
}