<?php

namespace App\models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $guarded = [];
//    protected $appends =['field_agent'];

    public function leader(){
        return $this->hasOne(Customer::class, 'id', 'leader_id');
    }

    public function customers(){
        return $this->belongsToMany(Customer::class, 'customer_group')
            ->withPivot(['role', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function field_agent()
    {
        return $this->belongsTo(User::class);

    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

}
