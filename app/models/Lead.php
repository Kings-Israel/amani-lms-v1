<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    //

    protected $fillable = [
        'name',
        'phone_number',
        'type_of_business',
        'location',
        'estimated_amount',
        'officer_id',
    ];
    
}
