<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['product_name', 'installments', 'interest','duration'];

    public function loans(){

        return $this->hasMany(Loan::class);
    }

    //installments
    public function month_interest($date){

        $loans = $this->loans()->get();
        foreach ($loans as $loan){

        }
    }
}
