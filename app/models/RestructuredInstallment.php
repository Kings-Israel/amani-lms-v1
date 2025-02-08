<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class RestructuredInstallment extends Model
{
    protected $fillable = [
        'loan_id', 'due_date', 'current', 'principal_amount', 'position', 'for_rollover', 'interest', 'total', 'amount_paid', 'start_date',
        'last_payment_date', 'completed', 'in_arrear', 'being_paid', 'interest_payment_date'
    ];
}
