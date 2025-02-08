<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepaymentMpesaTransaction extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the customer that owns the RepaymentMpesaTransaction
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
