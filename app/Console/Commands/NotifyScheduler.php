<?php

namespace App\Console\Commands;

use App\Jobs\Sms;
use App\models\Customer;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @throws \Exception
     */
    public function handle()
    {
        //updated scheduler
        //updated scheduler
        $installments = Installment::with('loan')
            ->where(['current' => true, 'completed' => false])
            ->whereDate('due_date', Carbon::now()->addDays(1))
            ->whereHas('loan', function ($query) {
                $query->where('loan_type_id', 2);
            })
            ->get();
        if (count($installments) > 0) {
            foreach ($installments as $installment) {
                $lon = Loan::where(['id' => $installment->loan_id, 'settled' => false])->first()->setAppends(['balance']);
                $cus = Customer::where('id', '=', $lon->customer_id)
                    ->select('id', 'fname', 'lname', 'phone', 'field_agent_id', 'branch_id')
                    ->first();
                $lf = User::find($cus->field_agent_id)->name;
                if ($lon && $cus && $lf) {
                    $amount_to_be_paid = $installment->total - $installment->amount_paid;
                    if ($amount_to_be_paid > 0) {
                        $message = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Pay your due of Ksh. ' . number_format($amount_to_be_paid) . ' that is due tomorrow. Your total balance is Ksh. ' . number_format($lon->balance) . "\r\n" . ' Paybill 6636959, Account Number 254' . substr($cus->phone, -9) . ' ' . "\r\n" . 'Your CO ' . $lf . '. Ref: ' . $installment->id;
                        if (isset($message)) {
                            $phone = '+254' . substr($cus->phone, -9);
                            $user = Customer::find($cus->id);
                            $user_type = false;
                            dispatch(new Sms($phone, $message, $user, $user_type));
                        }
                    }
                }
            }
        }
    }
}
