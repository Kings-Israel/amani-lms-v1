<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CheckOffMpesaDisbursementResponse extends Model
{
    protected $fillable = [
        'loan_id', 'OriginatorConversationID',
        'ConversationID', 'TransactionID',
        'TransactionAmount', 'TransactionReceipt',
        'B2CRecipientIsRegisteredCustomer', 'issued',
        'response', 'ResultCode',
        'ResultDesc', 'B2CChargesPaidAccountAvailableFunds',
        'ReceiverPartyPublicName', 'TransactionCompletedDateTime',
        'B2CUtilityAccountAvailableFunds', 'B2CWorkingAccountAvailableFunds'
    ];

    public function loan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffLoan::class, 'loan_id');
    }

}
