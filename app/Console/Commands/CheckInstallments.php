<?php

namespace App\Console\Commands;

use App\CustomerInteractionCategory;
use App\Jobs\Sms;
use App\models\Arrear;
use App\models\Customer;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\PendingRollover;
use App\models\Pre_interaction;
use App\models\Product;
use App\models\Branch;
use App\models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckInstallments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:installments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the current installments has been paid. If not create an arrear';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*************************handle arrears**********************************/
        $installments = Installment::where(['current' => true])->whereDate('due_date', Carbon::now())->get();

        foreach ($installments as $installment) {
            /**********************check if loan is cleared******************/
            $lon = Loan::where(['id' => $installment->loan_id, 'settled' => false])->first();
            if ($lon) {
                $customer = Customer::where('id', Loan::find($installment->loan_id)->customer_id)->first();

                $next_installment = Installment::where(['position' => $installment->position + 1, 'loan_id' => $installment->loan_id])->first();
                if ($installment->for_rollover) {
                    $inst = Installment::where(['loan_id' => $installment->loan_id, 'for_rollover' => false])->count();
                    $instB4rollover = Installment::where(['loan_id' => $installment->loan_id, 'for_rollover' => false])->sum('amount_paid');
                    $current_position = $installment->position - $inst;
                    $rollover_to_be_paid = $installment->total * $current_position;
                    $B4rollover_to_be_paid = $instB4rollover;

                    // $amount_to_be_paid = $installment->total * $current_position;
                    $amount_to_be_paid = $rollover_to_be_paid + $B4rollover_to_be_paid;

                    $payments = Payment::where(['loan_id' => $installment->loan_id, 'payment_type_id' => 1])->sum('amount');
                } else {
                    $amount_to_be_paid = $installment->position * $installment->total;
                    $payments = Payment::where(['loan_id' => $installment->loan_id, 'payment_type_id' => 1])->sum('amount');
                }

                //meaning the installment was not paid in time
                if ($payments < $amount_to_be_paid) {
                    $arrear_amount = $installment->total - $installment->amount_paid;

                    // Add arrear if loan is past end date
                    $arrears = Arrear::create([
                            'loan_id' => $installment->loan_id,
                            'amount' => $arrear_amount,
                            'installment_id' => $installment->id
                        ]);

                    //if there is a remaining installment
                    if ($next_installment) {
                        $next_payment = $installment->total + ($amount_to_be_paid - $payments);
                        $message = "Dear " . $customer->fname . " " . $customer->lname . ", you were supposed to pay your loan installment today but you did not. Pay earlier to avoid possible penalties.";
                        $next_installment->update(['current' => true]);
                        $installment->update(['current' => false, 'in_arrear' => true]);
                    } //meaning its the last installment
                    else {
                        $next_payment = $amount_to_be_paid - $payments;
                        // $message = "Dear Customer you have a balance of Ksh ".$next_payment ." which was due ".$installment->due_date. ". Pay your balance before ".date('Y-m-d', strtotime($installment->due_date. ' + 2 days'))  . " to avoid your loan being rolled over and attract a penalty of 20%";
                        $message = "Dear " . $customer->fname . " " . $customer->lname . ", you have a balance of Ksh " . $next_payment . " which was due on " . $installment->due_date . ". This is a reminder to kindly clear your loan balance.";
                        $installment->update(['in_arrear' => true]);
                    }

                    //add arrear to the pre interaction table
                    try {
                        $pre_interaction = $this->pre_interaction_arrears($arrears, $lon->customer_id);
                    } catch (Exception $e) {
                        info($e);
                    }
                } else {
                    //meaning installment was paid in time
                    if ($next_installment) {
                        $bal = $payments - $amount_to_be_paid;
                        $tobepaid = $next_installment->total - $bal;
                        $message = "Dear " . $customer->fname . " " . $customer->lname . ", thank you for your timely loan payment. Your next installment of Ksh. " . $tobepaid . " is due on " . $next_installment->due_date;
                        $next_installment->update(['current' => true]);
                    } else {
                        $message = "Dear " . $customer->fname . " " . $customer->lname . ", thank you for completing your LITSA CREDIT loan on time.";
                    }
                    $arrears = Arrear::where('loan_id', $installment->loan_id)->get();
                    foreach ($arrears as $arrear) {
                        $arrear->delete();
                    }
                    $installment->update(['current' => false]);
                }

                //disabled sending SMS
                $phone = '+254' . substr(Loan::find($installment->loan_id)->phone, -9) ;
                $user = Customer::where('id', Loan::find($installment->loan_id)->customer_id)->first();
                $user_type = false;
                $branch_id = Branch::where('bname', 'Bungoma')->first()->id;
                if ($user->branch_id == $branch_id) {
                    $fnd = dispatch(new Sms(
                      $phone, $message,$user,$user_type
                    ));
                }
            }
        }

        /*********************************handle rollover****************************/
        $loans = Loan::whereDate('end_date', '<', Carbon::now())->where(['settled' => false, 'rolled_over' => false])->get();
        foreach ($loans as $loan) {
            $phone = '+254' . substr($loan->phone, -9);
            $user = Customer::where('id', $loan->customer_id)->first();

            $dt = new Carbon($loan->end_date);

            if ($dt->format('Y-m-d') == Carbon::now()->format('Y-m-d')) {
                $balance = $loan->balance;
                $rollover_interest = $balance + (isset(Setting::first()->rollover_interest) ? (int)Setting::first()->rollover_interest : 100);
                $new_balance = $balance + $rollover_interest;

                //added to pending for easier tracking
                $pending_rollover = PendingRollover::create([
                    'loan_id' => $loan->id,
                    'amount' => $balance,
                    'rollover_interest' => $rollover_interest,
                    'rollover_due' => $new_balance,
                    'rollover_date' => Carbon::now(),
                ]);
            }
        }
    }

    public function pre_interaction_arrears($arrear, $customer_id)
    {
        $category = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();
        Pre_interaction::Create([
            'model_id' => $arrear->id,
            'amount' => $arrear->amount,
            'customer_id' => $customer_id,
            'interaction_category_id' => $category->id,
            'due_date' => Carbon::tomorrow(),
            'system_remark' => 'Kes ' . $arrear->amount . ' was due on ' . $arrear->installment->due_date . ' for loan number ' . $arrear->loan->loan_account
        ]);

        return 0;

    }
}
