<?php

namespace App\Console\Commands;

use App\models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Installment;
use App\models\Loan;
use App\models\Payment;
use Illuminate\Support\Facades\Storage;

class UpdateLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:loans';

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
     */
    public function handle()
    {
        $loans = Loan::where('disbursed', true)->get();
        $setting = Setting::query()->first();

        foreach ($loans as $loan) {
            $loan_product = DB::table('products')->where(['id' => $loan->product_id])->first();

            // if ($loan->rolled_over) {
            //     $rollover = DB::table('rollovers')->where(['loan_id' => $loan->id])->first();
            //     if ($rollover) {
            //         $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100)) + $rollover->rollover_interest;
            //     } else {
            //         $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100));
            //     }
            // } elseif ($loan->has_lp_fee) {
            //     if ($setting->lp_fee) {
            //         $lp_fee = $setting->lp_fee;
            //     } else {
            //         $lp_fee = 0;
            //     }
            //     $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100)) + $lp_fee;
            // } else {
            //     $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100));
            // }
            $total_amount_paid = DB::table('payments')->where(['loan_id' => $loan->id, 'payment_type_id' => 1])->sum('amount');

            // DB::table('loans')->where(['id' => $loan->id])->update(['total_amount' => $total, 'total_amount_paid' => $total_amount_paid]);
            DB::table('loans')->where(['id' => $loan->id])->update(['total_amount_paid' => $total_amount_paid]);

            // get first installment of each loan
            // $installment = Installment::where('loan_id', $loan->id)->first();
            // if ($installment) {
            //     $end_date = Carbon::parse($installment->start_date)->addDays($loan_product->installments)->format('Y-m-d');
            //     $loan->update(['end_date' => $end_date]);
            // }
        }

        // $payments = Payment::all();
        // $duplicates = [];
        // foreach ($payments as $payment) {
        //     $duplicate = Payment::where('transaction_id', $payment->transaction_id)->where('payment_type_id', $payment->payment_type_id)->where('id', '!=', $payment->id)->count();
        //     if ($duplicate > 0) {
        //         array_push($duplicates, $payment->transaction_id);
        //     }
        // }

        // Storage::disk('public')->put('duplicates.json', json_encode($duplicates));

        return 'done';
    }
}
