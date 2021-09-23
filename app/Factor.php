<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Factor extends Model
{
    protected $fillable = ['factor_number', 'product_id', 'product_type'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): MorphTo
    {
        return $this->morphTo();
    }
}
