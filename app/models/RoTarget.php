<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class RoTarget extends Model
{
    protected $fillable =
        [
            'user_id', 'disbursement_target','collection_target','date', 'customer_target', 'disbursement_target_amount'
        ];



}
