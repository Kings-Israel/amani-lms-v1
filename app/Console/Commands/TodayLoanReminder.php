<?php

namespace App\Console\Commands;

use App\Jobs\Sms;
use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\Installment;
use App\models\Loan;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TodayLoanReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind:loan_due_today';

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
        $installments = Installment::where(['current' => true, 'completed' => false])->whereDate('due_date' , Carbon::now())->get();

        foreach ($installments as $installment) {
            /**********************check if loan is cleared******************/
            $lon = Loan::where(['id' => $installment->loan_id, 'settled' => false])->first();
            //$cus =  Customer::where('id', '=', $lon->customer_id)->whereIn('branch_id', [4,5])->first();
            $cus =  Customer::where('id', '=', $lon->customer_id)->first();

            $lf = User::find($cus->field_agent_id);
            if ($lon and $cus){

                $amount_to_be_paid = $installment->total - $installment->amount_paid;

                if ($amount_to_be_paid > 0) {
                    $days = now()->diffInDays(Carbon::parse($installment->loan->end_date));
                    $end_date = Carbon::parse($installment->loan->end_date)->format('d M Y');

                    $arrears_amount = Arrear::where('loan_id', $lon->id)->sum('amount');

                    $balance = $amount_to_be_paid + $arrears_amount;

                    $deduct_installment = [
                        '254725584384',
                        '254791835504',
                        '254794093162',
                        '254797527218',
                        '254757913019',
                        '254705552874',
                        '254743166101',
                        '254748750941',
                        '254759716752',
                        '254759783485',
                        '254769609643',
                        '254741618857',
                        '254110277088',
                        '254112458461',
                        '254759249504',
                        '254717639709',
                        '254705534691',
                        '254703404122',
                        '254724693706',
                        '254719632132',
                        '254723893249',
                        '254710991913',
                        '254757195149',
                        '254746274613',
                        '254796795144',
                        '254743081829',
                        '254748440949',
                        '254791937696',
                        '254708767655',
                        '254741634399',
                        '254768740493',
                        '254741115227'
                    ];

                    if (collect($deduct_installment)->contains($cus->phone)) {
                        $balance = $balance - $installment->total;
                    }

                    // $message = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Ksh. ' . number_format($balance) . ' was due today. Pay now to avoid penalties. Outstanding loan balance Ksh. ' . number_format($lon->balance). ', days remaining on the loan is '.$days.' and final payment date is '.$end_date.'. "\r\n". LITSA CREDITS';
                    // $message = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Ksh. ' . number_format($balance) . ' is due today. Pay now to avoid penalties. Outstanding loan balance Ksh. ' . number_format($lon->balance). ', Days remaining on the loan is '.$days.'. "\r\n". LITSA CREDITS';
                    $message = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Ksh. ' . number_format($balance) . ' is due today. Pay now to avoid penalties. Outstanding loan balance Ksh. ' . number_format($lon->balance). ', Days remaining on the loan is '.$days.' and final payment date is '.$end_date.'. "\r\n". LITSA CREDITS';

                }
                if(isset($message)){
                    $branch_id = Branch::whereIn('bname', ['Bungoma', 'Homabay', 'Siaya', 'Busia', 'Migori', 'Kakamega'])->pluck('id');
                    $l = Loan::find($installment->loan_id);

                    // $field_agents = [221, 230, 252, 263, 219, 271, 245, 273, 244, 276, 268, 279, 274, 280, 283, 287, 221, 269, 233, 253, 251, 272, 270, 186, 237, 224, 281];

                    $exclude_users = [
                        '254729657866',
                        '254748004625',
                        '254743997001',
                        '254704887488',
                        '254714199569',
                        '254712696779',
                        '254708312495',
                        '254703333405',
                        '254705639862',
                        '254718738033',
                        '254748695270',
                        '254721919438',
                        '254798690411',
                        '254705554325',
                        '254748750941',
                        '254723220302',
                        '254743166101',
                        '254794093162',
                        '254729482251',
                        '254797527218',
                        '254748367338',
                        '254704289993',
                        '254759488549',
                        '254757913019',
                        '254706067688',
                        '254712647370',
                        '254708119634',
                        '254746299397',
                        '254708729768',
                        '254799922672',
                        '254114321669',
                        '254792286108',
                        '254726568594',
                        '254702984352',
                        '254759448909',
                        '254111664191',
                        '254713674854'
                    ];

                    $user = Customer::where('id', $l->customer_id)->whereIn('branch_id', $branch_id)->whereNotIn('phone', $exclude_users)->first();

                    if ($user) {
                        $aphone = '+254' . substr($cus->phone, -9);
                        $auser = $cus;
                        $suser_type = false;
                        // dispatch(new Sms(
                        //     $aphone, $message, $auser, $suser_type
                        // ));
                    }
                }
            }
        }
    }
}
