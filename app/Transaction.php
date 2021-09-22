<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    const DEPOSIT_TYPE = 1;
    const WITHDRAWAL_TYPE = 2;

    const DEPOSIT_AMOUNT_ON_REGISTRATION = 100000;

    protected $fillable = ['type', 'amount'];

    /**
     * Get the user that owns the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
