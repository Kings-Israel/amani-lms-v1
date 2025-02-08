<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CheckOffProduct extends Model
{
    protected $fillable = [
        'name',
        'interest',
        'period',
        'status',
    ];

    public function loans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CheckOffLoan::class, 'product_id');
    }
}
