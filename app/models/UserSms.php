<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class UserSms extends Model
{
    protected $fillable = [
        'branch_id','user_id','sms','phone'
    ];
}
