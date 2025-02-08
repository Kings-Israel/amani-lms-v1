<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Customer_location extends Model
{
    protected $guarded = [];

    protected $fillable = [
        "customer_id",
        "postal_address",
        "postal_code",
        "country",
        "county_id",
        "constituency",
        "ward",
        "physical_address",
        "longitude",
        "latitude",
        "business_longitude",
        "business_latitude",
        "business_address",
        "residence_type",
        "years_lived",
        "home_coordinates",
        "business_coordinates"
    ];

    public function customer() {
        return $this->belongsTo('App\models\Customer');
    }

    public function county() {
        return $this->belongsTo(\App\models\County::class);
    }
}
