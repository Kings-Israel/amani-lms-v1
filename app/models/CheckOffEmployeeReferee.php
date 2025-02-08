<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CheckOffEmployeeReferee extends Model
{
    protected $fillable = [
        'name','address','phone_number','relationship','occupation'
    ];

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CheckOffEmployee::class, 'referee_id', 'id');
    }
}
