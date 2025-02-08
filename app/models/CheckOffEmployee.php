<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class CheckOffEmployee extends Model
{
    protected $fillable = [
        'referee_id','next_of_kin_id','employer_id','first_name','last_name','phone_number','id_number',
        'primary_email','institution_email','dob','gender','marital_status','date_of_employment','terms_of_employment',
        ];

    protected $appends = ['full_name'];

    public function referee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffEmployeeReferee::class, 'referee_id');
    }

    public function employer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffEmployer::class, 'employer_id');
    }

    public function next_of_kin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CheckOffEmployeeNextOfKin::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name .' '. $this->last_name;
    }
}
