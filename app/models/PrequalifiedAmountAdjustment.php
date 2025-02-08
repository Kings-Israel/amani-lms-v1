<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class PrequalifiedAmountAdjustment extends Model
{
    protected $fillable = [
        'customer_id', 'initial_amount', 'proposed_amount','status', 'initiated_by','approved_by', 'approved_at','created_at', 'updated_at'
    ];
}
