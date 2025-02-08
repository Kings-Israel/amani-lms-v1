<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Msetting extends Model
{
    protected $fillable = [
        'paybill',
        'SecurityCredential' ,
        'InitiatorName',
        'Consumer_Key',
        'Consumer_Secret',
        'MMF_balance',
        'Utility_balance',
        'last_updated',
    ];
}
