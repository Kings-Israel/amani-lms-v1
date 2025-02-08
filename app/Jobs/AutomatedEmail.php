<?php

namespace App\Jobs;

use App\Mail\sendAutomatedEmail;
use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Product;
use App\models\Rollover;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class AutomatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;

    public $lo;
    public $recipient;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lo, User $recipient)
    {
        $this->lo = $lo;
        $this->recipient = $recipient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
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
        foreach ($this->lo as $lns)
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
            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90))
            {
                if ($skipped_installments_count > 1){
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

        $email = new sendAutomatedEmail($lo_arr, $count, $branch_array);

        //will add array of recipient emails here
//        Mail::to(['mukhami@deveint.com'])->queue($email);
        Mail::to([$this->recipient->recipient_email])->queue($email);

    }

}
