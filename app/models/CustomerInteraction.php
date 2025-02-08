<?php

namespace App\models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInteraction extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'customer_id',
        'user_id',
        'interaction_type_id',
        'remark',
        'next_scheduled_interaction',
        'interaction_category_id',
        'status',
        'followed_up',
        'additional_system_remark',
        'model_id',
        'closed_by',
        'closed_date',
        'target',
        'created_at',
        'updated_at'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interaction_type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerInteractionType::class, 'interaction_type_id', 'id');
    }
    public function interaction_category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerInteractionCategory::class, 'interaction_category_id', 'id');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function follow_ups()
    {
        return $this->hasMany(Customer_interaction_followup::class, 'follow_up_id');
    }


}
