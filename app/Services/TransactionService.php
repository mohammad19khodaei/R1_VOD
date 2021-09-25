<?php

namespace App\Services;

use App\Contracts\ProductContract;
use App\User;
use App\Comment;
use App\Article;
use App\Transaction;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected Transaction $transaction;

    public function deposit(User $user, int $amount): self
    {
        /** @var Transaction $newTransaction */
        $newTransaction = $user->transactions()->create([
            'type' => TransactionType::DEPOSIT,
            'amount' => $amount,
        ]);
        $this->transaction = $newTransaction;

        $user->update([
            'charge' => DB::raw('charge + ' . $amount),
        ]);

        return $this;
    }

    public function withdraw(User $user, int $amount)
    {
        /** @var Transaction $newTransaction */
        $newTransaction = $user->transactions()->create([
            'type' => TransactionType::WITHDRAWAL,
            'amount' => $amount,
        ]);
        $this->transaction = $newTransaction;

        $user->update([
            'charge' => DB::raw('charge - ' . $amount),
        ]);

        return $this;
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