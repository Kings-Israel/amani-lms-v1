<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CheckOffEmployeeSms extends Model
{
    protected $fillable = [
        'employee_id', 'sms', 'phone_number'
    ];
}
