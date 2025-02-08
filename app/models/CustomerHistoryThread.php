<?php

namespace App\models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CustomerHistoryThread extends Model
{
    protected $fillable = ['customer_id', 'user_id', 'remark', 'date_visited', 'next_scheduled_visit', 'created_at', 'updated_at'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
