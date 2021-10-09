<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function createUser(array $parameters)
    {
        DB::transaction(function () use ($parameters, &$user) {
            $user = User::create($parameters);

            (new TransactionService($user))->deposit(setting(SettingKey::REGISTRATION_DEPOSIT));
        });

        return $user;
    }

    public function enableUser(User $user, int $newBalance): void
    {
        if ($newBalance < 0 || !$user->isDisabled()) {
            return;
        }

        $user->update(['disabled_at' => null]);
    }
}