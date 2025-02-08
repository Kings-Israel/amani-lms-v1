<?php

namespace App\Jobs;

use App\Mail\sendAutomatedEmail;
use App\Mail\sendScoreSheetEmail;
use App\models\Arrear;
use App\models\Customer;
use App\models\Installment;
use App\models\Payment;
use App\models\Product;
use App\models\Rollover;
use App\models\RoTarget;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ScoreSheetEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;

    public $users;
    public $recipient;
    public $branches;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users, $branches, User $recipient)
    {
        $this->recipient = $recipient;
        $this->users = $users;
        $this->branches = $branches;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = $this->users;
        $branches = $this->branches;

        $CO_data = array();
        foreach ($users as $user) {
            $name = $user->name;
            //repayment rate && Disbursement Amount
            $rp_loans = $user->loans()->where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get();
            $disb_loans = count($rp_loans);
            $disbTotalAmount = 0;
            $disbPaidAmount = 0;
            $arr = array();
            foreach ($rp_loans as $loan) {
                $disbTotalAmount += $loan->loan_amount;
                $disbPaidAmount += $loan->getAmountPaidAttribute();
                array_push($arr, $loan->id);
            }
            if (count($rp_loans)>0){
                $loanSize = $disbTotalAmount / count($rp_loans);
            }else{
                $loanSize = 0;
            }
            $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $installments = Installment::whereBetween('due_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();
            $paid = 0;
            $due = 0;
            foreach ($installments as $installment) {
                $paid += $installment->amount_paid;
                $due += $installment->total;
            }
            if ($due > 0) {
                $repayment_rate = ($paid/$due) * 100;
            }
            else {
                $repayment_rate = 0;
            }
            //PAR
            $loans_w_arrears = $user->loans()->where('settled', '=' , false)
                ->whereHas('arrears', function (Builder $q) { $q->where('amount', '>', 0);})->get();


            $arrears_total = 0;
            foreach ($loans_w_arrears as $lns){
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90))
                {
                    $arrears_total += $lns->total_arrears;
                }
            }
            $totalAmount = 0;
            $user_loans = $user->loans()->where('disbursed','=',true)->get();
            foreach ($user_loans as $loan) {
                $last_payment_date = $loan->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > \Carbon\Carbon::now()->subDays(90))
                {
                    $totalAmount += $loan->balance;
                }
            }

            if ($totalAmount > 0) {
                $par= (int)(($arrears_total / $totalAmount) * 100);
            } else {
                $par = 0;
            }

            //1 to 30
            $amt = 0;
            $category =  DB::table('categories')->first();
            $loans = DB::table('loans')
                ->where('disbursed', true)
                ->whereExists(function ($query) {
                    $query->select("arrears.loan_id")
                        ->from('arrears')
                        ->whereRaw('arrears.loan_id = loans.id')
                        ->whereRaw('arrears.amount != 0');
                })
                ->join('customers', function ($join) use ($user) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        ->where('customers.field_agent_id', '=', $user->id);
                })
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.id', 'loans.product_id', 'loans.rolled_over', 'loans.loan_amount')
                ->get();
            foreach ($loans as $loan) {
                $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                $inst = Installment::find($arrear->installment_id);
                $created = Carbon::parse($inst->due_date);
                $overdue_days = $created->diffInDays(Carbon::now());
                if ($overdue_days <= $category->days && $overdue_days > $category->days - 30) {
                    $amt += Arrear::where('loan_id', $loan->id)->sum('amount');;
                }
            }
            //Non-Performing
            $non_performing_loans = array();
            $unsettled_loans = $user->loans()->where(['settled' => false, 'disbursed' => true])->get();
            foreach ($unsettled_loans as $lns){
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date != null && $last_payment_date < Carbon::now()->subDays(90))
                {
                    array_push($non_performing_loans, $lns);
                }
            }
            $non_performing_count = count($non_performing_loans);
            $non_performing_balance = 0;
            foreach ($non_performing_loans as $lo){
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $tot = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $tot = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
                }
                $non_performing_balance += $tot - $payments;
            }
            //Customers
            $cust = Customer::where('field_agent_id', '=',$user->id)->whereMonth('created_at', '=', Carbon::now())->whereYear('created_at', '=', Carbon::now())->get();

            //rolled over
            $rolled_over_loans = $user->loans()->whereHas('rollover', function (Builder $query) {
                $query->whereMonth('rollover_date', '=', Carbon::now())
                    ->whereYear('rollover_date', '=', Carbon::now());
            })->where(['settled' => false, 'disbursed' => true, 'rolled_over'=>true])->get();

            $rolled_over_loans_count = $rolled_over_loans->count();
            $rolled_over_balance = 0;
            foreach ($rolled_over_loans as $lo){
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
                }
                $rolled_over_balance += $total - $payments;
            }
            //skipped payments
            $all_loans = $user->loans()->whereHas('arrears', function (Builder $builder){
                $builder->where('amount', '!=',0);
            })->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->orderBy('id', 'desc')->get();
            $skipped = array();
            $skipped_installments_amount = 0;
            foreach ($all_loans as $lns)
            {
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90)){
                    $skipped_installments = Installment::where(['loan_id'=>$lns->id, 'completed'=>false])->where('due_date', '<', Carbon::now())->get();
                    if(count($skipped_installments) > 1){
                        foreach ($skipped_installments as $skipped_installment){
                            $skipped_installments_amount += $skipped_installment->amount_paid;
                            array_push($skipped, array('total'=>$skipped_installment->total, 'paid'=>$skipped_installment->amount_paid));
                        }
                    }
                }
            }
            $skipped_installments_count = count($skipped);
            //percentage disbursed
            $ro_disb_target_sum = RoTarget::where('user_id', $user->id)->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now())->first()->disbursement_target;
            if ($ro_disb_target_sum > 0){
                $dis = $user->loans()->where('disbursed', true)->whereYear('disbursement_date', Carbon::now())->whereMonth('disbursement_date', Carbon::now())->count();
                $per = (int)(($dis / $ro_disb_target_sum) * 100);
            }else{
                $per = 0;
            }


            array_push($CO_data, array(
                'CO_name'=>$name,
                'OLB'=>$totalAmount,
                'repayment_rate'=>$repayment_rate,
                'par'=>$par,
                '1_to_30'=>$amt,
                'disb_loans'=>$disb_loans,
                'disb_loans_amount'=>$disbTotalAmount,
                'loan_size'=>number_format($loanSize,2),
                'rolled_over'=>$rolled_over_loans_count,
                'rolled_over_amount'=>$rolled_over_balance,
                'non_performing_count'=>$non_performing_count,
                'non_performing_amount'=>$non_performing_balance,
                'customers'=>count($cust),
                'skipped'=>$skipped_installments_count,
                'skipped_amount'=>$skipped_installments_amount,
                'perc_disb'=>$per,
            ));
        }

        $branch_data = array();
        foreach ($branches as $branch) {
            $name = $branch->bname;
            //repayment rate && Disbursement Amount
            $rp_loans = $branch->loans()->where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get();
            $disb_loans = count($rp_loans);
            $disbTotalAmount = 0;
            $disbPaidAmount = 0;
            $arr = array();
            foreach ($rp_loans as $loan) {
                $disbTotalAmount += $loan->loan_amount;
                $disbPaidAmount += $loan->getAmountPaidAttribute();
                array_push($arr, $loan->id);
            }
            if (count($rp_loans)>0){
                $loanSize = $disbTotalAmount / count($rp_loans);
            }else{
                $loanSize = 0;
            }
            $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $installments = Installment::whereBetween('due_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();
            $paid = 0;
            $due = 0;
            foreach ($installments as $installment) {
                $paid += $installment->amount_paid;
                $due += $installment->total;
            }
            if ($due > 0) {
                $repayment_rate = ($paid/$due) * 100;
            }
            else {
                $repayment_rate = 0;
            }
            //PAR
            $loans_w_arrears = $branch->loans()->where('settled', false)
                ->whereHas('arrears', function ($q) { $q->where('amount', '>', 0);})->get();

             $arrears_total = 0;
            foreach ($loans_w_arrears as $lns){
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90))
                {
                    $arrears_total += $lns->total_arrears;
                }
            }

            $totalAmount = $branch->getActiveLoanBalanceAttribute();
            if ($totalAmount > 0) {
                $par= (int)(($arrears_total / $totalAmount) * 100);
            } else {
                $par = 0;
            }

            //1 to 30
            $amt = 0;
            $category =  DB::table('categories')->first();
            $loans = DB::table('loans')
                ->where('disbursed', true)
                ->whereExists(function ($query) {
                    $query->select("arrears.loan_id")
                        ->from('arrears')
                        ->whereRaw('arrears.loan_id = loans.id')
                        ->whereRaw('arrears.amount != 0');
                })
                ->join('customers', function ($join) use ($branch) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        ->where('customers.branch_id', '=', $branch->id);
                })
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.id', 'loans.product_id', 'loans.rolled_over', 'loans.loan_amount')
                ->get();
            foreach ($loans as $loan) {
                $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                $inst = Installment::find($arrear->installment_id);
                $created = Carbon::parse($inst->due_date);
                $overdue_days = $created->diffInDays(Carbon::now());
                if ($overdue_days <= $category->days && $overdue_days > $category->days - 30) {
                    $amt += Arrear::where('loan_id', $loan->id)->sum('amount');;
                }
            }
            //Non-Performing
            $non_performing_loans = array();
            $unsettled_loans = $branch->loans()->where(['settled' => false, 'disbursed' => true])->get();
            foreach ($unsettled_loans as $lns){
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date != null && $last_payment_date < Carbon::now()->subDays(90))
                {
                    array_push($non_performing_loans, $lns);
                }
            }
            $non_performing_count = count($non_performing_loans);
            $non_performing_balance = 0;
            foreach ($non_performing_loans as $lo){
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $tot = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $tot = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
                }
                $non_performing_balance += $tot - $payments;
            }
            //Customers
            $cust = Customer::where('branch_id', '=',$branch->id)->whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->get();

            //rolled over
            $rolled_over_loans = $branch->loans()->whereHas('rollover', function (Builder $query) {
                $query->whereMonth('rollover_date', '=', Carbon::now())
                    ->whereYear('rollover_date', '=', Carbon::now());
            })->where(['settled' => false, 'disbursed' => true, 'rolled_over'=>true])->get();

            $rolled_over_loans_count = $rolled_over_loans->count();
            $rolled_over_balance = 0;
            foreach ($rolled_over_loans as $lo){
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
                }
                $rolled_over_balance += $total - $payments;
            }

            //skipped payments
            $all_loans = $branch->loans()->whereHas('arrears', function (Builder $builder){
                $builder->where('amount', '!=',0);
            })->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->orderBy('id', 'desc')->get();
            $skipped = array();
            $skipped_installments_amount = 0;
            foreach ($all_loans as $lns)
            {
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90)){
                    $skipped_installments = Installment::where(['loan_id'=>$lns->id, 'completed'=>false])->where('due_date', '<', Carbon::now())->get();
                    if(count($skipped_installments) > 1){
                        foreach ($skipped_installments as $skipped_installment){
                            $skipped_installments_amount += $skipped_installment->amount_paid;
                            array_push($skipped, array('total'=>$skipped_installment->total, 'paid'=>$skipped_installment->amount_paid));
                        }
                    }
                }
            }
            $skipped_installments_count = count($skipped);
            //percentage disbursed
            $cos = User::role('field_agent')->where('branch_id', $branch->id)->whereHas('RoTarget', function ($q) {
                $q->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now());
            })->get();
            $percentages = [];
            foreach ($cos as $co) {
                $ro_disb_target_sum = RoTarget::where('user_id', $co->id)->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now())->first()->disbursement_target;
                if ($ro_disb_target_sum > 0){
                    $dis = $co->loans()->where('disbursed', true)->whereYear('disbursement_date', Carbon::now())->whereMonth('disbursement_date', Carbon::now())->count();
                    $per = (int)(($dis / $ro_disb_target_sum) * 100);
                }else{
                    $per = 0;
                }
                    array_push($percentages, $per);
                }

            if (count($percentages)>0){
                $avg = array_sum($percentages) / count($percentages);
            }else{
                $avg = 0;
            }

            array_push($branch_data, array(
                'CO_name'=>$name,
                'OLB'=>$totalAmount,
                'repayment_rate'=>$repayment_rate,
                'par'=>$par,
                '1_to_30'=>$amt,
                'disb_loans'=>$disb_loans,
                'disb_loans_amount'=>$disbTotalAmount,
                'loan_size'=>number_format($loanSize,2),
                'rolled_over'=>$rolled_over_loans_count,
                'rolled_over_amount'=>$rolled_over_balance,
                'non_performing_count'=>$non_performing_count,
                'non_performing_amount'=>$non_performing_balance,
                'customers'=>count($cust),
                'skipped'=>$skipped_installments_count,
                'skipped_amount'=>$skipped_installments_amount,
                'perc_disb'=>round($avg, 2),
            ));
        }
        $data = ['credit_officers'=>$CO_data, 'branches'=>$branch_data];

        $email = new sendScoreSheetEmail($data);

        //add other recipients here
//        Mail::to(['mukhami@deveint.com'])->queue($email);
        Mail::to([$this->recipient->recipient_email])->queue($email);
    }
}
