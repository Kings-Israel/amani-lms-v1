<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Activity_otp extends Model
{
    protected $table = "activity_otps";
    protected $fillable = [
        'user_id',
        'token',
        'activity',
        'status',
        'expire_at'
    ];
}
