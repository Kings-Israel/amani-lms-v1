<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Regpayment extends Model
{
    //
    protected $fillable = [
        'customer_id', 'date_payed', 'transaction_id','amount','channel'
    ];
    protected $appends = ['fullname', 'branch'];

    public function  customer(){

        return $this->belongsTo(Customer::class);
    }

    //customer name
    public function getFullnameAttribute(){

        return $this->customer()->first()->fullName;
    }

    //branch
    public function getBranchAttribute(){

        $branch = Branch::where('id', $this->customer()->first()->branch_id)->first();

        return $branch->bname;

    }
}
