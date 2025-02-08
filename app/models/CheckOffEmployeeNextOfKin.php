<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CheckOffEmployeeNextOfKin extends Model
{
    protected $fillable = [
        'name','phone_number','relationship'
    ];

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CheckOffEmployee::class, 'referee_id', 'id');
    }
}
