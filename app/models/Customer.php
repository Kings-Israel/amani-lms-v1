<?php

namespace App\models;

use App\User;
use App\models\Business_type;
use App\models\Industry;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    protected $fillable = [
        "type",
        "title",
        "fname",
        "mname",
        "lname",
        "field_agent_id",
        "guarantor_id",
        "tax_pin",
        "dob","phone",
        "email",
        "document_id",
        "id_no",
        "marital_status",
        "gender",
        "is_employed",
        "employment_status",
        "income_range_id",
        "employment_date",
        "employer",
        "branch_id",
        "account_id",
        "industry_id",
        "business_type_id",
        "prequalified_amount",
        "alternate_phone",
        "industry_id","business_type_id",
        "account_id",
        "status",
        'previous_prequalified_amount',
        "times_loan_applied",
        "classification"
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = ['fullName', 'fullNameUpper'];


    public function getFullNameAttribute() {
        return $this->fname .' '. $this->lname;
    }

    public function getfNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getmNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getlNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getFullNameUpperAttribute() {
        return strtoupper( $this->fname .' '. $this->lname );
    }

    public function regpayments(){
        return $this->hasMany(\App\models\Regpayment::class);
    }

    public function referees(){
        return $this->belongsToMany(Referee::class, 'customer_referee')
            ->withPivot(['created_at', 'updated_at'])->withTimestamps();
    }

    public function payment()
    {
        return $this->hasManyThrough('App\models\Payment', 'App\models\Loan');
    }

    public function location() {
        return $this->hasOne('App\models\Customer_location');
    }

    public function customer_document() {
        return $this->hasOne(CustomerDocument::class);
    }

    public function accountType() {
        return $this->belongsTo(\App\models\Account::class, "account_id");
    }

    public function branch() {
        return $this->belongsTo(\App\models\Branch::class);
    }

    public function businessType() {
        return $this->belongsTo(Business_type::class, "business_type_id");
    }

    public function industry() {
        return $this->belongsTo(Industry::class);
    }

    public function guarantor() {
        return $this->belongsTo(\App\models\Guarantor::class);
    }

    public function nextOfKin() {
        return $this->hasOne(\App\models\Next_of_kin::class, "customer_id");
    }

    public function incomeRange() {
        return $this->belongsTo(\App\models\Income_range::class);
    }

    public function idDocument() {
        return $this->belongsTo(\App\models\Document::class, "document_id");
    }

    public function hasSettledAndActiveLoans()
    {
        $hasSettledLoan = $this->loans()->where('settled', '1')->exists();

        $hasActiveLoan = $this->loans()->where('settled', '0')->exists();

        return $hasSettledLoan && $hasActiveLoan;
    }

    public function loans() {
        return $this->hasMany(\App\models\Loan::class);
    }

    public function lastCompletePayment() {
        return $this->payment()->where("approved", 1);
    }

    public function disbursements() {
        return $this->loans()->where('disbursed', 1);
    }

    //belongs to loan officer
    public function Officer(){
        return $this->belongsTo(User::class,'field_agent_id','id');
    }

    //belongs to a group
    public function group()
    {
        return $this->belongsToMany(Group::class, 'customer_group')
            ->withPivot(['role', 'created_at', 'updated_at'])
            ->withTimestamps();
    }
}
