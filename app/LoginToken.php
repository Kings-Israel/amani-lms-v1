<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginToken extends Model
{
    protected $fillable = ['user_id', 'token', 'active','in_use', 'token_expires_at', 'created_at', 'updated_at'];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
