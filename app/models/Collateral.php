<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
    protected $fillable = [
        'item', 'description', 'serial_no', 'market_value', 'image_url', 'loan_id'
    ];
}
