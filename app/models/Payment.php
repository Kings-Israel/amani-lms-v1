<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\models\Ptype;


class Payment extends Model
{
    protected $fillable = [
        'loan_id', 'date_payed', 'transaction_id','amount','channel','payment_type_id'
    ];

    protected $appends = [
        'type', 'loan_account'
    ];


    public function Payment_type(){

        return $this->belongsTo(Payment_type::class, 'payment_type_id', 'id');
    }

    public function Loan(){

       return $this->belongsTo(Loan::class);
    }

    public function Customer(){

    }

    public function getTypeAttribute(){

        return $this->Payment_type()->first()->name;
    }

    public function getLoanAccountAttribute(){

        return $this->Loan()->first()->loan_account;
    }



   /* public function getBranchAttribute(){

        $customer_id = $this->Loan()->first()->customer_id;


    }*/
}
