<?php

namespace App\models;

use App\models\Business_type;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $guarded = [];

    protected $fillable = ["bname", "industry_id"];

    public function businessTYpes() {
        return $this->hasMany(BUsiness_type::class);
    }
}
