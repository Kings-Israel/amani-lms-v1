<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CheckOffMpesaDisbursementTransaction extends Model
{
    protected $fillable = [
        'loan_id', 'transaction_receipt', 'amount', 'channel', 'disbursed_at'
    ];

    public function loan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffLoan::class, 'loan_id');
    }
}
