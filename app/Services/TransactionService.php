<?php

namespace App\Services;

use App\Contracts\ProductContract;
use App\Enums\TransactionType;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected Transaction $transaction;

    public function deposit(User $user, int $amount): Transaction
    {
        /** @var Transaction $transaction */
        $transaction = $user->transactions()->create([
            'type' => TransactionType::DEPOSIT,
            'amount' => $amount,
        ]);

        (new UserChargeService($user))->increase($amount);

        return $transaction;
    }

    public function withdraw(User $user, int $amount): Transaction
    {
        /** @var Transaction $transaction */
        $transaction = $user->transactions()->create([
            'type' => TransactionType::WITHDRAWAL,
            'amount' => $amount,
        ]);

        (new UserChargeService($user))->decrease($amount);

        return $transaction;
    }

    /**
     * @param ProductContract $product
     * @throws \Exception
     */
    public function createFactor(ProductContract $product): void
    {
        $this->checkTransaction();

        $this->transaction->factors()->create([
            'factor_number' => bin2hex(random_bytes(10)),
            'product_id' => $product->id,
            'product_type' => get_class($product),
        ]);
    }

    protected function checkTransaction(): void
    {
        if (is_null($this->transaction)) {
            throw new \Exception('Factor only can be created for a transaction');
        }
    }
}