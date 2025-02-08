<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Guarantor extends Model
{
    protected $guarded = [];
    protected $appends = [
        'business'
    ];


    private function Business(){
        return $this->belongsTo('App\models\Business_type','business_id','id');
    }

    //get branch

    public function getBusinessAttribute(){

        return $this->Business()->first()->bname;
    }

    //get customer

    public function customer(){
        return $this->belongsTo('App/models/Customer');
    }

}
