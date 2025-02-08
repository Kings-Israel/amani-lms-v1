<?php

namespace App\models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $guarded = [];

    protected $appends = ['owner', 'product', 'installments', 'interest', 'phone', 'field_agent', 'field_agent_name', 'branch_name', 'branch', 'total', 'amount_paid', 'balance', 'amount_paid_b4_rollover', 'balance_b4_rollover', 'rolled_over_date', 'rollover_fee', 'percentage_paid',
        'next_payment_date', 'total_arrears', 'elapsed_schedule', 'profit', 'last_payment_date'];


    public function customer()
    {
        return $this->belongsTo('App\models\Customer');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /********************get product where loan belongs to*/
    public function product()
    {
        return $this->belongsTo('App\models\Product');
    }

    /**************************get payments relationship********************/
    public function payment()
    {
        return $this->hasMany(Payment::class, 'loan_id', 'id');
    }

    //get rollover
    public function rollover()
    {
        return $this->hasOne(Rollover::class);
    }

    //get pending rollover
    public function pending_rollover()
    {
        return $this->hasOne(PendingRollover::class);
    }

    //get loan arrear
    public function arrears()
    {
        return $this->hasMany(Arrear::class);
    }

    //get installments
    public function Installments()
    {
        return $this->hasMany(Installment::class);
    }

    //last payment made for loan
   public function getLastPaymentDateAttribute()
   {
        $installment = $this->installments()->orderBy('last_payment_date', 'desc')->first();
        if($installment){
            if($installment->last_payment_date != null)
            return $installment->last_payment_date;
        }
   }

    //due today in instalments
    public function loan_due()
    {
        $inst = $this->Installments()/*->where('current', true)*/->whereDate('due_date', Carbon::now())->first();

        $bal = 0;
        if ($inst) {
            $bal = $inst->total - $inst->amount_paid;

        }
        return $bal;
    }

    //due today loans count installments
    public function loan_due_count()
    {
        $inst = $this->Installments()->where('current', true)->whereDate('due_date', Carbon::now())->first();

        $count = 0;
        if ($inst) {
            $count = +1;

        }
        return $count;
    }

    //has many mrequest
    public function mrequests()
    {
        return $this->hasMany(Mrequest::class, 'loan_id', 'id');
    }

    //total arrears  amount
    public function getTotalArrearsAttribute()
    {
        if ($this->arrears()->first()) {
            $amount = 0;

            foreach ($this->arrears()->get() as $arrear) {
                $amount += $arrear->amount;

            }
            return $amount;
        } else {
            return 0;
        }
    }

    //next payment date
    public function getNextPaymentDateAttribute()
    {

        if ($this->disbursed) {
            $inst = $this->Installments()->where('current', true)->first();
            //$next = $this->Installments()->where('position', $inst->position + 1)->first();
            if ($inst) {
                return $inst->due_date;
            }
            return 'N/A';
        }
    }

    //get elapsed schedules
    //next payment date
    public function getElapsedScheduleAttribute()
    {
        if ($this->disbursed) {
            $inst = $this->Installments()->where('current', true)->first();
            if ($inst) {

                return $inst->position - 1;
            }
            return $this->Installments()->count();

        }
    }

    //get total to be paid {principal + interest}
    public function getTotalAttribute()
    {
        if ($this->rolled_over) {
            $total = $this->total_amount + $this->rollover()->first()->rollover_interest;
            return $total;
        }

        $total = $this->total_amount;

        return $total;
    }

    //get total paid
    public function getAmountPaidAttribute()
    {
        $total = $this->payment()->where('payment_type_id', 1)->sum('amount');
        return $total;
    }

    //percentage paid
    public function getPercentagePaidAttribute()
    {
        $percentage_paid = (int)$this->getAmountPaidAttribute() / (int)$this->getTotalAttribute() * 100;

        return $percentage_paid;
    }

    //balance
    public function getBalanceAttribute()
    {
        $balance = $this->getTotalAttribute() - $this->getAmountPaidAttribute();

        return $balance;
    }


    public function getOwnerAttribute()
    {

        return $this->customer()->first()->fname . ' ' . $this->customer()->first()->lname;
    }


    public function getProductAttribute()
    {

        return $this->product()->first()->product_name;
    }


    public function getInstallmentsAttribute()
    {

        return $this->product()->first()->installments;
    }

    public function getInterestAttribute()
    {

        return $this->product()->first()->interest;
    }

    public function getPhoneAttribute()
    {

        return $this->customer()->first()->phone;

    }

    public function getFieldAgentAttribute()
    {
        $user = User::where('id', $this->customer()->first()->field_agent_id)->first();

        return $user;
    }

    public function getFieldAgentNameAttribute()
    {
        $name = $this->getFieldAgentAttribute()->name;

        return $name;
    }

    /*get brach of the loan*/
    public function getBranchAttribute()
    {

        $branch = Branch::find($this->customer()->first()->branch_id);
        return $branch;
    }

    // branch name
    public function getBranchNameAttribute()
    {

        $branch = Branch::find($this->customer()->first()->branch_id)->bname;
        return $branch;
    }

//rollover interest
    public function getRolloverFeeAttribute()
    {
        if ($this->rolled_over) {
            return (int)$this->rollover()->first()->rollover_interest;

        }

    }

//amount_paid_b4_rollover

    public function getAmountPaidB4RolloverAttribute()
    {
        if ($this->rolled_over) {

            $rollover = ($total = $this->loan_amount + ($this->loan_amount * ($this->product()->first()->interest / 100))) - (int)$this->rollover()->first()->amount;

            return $rollover;
        }


    }

//balance_b4_rollover

    public function getBalanceB4RolloverAttribute()
    {
        if ($this->rolled_over) {


            return (int)$this->rollover()->first()->amount;
        }


    }

//rolled over date
    public function getRolledOverDateAttribute()
    {
        if ($this->rolled_over) {


            return $this->rollover()->first()->rollover_date;
        }
    }


//loan profit
    public function getProfitAttribute()
    {
        $profit = $this->getAmountPaidAttribute() - $this->loan_amount;

        return $profit;
    }

    public function paidInterest($year, $month): int
    {
//loan has been completed
        $interest = 0;
        if ($month == null) {
            $installments = $this->Installments()->where('for_rollover', false)->whereYear('interest_payment_date', $year)->get();
        } else {
            $installments = $this->Installments()->where('for_rollover', false)->whereYear('interest_payment_date', $year)->whereMonth('interest_payment_date', $month)->get();
        }
        foreach ($installments as $installment) {
            $interest += $installment->interest;
        }
        return $interest;
    }

    public function paidInterestv2($year, $month): int
    {
//loan has been completed
        $interest = 0;
        if ($month == null) {
            $installments_interest_sum = $this->Installments()->where('for_rollover', false)->whereYear('interest_payment_date', $year)->sum('interest');
        } else {
            $installments_interest_sum = $this->Installments()->where('for_rollover', false)->whereYear('interest_payment_date', $year)->whereMonth('interest_payment_date', $month)->sum('interest');
        }

        return $installments_interest_sum;
    }

//paid rollover interest
    public function paidRolloverInterest($year, $month)
    {
//loan has been completed
        $interest = 0;
        if ($month == null) {
            $installments = $this->Installments()->where('for_rollover', true)->whereYear('interest_payment_date', $year)->get();
        } else {
            $installments = $this->Installments()->where('for_rollover', true)->whereYear('interest_payment_date', $year)->whereMonth('interest_payment_date', $month)->get();
        }
        foreach ($installments as $installment) {
            $interest += $installment->interest;
        }

        return $interest;

    }

    public static function getTotalUnpaidBalance()
    {
        return self::where('settled', 0)
            ->where('approved', 1)
            ->whereNotNull('approved_date')
            ->sum(\DB::raw('total_amount - total_amount_paid'));
    }

}
