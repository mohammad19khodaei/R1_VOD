<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\User;

class EmailHistoryService
{
    public function removeLastInProgress(User $user, int $newBalance): void
    {
        $lastEmailHistory = $user->emailHistories()->where('in_progress', 1)->first();
        if (!$lastEmailHistory || $newBalance < setting(SettingKey::NOTIFY_USER_BALANCE_THRESHOLD)) {
            return;
        }

        $lastEmailHistory->update(['in_progress' => 0]);
    }
}