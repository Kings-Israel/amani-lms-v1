<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CheckOffPayment extends Model
{

    protected $fillable = [
        'employer_id',
        'loan_id',
        'employee_id',
        'channel',
        'TransID',
        'TransAmount',
        'TransTime',
        'BusinessShortCode',
        'BillRefNumber',
        'InvoiceNumber',
        'OrgAccountBalance',
        'MSISDN',
        'FirstName',
        'MiddleName',
        'LastName',
    ];

    public function loan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffLoan::class, 'loan_id');
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffEmployee::class, 'employee_id');
    }

}
