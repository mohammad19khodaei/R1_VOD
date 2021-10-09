<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserChargeService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function canSubmitArticle(): bool
    {
        return $this->user->charge > 0;
    }

    public function canSubmitComment(int $commentCount): bool
    {
        return $this->user->charge > 0 || $commentCount < setting(SettingKey::MAX_NUMBER_OF_FREE_COMMENT);
    }

    public function increase(int $amount): void
    {
        $this->user->increment('charge', $amount);
    }

    public function decrease(int $amount): void
    {
        $this->user->update([
            'charge' => DB::raw('charge - ' . $amount),
        ]);
    }

    public function chargeNotifyIsRequired(): bool
    {
        $newCharge = optional($this->user->fresh())->getAttribute('charge');
        return $newCharge < setting(SettingKey::NOTIFY_USER_CHARGE_THRESHOLD) &&
            !$this->user->isNotifiedBefore();
    }

    public function chargeUser(int $amount): User
    {
        $user = null;
        DB::beginTransaction();
        try {
            $newCharge = $this->user->charge + $amount;
            $attributes = ['charge' => $newCharge];

            (new EmailHistoryService())->removeLastInProgress($this->user, $newCharge);


            // enable user
            if ($newCharge > 0 && $this->user->isDisabled()) {
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