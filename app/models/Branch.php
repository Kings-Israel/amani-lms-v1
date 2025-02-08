<?php

namespace App\models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    //protected $guarded = [];
    protected $fillable = [
        'bname','status','bemail','bphone','paybill'
    ];
    protected $appends = [
        'loan_count', 'loan_total', 'total_paid', 'loan_balance', 'loan_arrears','month_total_paid', 'month_total_loan'/*, 'loan_disbursement_total'*/
        //added - mukhami
        ,'loan_balance_month', 'active_loan_balance'
    ];

    public function loans()
    {
        return $this->hasManyThrough('App\models\Loan', 'App\models\Customer');
    }

    public function groups(){

        return $this->hasMany(Group::class);
    }

    public function guarantors()
    {
        return $this->hasManyThrough('App\models\Guarantor', 'App\models\Customer');
    }

    //payments

    public function payments()
    {

        $payment = $this->loans()->with('payment')->get();
        // return $payment->payment()->where('payment_type_id', $id)->get();
        return $payment;
    }

    //registration payments
    public function Regpayments()
    {
        return $this->hasManyThrough(Regpayment::class, Customer::class);
    }

    //customers relationship
    public function customers()
    {
        return $this->hasManyThrough(Customer::class, User::class, 'branch_id', 'field_agent_id');
    }

    //expenses
    public function expenses(){

        return $this->hasMany(Expense::class);
    }

    //users to a branch relationship
    public function users(){

        return $this->hasMany(User::class);
    }

    //settlements
    public function settlements(){

        return $this->hasManyThrough(User_payments::class, User::class);
    }
    //investments
    public function investments(){

        return $this->hasManyThrough(Investment::class, User::class);
    }

    //users sms
    public function user_sms(){
        return $this->hasMany(UserSms::class);
    }
    //customers sms
    public function customer_sms(){

        return $this->hasMany(CustomerSms::class);
    }

    //total loan amount

    public function getTotalLoanAttribute()
    {
        $total = 0;

        foreach ($this->loans()->get() as $loan) {
            $total += $loan->getTotalAttribute();
        }

        return $total;
    }

    //total loan amount this month - Mukhami
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

        foreach ($this->loans()->whereDate('end_date', Carbon::now("Africa/Nairobi"))->get() as $loan) {
            //dd($loan->getTotalAttribute());
            $total += $loan->getTotalAttribute();
        }

        return $total;
    }
    //total loan amount this month
    public function today_inst_amount()
    {
        $total = 0;

        foreach ($this->loans()->where('settled', false)->get() as $loan) {
            //dd($loan->getTotalAttribute());
            $total += $loan->loan_due();
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

        foreach ($this->loans()->get() as $loan) {
            $total += $loan->total;

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

    //total paid so far in the month -Mukhami
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

    //loan balance without non performing loans
    public function getActiveLoanBalanceAttribute()
    {
        $total = 0;
        foreach ($this->loans()->where('disbursed', true)->get() as $loan) {
            $last_payment_date = $loan->last_payment_date;
            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90))
            {
                $total += $loan->balance;
            }

        }
        return $total;
    }

    //loan balance current month
    public function getLoanBalanceMonthAttribute(){
        $total = 0;
        $loans = $this->loans()
            ->where('disbursed','=',true)
            ->whereMonth('disbursement_date', Carbon::now())
            ->whereYear('disbursement_date', Carbon::now())
            ->get();
        foreach ($loans as $loan) {
            $total += $loan->balance;
        }
        return $total;

    }

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

    //total loan disbursement
    public function getTotalLoanDisbursement($year, $month)
    {
        $total = 0;
        if ($year != null && $month != null) {

            $loans = $this->loans()->where('disbursed', true)->whereYear('disbursement_date', $year)->whereMonth('disbursement_date', $month)->get();
            foreach ($loans as $loan) {
                $total += $loan->loan_amount;

            }
        } else {
            $loans = $this->loans()->where('disbursed', true)/*->whereYear('disbursement_date', $year)->whereMonth('disbursement_date', $year)*/
            ->get();
            foreach ($loans as $loan) {
                $total += $loan->loan_amount;

            }
        }

        return $total;
    }


    //total loan collections attribute

    public function total_loan_collections($year, $month)
    {
        $total = 0;
        if ($year != null && $month != null) {
            $loans = $this->payments();
            foreach ($loans as $loan) {
                $pays = $loan->payment()->whereYear('date_payed', $year)->whereMonth('date_payed', $month)->where('payment_type_id', 1)->get();
                foreach ($pays as $pay) {
                    $total += $pay->amount;
                }


            }
        } else {
            $loans = $this->payments();
            foreach ($loans as $loan) {
                $pays = $loan->payment()->where('payment_type_id', 1)->get();
                foreach ($pays as $pay) {
                    $total += $pay->amount;
                }


            }
        }

        return $total;
    }

    //total loan processing fee
    public function total_processing_fee($year, $month)
    {
        $total = 0;
        if ($year != null && $month != null) {
            // $loans = $this->payments();
            $loans = $this->loans()/*->whereYear('disbursement_date', $year)->whereMonth('disbursement_date', $month)*/->with('payment')->get();
              foreach ($loans as $loan) {
                  $pays = $loan->payment()->whereYear('date_payed', $year)->whereMonth('date_payed', $month)->where('payment_type_id', 3)->get();

                  foreach ($pays as $pay) {
                      $total += $pay->amount;
                  }


              }

            /*$setting = Setting::first();

            $total = $setting->loan_processing_fee * count($loans);*/
        } else {
            // $loans = $this->payments();
            $loans = $this->loans()->get();
            foreach ($loans as $loan) {
                $pays = $loan->payment()->where('payment_type_id', 3)->get();
                foreach ($pays as $pay) {
                    $total += $pay->amount;
                }


            }
            /*$setting = Setting::first();

            $total = $setting->loan_processing_fee * count($loans);*/
        }
        //dd($total);

        return $total;
    }

    //registration fee
    public function total_registration_fee($year, $month)
    {
        //dd($month);
        $total = 0;
        if ($year != null && $month != null) {
            $regs = $this->Regpayments()->whereYear('date_payed', $year)->whereMonth('date_payed', $month)->get();
            //dd($regs);
            foreach ($regs as $reg) {
                /*$pays = $reg->whereYear('date_payed', $year)->whereMonth('date_payed', $month)->get();
                foreach ($pays as $pay) {
                    $total += $pay->amount;
                }*/
                $total += $reg->amount;


            }
        } else {
            $regs = $this->Regpayments()->get();
            foreach ($regs as $reg) {
                /* $pays = $reg->get();
                 foreach ($pays as $pay) {
                     $total += $pay->amount;
                 }*/
                $total += $reg->amount;



            }
        }

        return $total;
    }




    //total expenses
    //

    public function total_expenses($year, $month)
    {
        $total = 0;
        if ($year != null && $month != null) {
            $expenses = $this->expenses()->whereYear('date_payed', $year)->whereMonth('date_payed', $month)->get();
            foreach ($expenses as $expense) {
                $total += $expense->amount;



            }
        } else {
            $expenses = $this->expenses()->get();
            foreach ($expenses as $expense) {
                $total += $expense->amount;



            }
        }

        return $total;
    }

    public function getloanDisbursementTotalAttribute() {
        //
    }

    //balance b/d
    public function balance_bd($year, $month)
    {

        //dd($year, $month);
        $loan_collections = 0;
        $processing_fee = 0;
        $reg_fee = 0;
        $total_disbursement = 0;
        $expenses = 0;
        $investment_total = 0;

        //get all investments
        $investments = $this->investments()->whereYear('date_payed', $year)->whereMonth('date_payed', '<', $month)->get();
        foreach ($investments as $investment){
            $investment_total += $investment->amount;
        }
        //get all the loan collections
        $loans = $this->payments();
        foreach ($loans as $loan) {
            $pays = $loan->payment()->whereYear('date_payed', $year)->whereMonth('date_payed', '<', $month)->where('payment_type_id', 1)->get();
            foreach ($pays as $pay) {
                $loan_collections += $pay->amount;
            }


        }

        //processing fee
        $l =  $this->loans()->get();
        foreach ($l as $loan) {
            $pays = $loan->payment()->whereYear('date_payed', $year)->whereMonth('date_payed', '<', $month)->where('payment_type_id', 3)->get();
            foreach ($pays as $pay) {
                $processing_fee += $pay->amount;
            }
        }

        //registration fee
        //  $regs = $this->Regpayments()->get();
        $regs = $this->Regpayments()->whereYear('date_payed', $year)->whereMonth('date_payed','<', $month)->get();

        //dd($month);
        foreach ($regs as $reg) {

            $reg_fee += $reg->amount;



        }


        //disbursement fee
        $l = $this->loans()->whereYear('disbursement_date', $year)->whereMonth('disbursement_date', '<', $month)->where('disbursed', true)->get();

        foreach ($l as $loan) {
            /*$pays = $loan->payment()->whereYear('date_payed', $year)->whereMonth('date_payed', '<', $month)->where('payment_type_id', 2)->get();
            foreach ($pays as $pay) {
                $total_disbursement += $pay->amount;
            }*/

            $total_disbursement += $loan->loan_amount;
        }

        $exp = $this->expenses()->whereYear('date_payed', $year)->whereMonth('date_payed','<', $month)->get();
        foreach ($exp as $expense) {
            $expenses += $expense->amount;



        }
       // dd($processing_fee);

        $inflows = $reg_fee + $loan_collections + $processing_fee + $investment_total;
        $outflows = $total_disbursement + $expenses;
          //dd($outflows);
        $balance = $inflows - $outflows;

        //dd($expenses);

        return $balance;

    }

    //sms
    public function  sms_total(){
        //dd($this->user_sms()->count());
        $sms_total = $this->user_sms()->count() + $this->customer_sms()->count();
        return $sms_total;

    }


}
