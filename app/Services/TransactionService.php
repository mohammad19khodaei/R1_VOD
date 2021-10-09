<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Transaction;
use App\User;

class TransactionService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function deposit(int $amount): void
    {
        $this->user->transactions()->create([
            'type' => TransactionType::DEPOSIT,
            'amount' => $amount,
        ]);

        (new UserChargeService($this->user))->increase($amount);
    }

    public function withdraw(int $amount): Transaction
    {
        /** @var Transaction $transaction */
        $transaction = $this->user->transactions()->create([
            'type' => TransactionType::WITHDRAWAL,
            'amount' => $amount,
        ]);

        (new UserChargeService($this->user))->decrease($amount);

        return $transaction;
    }
}