<?php

namespace App\Http\Controllers;

use App\Jobs\AutomatedEmail;
use App\Jobs\ScoreSheetEmail;
use App\Mail\sendAutomatedEmail;
use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Product;
use App\models\Rollover;
use App\models\RoTarget;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EmailedReportsController extends Controller
{
    public function skipped_payments_pdf($recipientID)
    {
        ini_set('max_execution_time', 180);

        $recipient = User::where(['id'=>$recipientID,'is_recipient'=> true])->first();
        if (!$recipient){
            return "You are not allowed to view this page";
        }
        if ($recipient->hasRole('admin') || $recipient->hasRole('accountant') || $recipient->hasRole('sector_manager')){
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder){
                $builder->where('amount', '!=',0);
            })->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->orderBy('id', 'desc')->get();
        }elseif ($recipient->hasRole('field_agent')){
            $loans_w_arrears = $recipient->loans()->whereHas('arrears', function (Builder $builder){
                $builder->where('amount', '!=',0);
            })->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->orderBy('id', 'desc')->get();
        }else{
            $recipient_branch = Branch::find($recipient->branch_id);
            $loans_w_arrears = $recipient_branch->loans()->whereHas('arrears', function (Builder $builder){
                $builder->where('amount', '!=',0);
            })->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->orderBy('id', 'desc')->get();
        }
        $today = Carbon::today()->format('Y-m-d');
        $lo_arr = array();

        $narok_skipped = 0;
        $narok_disb = 0;
        $narok_total = 0;
        $narok_arrears = 0;
        $narok_paid = 0;
        $narok_due = 0;

        $maua_skipped = 0;
        $maua_disb = 0;
        $maua_total = 0;
        $maua_arrears = 0;
        $maua_paid = 0;
        $maua_due = 0;

        $makutano_skipped = 0;
        $makutano_disb = 0;
        $makutano_total = 0;
        $makutano_arrears = 0;
        $makutano_paid = 0;
        $makutano_due = 0;
        foreach ($loans_w_arrears as $lns)
        {
            $last_payment_date = $lns->last_payment_date;
            $product = Product::find($lns->product_id);
            $skipped_installments = Installment::where(['loan_id'=>$lns->id, 'completed'=>false])->where('due_date', '<', $today)->get();
            $skipped_installments_count = count($skipped_installments);
            //next payment date
            $next_payment_date = Installment::where(['loan_id' => $lns->id, 'current' => true])->first()->due_date;
            //principle paid & principle due & Interest Paid , Interest Due
            $instals = Installment::where(['loan_id' => $lns->id])->get();
            $ppaid = 0;
            $Ipaid = 0;
            foreach ($instals as $instal) {
                if ($instal->amount_paid >= $instal->principal_amount) {
                    $ppaid += $instal->principal_amount;
                } else {
                    $ppaid += $instal->amount_paid;
                }
                if ($instal->amount_paid > $instal->principal_amount) {
                    $iP = $instal->amount_paid - $instal->principal_amount;
                    $Ipaid += $iP;
                }
            }
            $pdue = $lns->loan_amount - $ppaid;
            $loan_total_interest = $lns->loan_amount * ($product->interest / 100);
            $interest_due = $loan_total_interest - $Ipaid;
            //Loan Officer
            $Customer = Customer::find($lns->customer_id);
            $user = User::find($Customer->field_agent_id);
            $branch = Branch::find($Customer->branch_id);
            $branch = $branch->bname;
            $field_agent = $user->name;
            //Amount Paid
            $payments = Payment::where(['loan_id' => $lns->id, 'payment_type_id' => 1])->sum('amount');
            //TOTAL
            if ($lns->rolled_over) {
                $rollover = Rollover::where('loan_id',$lns->id)->first();
                $total = $lns->loan_amount + ($lns->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            } else {
                $total = $lns->loan_amount + ($lns->loan_amount * ($product->interest / 100));

            }
            //Balance
            $balance = $total - $payments;
            //Total Arrears
            $amount = 0;
            $arrears = Arrear::where('loan_id', $lns->id)->get();
            if ($arrears->first()) {
                foreach ($arrears as $arrear) {
                    $amount += $arrear->amount;
                }
            }
            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180))
            {
                if ($skipped_installments_count > 0){
                    $br_id = Branch::find($Customer->branch_id)->id;
                    if ($br_id == 1){
                        $narok_skipped += $skipped_installments_count;
                        $narok_disb += $lns->loan_amount;
                        $narok_total += $total;
                        $narok_arrears += $amount;
                        $narok_paid += $payments;
                        $narok_due += $balance;
                    }
                    elseif ($br_id == 2){
                        $maua_skipped += $skipped_installments_count;
                        $maua_disb += $lns->loan_amount;
                        $maua_total += $total;
                        $maua_arrears += $amount;
                        $maua_paid += $payments;
                        $maua_due += $balance;

                    }
                    elseif ($br_id == 3){
                        $makutano_skipped += $skipped_installments_count;
                        $makutano_disb += $lns->loan_amount;
                        $makutano_total += $total;
                        $makutano_arrears += $amount;
                        $makutano_paid += $payments;
                        $makutano_due += $balance;

                    }
                    array_push($lo_arr ,array(
                        'id'=>$lns->id,
                        'loan_account'=>$lns->loan_account,
                        'loan_amount'=>$lns->loan_amount,
                        'date_created'=>$lns->date_created,
                        'field_agent'=>$field_agent,
                        'branch'=>$branch,
                        'end_date'=>$lns->end_date,
                        'disbursement_date'=>$lns->disbursement_date,
                        'created_at'=>$lns->created_at,
                        'last_payment_date'=>$last_payment_date,
                        'purpose'=>$lns->purpose,
                        'phone'=>$lns->customer->phone,
                        'product_name'=>$product->product_name,
                        'installments'=>$product->installments,
                        'interest'=>$product->interest,
                        'owner'=>$lns->customer->fname ." ". $lns->customer->lname,
                        'skipped_installments'=>$skipped_installments_count,
                        'next_payment_date'=>$next_payment_date,
                        'principle_paid'=>$ppaid,
                        'principle_due'=>$pdue,
                        'interest_paid'=>$Ipaid,
                        'interest_due'=>$interest_due,
                        'amount_paid'=>$payments,
                        'total'=>$total,
                        'balance'=>$balance,
                        'total_arrears'=>$amount,
                    ));
                }
            }
        }
        $count = count($lo_arr);
        $branch_array = [
            "Narok" =>[
                "skipped"=>$narok_skipped,
                "disb"=>$narok_disb,
                "total"=>$narok_total,
                "arrears"=>$narok_arrears,
                "paid"=>$narok_paid,
                "due"=>$narok_due,
            ],
            "Maua" =>[
                "skipped"=>$maua_skipped,
                "disb"=>$maua_disb,
                "total"=>$maua_total,
                "arrears"=>$maua_arrears,
                "paid"=>$maua_paid,
                "due"=>$maua_due,
            ],
            "Makutano" =>[
                "skipped"=>$makutano_skipped,
                "disb"=>$makutano_disb,
                "total"=>$makutano_total,
                "arrears"=>$makutano_arrears,
                "paid"=>$makutano_paid,
                "due"=>$makutano_due,
            ],
        ];
        $pdf  = PDF::loadView('pdf.skipped-payments', ['lo'=>$lo_arr, 'count'=>$count, 'branch_array'=>$branch_array])->setPaper('a4', 'landscape');
        return $pdf->download('skipped-payments-'.$today.'.pdf');
    }

    public function perf_tracker()
    {
        $users = User::query()->role('field_agent')->where('status', true)->get();
        $branches = Branch::all();
        $arr = [];
        foreach ($users as $user){
            $ro_disb_target_sum = RoTarget::where('user_id', $user->id)->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now())->first()->disbursement_target;
            if ($ro_disb_target_sum > 0){
                $dis = $user->loans()->where('disbursed', true)->whereYear('disbursement_date', Carbon::now())->whereMonth('disbursement_date', Carbon::now())->count();
                $per = (int)(($dis / $ro_disb_target_sum) * 100);
            }else{
                $dis = $user->loans()->where('disbursed', true)->whereYear('disbursement_date', Carbon::now())->whereMonth('disbursement_date', Carbon::now())->count();
                $per = 0;
            }
            array_push($arr, array('CO'=>$user->name, 'sum'=>$ro_disb_target_sum, 'dis'=>$dis,  '%'=>$per));
        }
        $data =[];
        foreach ($branches as $branch){
            $cos = User::role('field_agent')->where('branch_id', $branch->id)->whereHas('RoTarget', function ($q) {
                $q->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now());
            })->get();
            $percentages = [];
            $arr_co = [];
            foreach ($cos as $co) {
                $ro_disb_target_sum = RoTarget::where('user_id', $co->id)->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now())->first()->disbursement_target;
                if ($ro_disb_target_sum > 0){
                    $dis = $co->loans()->where('disbursed', true)->whereYear('disbursement_date', Carbon::now())->whereMonth('disbursement_date', Carbon::now())->count();
                    $per = (int)(($dis / $ro_disb_target_sum) * 100);
                }else{
                    $per = 0;
                }
                array_push($percentages, $per);
                array_push($arr_co, $co->name);
            }

            if (count($percentages)>0){
                $avg = array_sum($percentages) / count($percentages);
            }else{
                $avg = 0;
            }
            array_push($data, ['br'=>$branch->bname, 'av%'=>$avg, 'cos'=>$arr_co]);
        }
        dd($data);
//        dispatch(new ScoreSheetEmail($users, $branches));
//        return "Job has been dispatched.";
    }
    public function testpdf()
    {
        $pdf = PDF::loadView('pdf.test');
        return $pdf->download('test.pdf');
    }

}
