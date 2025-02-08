<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Referee extends Model
{
    protected $fillable = [
        'full_name',
        'id_number',
        'phone_number'
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_referee')
            ->withPivot(['created_at', 'updated_at'])->withTimestamps();
    }

    
}
