<?php

namespace App;

use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\Installment;
use App\models\Investment;
use App\models\Loan;
use App\models\Payment;
use App\models\RoTarget;
use App\models\User_payments;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'branch_id', 'password','salary','status', 'last_seen','field_agent_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    protected $appends = [
        'branch', 'disbursement_target', 'collection_target', 'disbursed_count', 'total_collections', 'percentage_disbursed',
        'percentage_collection', 'loan_count', 'loan_total', 'total_paid', 'loan_balance', 'loan_arrears', 'average_performance',
        'investment'
        //mukhami
        ,'month_total_loan'/*,'loan_balance_month'*/ , 'active_loan_balance'
    ];

    //get loans

    public function loans()
    {

        return $this->hasManyThrough(Loan::class, Customer::class, 'field_agent_id', 'customer_id');
    }

    //get Ro target relationship
    public function RoTarget()
    {

        return $this->hasOne(RoTarget::class);
    }

    //investors settlement
    public function investments()
    {

        return $this->hasMany(Investment::class);
    }

    //investors and loan officers investments and salary settlements relationship
    public function settlement()
    {
        return $this->hasMany(User_payments::class, 'user_id', 'id');
    }

    //user payments
    public function user_payments(){

        return $this->hasMany(User_payments::class);
    }

    //login tokens
    public function login_token()
    {
        return $this->hasMany(LoginToken::class);
    }

    //get investment amount by investor
    public function getInvestmentAttribute()
    {
        $total = 0;

        if ($this->hasRole('investor')) {
            foreach ($this->investments()->get() as $item) {
                $total += $item->amount;
            }
        }

        return $total;
    }

    //get collection_target
    public function getCollectionTargetAttribute()
    {

        if ($this->RoTarget()->first()) {
            return $this->RoTarget()->first()->collection_target;
        }
    }
    public function target($date)
    {

        if ($this->RoTarget()->whereMonth('created_at', $date)->whereYear('created_at', Carbon::now())->first()) {
            return $this->RoTarget()->whereMonth('created_at', $date)->whereYear('created_at', Carbon::now())->first();
        }
    }

    //get collection_target
    public function getDisbursementTargetAttribute()
    {

        if ($this->RoTarget()->first()) {
            return $this->RoTarget()->first()->disbursement_target;
        }
    }



    //number of loans disbursed disbursed_count

    public function getDisbursedCountAttribute()
    {

        $loans = $this->loans()->where('disbursed', true)->count();
        return $loans;
    }

    //ammount collected
    public function getTotalCollectionsAttribute($date)
    {
        if ($this->hasRole(['field_agent'])) {
            $loans = $this->loans()->where('disbursed', true)->get();
          // dd($date);

            $total = 0;
            $payments = 0;
            foreach ($loans as $loan) {
                if ($date != null) {
                    $payments += Payment::where(['payment_type_id' => 1, 'loan_id' => $loan->id])->whereYear('date_payed', $date)->whereMonth('date_payed', $date)/*->whereDay('date_payed', $date)*/->sum('amount');

                } else {
                    $payments += Payment::where(['payment_type_id' => 1, 'loan_id' => $loan->id])->whereYear('date_payed', Carbon::now())->whereMonth('date_payed', Carbon::now())/*->whereDay('date_payed', Carbon::now())*/->sum('amount');

                }


            }
            return $payments;
        }


    }

    //collections mtd
    public function collections_MTD($date)
    {
        $loans = $this->loans()->where('disbursed', true)->get();
        $total = 0;
        foreach ($loans as $loan) {
            $payments = Payment::where(['payment_type_id' => 1, 'loan_id' => $loan->id])->whereYear('date_payed', $date)->whereMonth('date_payed', $date)->sum('amount');
        }
        return $payments;


    }




    //installments due
    public function installmets_due($date)
    {
        $total = 0;
        $instals = $this->loans()->get();
        foreach ($instals as $instal)
        {
            $in = Installment::where(['loan_id' => $instal->id])->whereYear('due_date', $date)->whereMonth('due_date', $date)->whereDay('due_date', $date)->first();
            if ($in){
                $due = $in->total - $in->amount_paid;
                $total += $due;
            }
        }
        return $total;
    }

    //percentage disbursed
    public function getPercentageDisbursedAttribute()
    {
        if ((int)$this->getDisbursementTargetAttribute() > 0) {

            return (int)$this->getDisbursedCountAttribute() / (int)$this->getDisbursementTargetAttribute() * 100 . "%";
        } else {
            return 0 . "%";
        }
    }

    //percentage collection
    public function getPercentageCollectionAttribute($date)
    {
        if ((int)$this->getCollectionTargetAttribute() > 0) {
            return (int)$this->getTotalCollectionsAttribute($date) / (int)$this->getCollectionTargetAttribute() * 100 . "%";
        } else {
            return 0 . '%';
        }
    }

    //average performance
    public function getAveragePerformanceAttribute($date)
    {
        $Per_disbursement = 0;
        $Per_collection = 0;
        if ((int)$this->getCollectionTargetAttribute() > 0) {


            $Per_collection = (int)$this->getTotalCollectionsAttribute($date) / (int)$this->getCollectionTargetAttribute() * 100;
        }

        if ((int)$this->getDisbursementTargetAttribute() > 0) {

            $Per_disbursement = (int)$this->getDisbursedCountAttribute() / (int)$this->getDisbursementTargetAttribute() * 100;
        }

        $average = ($Per_collection + $Per_disbursement) / 2;

        return $average . '%';


    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    //get branch

    public function getBranchAttribute()
    {
        if ($this->branch_id == 0) {
            return "No Branch";
        }

        return $this->branch()->first()->bname;
    }


    //loan officer functions

    //total loan amount

    public function getTotalLoanAttribute()
    {
        $total = 0;

        foreach ($this->loans()->get() as $loan) {
            $total += $loan->getTotalAttribute();
        }

        return $total;
    }
//total loan amount this month
    public function getMonthTotalLoanAttribute()
    {
        $total = 0;

        foreach ($this->loans()->whereMonth('end_date', Carbon::now())->get() as $loan) {
            $total += $loan->getTotalAttribute();
        }

        return $total;
    }

    //total loan amount this month
    public function today_loan_amount()
    {
        $total = 0;
        if ($this->hasRole(['field_agent'])) {

            foreach ($this->loans()->get() as $loan) {
                $total += $loan->whereDate('end_date', Carbon::now("Africa/Nairobi"))->getTotalAttribute();
            }
        }

        return $total;
    }

    //total loan amount today
    public function today_inst_amount()
    {
        $total = 0;
        if ($this->hasRole(['field_agent'])) {
            foreach ($this->loans()->where('settled', false)->get() as $loan) {
                $total += $loan->loan_due();
            }
        }

        return $total;
    }

    //get total number of loans

    public function getLoanCountAttribute()
    {

        return $this->loans()->where('disbursed', true)->count();
    }

    //get total loans ammount

    public function getLoanTotalAttribute()
    {
        $total = 0;
        if ($this->hasRole(['field_agent'])) {
            foreach ($this->loans()->get() as $loan) {
                // var_dump((int)$loan->total); exit();
                $total += (int)$loan->total;

            }
        }

        return $total;
    }

    //total paid
    public function getTotalPaidAttribute()
    {
        $total = 0;

        foreach ($this->loans()->get() as $loan) {
            $total += $loan->amount_paid;

        }
        return $total;
    }
// total paid this month - Mukhami
    public function getMonthTotalPaidAttribute()
    {
        $total = 0;

        foreach ($this->loans()->whereMonth('end_date', Carbon::now())->get() as $loan) {
            $total += $loan->amount_paid;

        }
        return $total;
    }


    //loan balances
    public function getLoanBalanceAttribute()
    {
        $total = 0;

        foreach ($this->loans()->where('disbursed', true)->get() as $loan) {
            $total += $loan->balance;

        }
        return $total;
    }
    public function getActiveLoanBalanceAttribute()
    {
        $total = 0;
        foreach ($this->loans()->where('disbursed', true)->get() as $loan) {
            $last_payment_date = $loan->last_payment_date;
            if ($last_payment_date == null || $last_payment_date > \Carbon\Carbon::now()->subDays(90))
            {
                $total += $loan->balance;
            }

        }
        return $total;
    }
//Loan Balance - current Month
//    public function getLoanBalanceMonthAttribute(){
//        $total = 0;
//        $loans = $this->loans()
//                    ->where('disbursed','=',true)
//                    ->whereMonth('disbursement_date', Carbon::now())
//                    ->whereYear('disbursement_date', Carbon::now())
//                    ->get();
//        foreach ($loans as $loan) {
//            $total += $loan->balance;
//        }
//        return $total;
//
//    }

    //total arrears
    //loan balances
    public function getLoanArrearsAttribute()
    {
        $total = 0;

        foreach ($this->loans()->get() as $loan) {
            $total += $loan->total_arrears;

        }
        return $total;
    }


}
