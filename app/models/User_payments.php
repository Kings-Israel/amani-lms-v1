<?php

namespace App\models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class User_payments extends Model
{
    protected $fillable = [
        'user_id','expense_id','amount','date_payed','channel', 'transaction_id'
    ];

    protected $appends = [
        'user_name', 'type', 'branch'
    ];

    public function User(){

        return $this->belongsTo(User::class);
    }

    public function expense(){
        return $this->belongsTo(Expense::class);
    }

    public function  getTypeAttribute(){

        return $this->expense()->first()->expense_name;
    }

    //branch name
    public function getBranchAttribute(){

        $branch = Branch::where('id', $this->User()->first()->branch_id)->first();
        return $branch->bname;
    }

    public function getUserNameAttribute(){

        return $this->User()->first()->name;
    }
}
