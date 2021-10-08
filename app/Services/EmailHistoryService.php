<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\User;

class EmailHistoryService
{
    public function removeLastInProgress(User $user, int $newCharge): void
    {
        $lastEmailHistory = $user->emailHistories()->where('in_progress', 1)->first();
        if (!$lastEmailHistory || $newCharge < setting(SettingKey::NOTIFY_USER_CHARGE_THRESHOLD)) {
            return;
        }

        $lastEmailHistory->update(['in_progress' => 0]);
    }
}