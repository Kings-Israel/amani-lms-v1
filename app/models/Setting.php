<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'registration_fee', 'loan_processing_fee', 'rollover_interest', 'lp_fee'
    ];
    public $timestamps = false;


    public function required_pay(){

        // $required_pay = (int)$this->loan_processing_fee + (int)$this->registration_fee;
        $required_pay = (int)$this->registration_fee;

        return (int)$required_pay;
    }
}
