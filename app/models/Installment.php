<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $fillable = [
        'loan_id', 'due_date', 'current', 'principal_amount', 'position', 'for_rollover', 'interest', 'total', 'amount_paid', 'start_date',
        'lp_fee', 'last_payment_date', 'completed', 'in_arrear', 'being_paid', 'interest_payment_date'
    ];

    protected $appends = [
        'interest_paid'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'current' => 'bool'
    ];

    public function loan(){
        return $this->belongsTo(Loan::class);
    }

    public function getInterestPaidAttribute()
    {
        if ($this->amount_paid >= $this->interest) {
            $interest = $this->interest;
        } else {
            $interest = $this->amount_paid - $this->interest;
        }
        return $interest;
    }
}
