<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Enums\SettingKey;
use App\User;

class NotificationLogService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function resetLowBalanceNotificationStatus(int $newBalance): void
    {
        $lowBalanceNotification = $this->user->notificationLog()
            ->where('type', NotificationType::LOW_BALANCE_TYPE)
            ->first();

        if (!$lowBalanceNotification || $newBalance < setting(SettingKey::NOTIFY_USER_BALANCE_THRESHOLD)) {
            return;
        }

        $lowBalanceNotification->delete();
    }
}