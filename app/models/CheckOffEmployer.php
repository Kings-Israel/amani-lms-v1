<?php

namespace App\models;
use Illuminate\Foundation\Auth\User as Authenticatable;



class CheckOffEmployer extends  Authenticatable
{

    protected $fillable = [
        'code','name','location','contact_name','contact_phone_number', 'status', 'otp', 'password'
    ];

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CheckOffEmployee::class, 'employer_id', 'id');
    }
}
