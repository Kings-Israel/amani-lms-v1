<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'user_id','amount','transaction_no','date_payed'
    ];
}
