<?php

namespace App\models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CheckOffMpesaDisbursementRequest extends Model
{
    protected $fillable = [
        'loan_id', 'requested_by', 'ConversationID', 'OriginatorConversationID', 'ResponseCode', 'ResponseDescription', 'issued', 'response'
    ];

    public function loan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffLoan::class, 'loan_id');
    }

    public function requester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
