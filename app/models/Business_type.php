<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\models\Industry;

class Business_type extends Model
{
    protected $guarded = [];

    protected $fillable = ["iname", "industry_id"];

    public function industry() {
        return $this->belongsTo(Industry::class);
    }
}
