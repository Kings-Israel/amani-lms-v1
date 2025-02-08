<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CustomerSms extends Model
{
    protected $fillable = [
        'customer_id','sms','branch_id', 'phone'
    ];
}
