<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckOffLoan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'employee_id', 'loan_amount', 'end_date', 'effective_date', 'approved',
        'approved_date', 'approved_by', 'disbursed', 'disbursed_at', 'settled', 'settled_at',
        'interest', 'total_amount', 'rejected', 'rejected_by', 'rejected_at', 'employer_approval_id', 'rejected_by_employer'
    ];

    protected $appends = ['balance', 'amount_paid'];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffProduct::class, 'product_id');
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffEmployee::class, 'employee_id');
    }

    public function mpesa_disbursement_request(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(CheckOffMpesaDisbursementRequest::class, 'loan_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(CheckOffPayment::class, 'loan_id');
    }
    public function getAmountPaidAttribute()
    {
        return $this->payments()->sum('TransAmount');
    }

    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->getAmountPaidAttribute();
    }
}
