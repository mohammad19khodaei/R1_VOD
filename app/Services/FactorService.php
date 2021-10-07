<?php

namespace App\Services;

use App\Contracts\ProductContract;
use App\Transaction;

class FactorService
{
    protected Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function create(ProductContract $product)
    {
        return $this->transaction->factors()->create([
            'factor_number' => uniqid('R1_', false),
            'product_id' => $product->id,
            'product_type' => get_class($product),
        ]);
    }
}