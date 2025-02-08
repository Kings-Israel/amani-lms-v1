<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Mrequest extends Model
{
    protected $fillable = [
        'ConversationID',
        'OriginatorConversationID',
        'ResponseCode',
        'ResponseDescription',
        'settled',
        'loan_id',
        'requested_by',
        'disburse_loan_ip'
    ];
}
