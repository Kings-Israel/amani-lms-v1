<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Next_of_kin extends Model
{
    protected $guarded = [];

    protected $fillable = [
        "Kin_name",
        "Kin_phone",
        "customer_id",
        "relationship_id"
    ];

    public function relationShip() {
        return $this->belongsTo(\App\models\Relationship::class, "relationship_id");
    }

}
