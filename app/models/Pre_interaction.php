<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Pre_interaction extends Model
{
    protected $fillable = [
        'amount', 'model_id', 'customer_id', 'interaction_category_id', 'due_date', 'system_remark'
    ];
}
