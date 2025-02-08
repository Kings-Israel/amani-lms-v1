<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    protected $table = "loan_types";
    protected $fillable = ['name', 'created_at', 'updated_at'];

    public function loan()
    {
        return $this->hasMany('App\models\Loan');
    }
}
