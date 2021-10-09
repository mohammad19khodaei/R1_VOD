<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserBalanceService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function canSubmitArticle(): bool
    {
        return $this->user->balance > 0;
    }

    public function canSubmitComment(int $commentCount): bool
    {
        return $this->user->balance > 0 || $commentCount < setting(SettingKey::MAX_NUMBER_OF_FREE_COMMENT);
    }

    public function increase(int $amount): void
    {
        $this->user->increment('balance', $amount);
    }

    public function decrease(int $amount): void
    {
        $this->user->update([
            'balance' => DB::raw('balance - ' . $amount),
        ]);
    }

    public function chargeNotifyIsRequired(): bool
    {
        $newBalance = optional($this->user->fresh())->getAttribute('balance');
        return $newBalance < setting(SettingKey::NOTIFY_USER_BALANCE_THRESHOLD) &&
            !$this->user->isNotifiedForLowBalanceBefore();
    }

    public function chargeUser(int $amount): User
    {
        $user = null;
        DB::beginTransaction();
        try {
            $newBalance = $this->user->balance + $amount;
            $attributes = ['balance' => $newBalance];

            (new NotificationLogService($this->user))->resetLowBalanceNotificationStatus($newBalance);

            // enable user
            if ($newBalance > 0 && $this->user->isDisabled()) {
                $attributes['disabled_at'] = null;
            }

            (new TransactionService($this->user))->deposit($amount);

            DB::commit();
            $user = tap($this->user)->update($attributes);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollback();
        }

        return $user;
    }
}