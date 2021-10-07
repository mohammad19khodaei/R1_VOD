<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\User;
use Illuminate\Support\Facades\DB;

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
        $this->user = $this->user->fresh();
    }
}