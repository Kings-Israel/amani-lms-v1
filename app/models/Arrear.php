<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Arrear extends Model
{
    protected $fillable = [
        'loan_id', 'amount','installment_id'
    ];

    protected $appends = ['last_payment_date'];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function getLastPaymentDateAttribute(){
        return $this->installment()->first()->last_payment_date;
    }
}
