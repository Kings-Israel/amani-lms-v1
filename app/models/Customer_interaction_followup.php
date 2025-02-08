<?php

namespace App\models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Customer_interaction_followup extends Model
{
    protected $fillable = ['follow_up_id', 'follow_by', 'remark', 'next_scheduled_interaction', 'status'];

    public function followed_by()
    {
        return $this->belongsTo(User::class, 'follow_by');
    }
}
