<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_type_id','amount','branch_id','date_payed','paid_by','description'
    ];

    protected $appends =
        [
            'expense_name', 'branch'
        ];

    public function type(){

        return $this->belongsTo(Expense_type::class,'expense_type_id');
    }
    public function branch(){

        return $this->belongsTo(Branch::class);
    }

    public function getExpenseNameAttribute(){
        return $this->type()->first()->expense_name;
    }

    //branch
    public function getBranchAttribute(){

        return $this->branch()->first()->bname;
    }
}
