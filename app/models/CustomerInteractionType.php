<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CustomerInteractionType extends Model
{
    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];

    public function interactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerInteraction::class, 'interaction_type_id');
    }


}
