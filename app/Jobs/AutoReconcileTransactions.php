<?php

namespace App\Jobs;

use App\models\Arrear;
use App\models\Customer;
use App\models\CustomerInteraction;
use App\models\CustomerInteractionCategory;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Pre_interaction;
use App\models\Product;
use App\models\Raw_payment;
use App\models\Regpayment;
use App\models\RepaymentMpesaTransaction;
use App\models\Setting;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AutoReconcileTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $raw_payments = Raw_payment::get();

            foreach ($raw_payments as $raw_payment) {
                $customer = Customer::where('phone', '254'.substr($raw_payment->account_number, -9))->first();

                if ($customer) {
                    $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();
                    $lon = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

                    $result = [
                        "amount" => $raw_payment->amount,
                        "mpesaReceiptNumber" => $raw_payment->mpesaReceiptNumber,
                        "customer_id" => $customer->id,
                        "transactionDate" => Carbon::now('Africa/Nairobi'),
                        "phoneNumber" => $raw_payment->account_number
                    ];

                    if ($loan) {
                        $reg = Regpayment::where('customer_id', $customer->id)->first();
                        $setting = Setting::first();
                        if ($reg) {
                            if ($reg->amount > $setting->registration_fee) {
                                //Registration amount is more than set registration
                                $remaining_reg = (int)$reg->amount - (int)$setting->registration_fee;

                                $reg->update([
                                    "amount" => (int)$setting->registration_fee,
                                    "transaction_id" => $raw_payment->mpesaReceiptNumber,
                                ]);

                                //remaider after reg
                                $remaiderafter_reg = (int) $raw_payment->amount + $remaining_reg;

                                $this->rem_after_reg($raw_payment, $customer, $remaiderafter_reg);
                            } else if ($reg->amount == $setting->registration_fee) {
                                /*************************if paid registration amount equal to set registration fee*****************/
                                $reg_fee = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 3])->sum('amount');
                                if ((int) $raw_payment->amount < (int) $loan->balance) {
                                    //amount remaining is less than or equal to loan amount
                                    $pay_loan = Payment::create([
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now("Africa/Nairobi"),
                                        'transaction_id' => $raw_payment->mpesaReceiptNumber,
                                        'amount' => $raw_payment->amount,
                                        'channel' => "MPESA",
                                        'payment_type_id' => 1,
                                    ]);
                                } else {
                                    //amount remaining is greator than loan balance so put the remaining in reg fee account
                                    $over_pay = (int)$raw_payment->amount - (int)$loan->balance;

                                    $pay_loan = Payment::create([
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now("Africa/Nairobi"),
                                        'transaction_id' => $raw_payment->mpesaReceiptNumber,
                                        'amount' => $loan->balance,
                                        'channel' => "MPESA",
                                        'payment_type_id' => 1,
                                    ]);

                                    //set loan as paid
                                    Loan::find($loan->id)->update(['settled' => true]);
                                    $reg2 = Regpayment::where('customer_id', $customer->id)->first();
                                    $add_to_reg = $reg2->update([
                                        "amount" => $reg2->amount + $over_pay,
                                        "transaction_id" => $raw_payment->mpesaReceiptNumber,
                                    ]);
                                }

                                $this->handle_installments($loan, $raw_payment->amount);
                            } else {
                                /*************************if paid registration amount is less than set registration fee*****************/
                                $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;

                                if ((int) $raw_payment->amount <= $remaining_reg) {
                                    $reg->update([
                                        'date_payed' => Carbon::now('Africa/Nairobi'),
                                        "amount" => (int)$reg->amount + $raw_payment->amount,
                                        "transaction_id" => $raw_payment->mpesaReceiptNumber,
                                    ]);
                                } else {
                                    //more amount than registration
                                    $reg->update([
                                        'date_payed' => Carbon::now('Africa/Nairobi'),
                                        "amount" => (int)$setting->registration_fee,
                                        "transaction_id" => $raw_payment->mpesaReceiptNumber,
                                    ]);

                                    //remaider after reg
                                    $remaiderafter_reg = (int)$raw_payment->amount - $remaining_reg;
                                    $this->rem_after_reg($raw_payment, $customer, $remaiderafter_reg);
                                }
                            }
                        }
                        /****************************************very unlickely to happen that customer have approved loan and he has not paid registration fee *********************/
                        else {
                            if ($raw_payment->amount <= (int)$setting->registration_fee) {
                                $regi = Regpayment::create([
                                    'customer_id' => $customer->id,
                                    'date_payed' => Carbon::now('Africa/Nairobi'),
                                    "amount" => $raw_payment->amount,
                                    "transaction_id" => $raw_payment->mpesaReceiptNumber,
                                    "channel" => "MPESA",
                                ]);
                            }
                            else {
                                //more amount than registration
                                $remaining_reg = (int)$raw_payment->amount - (int)$setting->registration_fee;

                                $regi = Regpayment::create([
                                    'customer_id' => $customer->id,
                                    'date_payed' => Carbon::now('Africa/Nairobi'),
                                    "amount" => (int)$setting->registration_fee,
                                    "transaction_id" => $raw_payment->mpesaReceiptNumber,
                                    "channel" => "MPESA",
                                ]);

                                //remaider after reg
                                $remaiderafter_reg = $remaining_reg;
                                $this->rem_after_reg($raw_payment, $customer, $remaiderafter_reg);
                            }
                        }
                    } else {
                        //get the message
                        //meaning he has no active loan so check if registration fee is paid
                        $reg = Regpayment::where('customer_id', $customer->id)->first();
                        if ($reg) {
                            $reg->update([
                                //'date_payed' => Carbon::now('Africa/Nairobi'),
                                "amount" => $raw_payment->amount + $reg->amount,
                                "transaction_id" => $raw_payment->mpesaReceiptNumber,
                            ]);
                        } else {
                            $regi = Regpayment::create([
                                'customer_id' => $customer->id,
                                'date_payed' => Carbon::now('Africa/Nairobi'),
                                "amount" => $raw_payment->amount,
                                "transaction_id" => $raw_payment->mpesaReceiptNumber,
                                "channel" => "MPESA",
                            ]);
                        }
                    }

                    $transaction = RepaymentMpesaTransaction::create($result);

                    $raw_payment->delete();
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            info($th);
            DB::rollBack();
        }
    }

    //remaider after reg
    public function rem_after_reg($raw_payment, $customer, $remaiderafter_reg)
    {
        $setting = Setting::first();

        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

        /********************if balance is less or equal to loan balance**************/
        if ((int)$remaiderafter_reg < $loan->balance) {
            //amount remaining is less than or equal to loan balance
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::now("Africa/Nairobi"),
                'transaction_id' => $raw_payment->mpesaReceiptNumber,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
        } elseif ((int)$remaiderafter_reg == $loan->balance) {
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::now("Africa/Nairobi"),
                'transaction_id' => $raw_payment->mpesaReceiptNumber,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
            Loan::find($loan->id)->update(['settled' => true]);
        } else {
            //amount remaining is greator than loan amount so put the remaining in reg fee account
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::now("Africa/Nairobi"),
                'transaction_id' => $raw_payment->mpesaReceiptNumber,
                'amount' => (int)$loan->balance,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);

            //set loan as paid
            Loan::find($loan->id)->update(['settled' => true]);

            $over_pay = $remaiderafter_reg - $loan->balance;

            $reg2 = Regpayment::where('customer_id', $customer->id)->first();

            $add_to_reg = $reg2->update([
                "amount" => $reg2->amount + $over_pay,
                "transaction_id" => $raw_payment->mpesaReceiptNumber,
            ]);
        }

        $this->handle_installments($loan, $remaiderafter_reg);
    }

    public function handle_installments($loan, $rem)
    {
        $installment = Installment::where(['loan_id' => $loan->id, 'being_paid' => true])->first();
        $product = Product::where('id', $loan->product_id)->first();
        if ($installment->for_rollover){
            if ($loan->loan_type_id == 1) {
                $total_installments = $product->duration * 2;
            } else {
                $total_installments = $product->installments * 2;
            }
        } else {
            if ($loan->loan_type_id == 1){
                $total_installments = $product->duration;
            } else {
                $total_installments = $product->installments;
            }
        }

        //put into consideration restructured loans
        if ($loan->restructured == true){
            $total_installments = Installment::where('loan_id', '=', $loan->id)->count();
        }

        for ($i=$installment->position; $i<=$total_installments; $i++)
        {
            $instal = Installment::where(['position' => $i, 'loan_id' => $loan->id])->first();

            //additional check if installment exists
            if ($instal) {
                $insta_id = $instal->id;

                $balance = $instal->total - $instal->amount_paid;
                //$rem -= $balance;
                $rem2 = $rem - $balance;
                $arrear_id = null;

                if ($rem >= $balance){
                    //check installments is in arrears
                    if ($instal->in_arrear){
                        $arrear = Arrear::where(['installment_id' => $instal->id])->first();
                        if ($arrear){
                            $arrear_id = $arrear->id;
                            $arrear->delete();
                        }
                    }
                    if ($instal->interest_payment_date == null) {
                        $interest_payment_date = Carbon::now();
                    } else {
                        //  $interest_payment_date = $instal->interest_payment_date;
                        if ($instal->amount_paid >= $instal->interest){
                            $interest_payment_date = $instal->interest_payment_date;
                        } else {
                            $interest_payment_date = Carbon::now();
                        }
                    }

                    $instal->update([
                        'last_payment_date' => Carbon::now(),
                        'amount_paid' => $instal->total,
                        'in_arrear' => false,
                        'being_paid' => false,
                        'completed' => true,
                        'interest_payment_date' => $interest_payment_date
                    ]);

                    $next = Installment::where(['position' => $i + 1, 'loan_id' => $loan->id])->first();

                    if ($next){
                        $next->update([
                            'being_paid' => true
                        ]);
                    }

                    $rem -= $balance;

                    //delete pre interaction if active
                    try {
                        $this->handle_preinteraction($insta_id, $arrear_id);
                    } catch (Exception $e) {
                        info('some error happened on handling preinteraction');
                    }
                } elseif ($rem != 0 && $rem < $balance) {
                    if ($instal->in_arrear) {
                        $arrear = Arrear::where('installment_id', $instal->id)->first();
                        $arrear_id = $arrear->id;
                        //check if amount paid is greater than arrear
                        // $check = $arrear->amount - $rem;
                        $arrear->update([
                            'amount' => $arrear->amount - $rem
                        ]);
                    }
                    $rm = $instal->amount_paid + $rem;
                    // $interest_payment_date = Carbon::now();
                    $interest_payment_date = $instal->interest_payment_date;

                    if ($rm >= $instal->interest){
                        if ($instal->interest_payment_date == null){
                            $interest_payment_date = Carbon::now();
                        } else {
                            // if ($instal->amount_paid >= $instal->interest){
                            //     $interest_payment_date = $instal->interest_payment_date;
                            // }
                            // else{
                            //     $interest_payment_date = Carbon::now();
                            // }
                            $interest_payment_date = $instal->amount_paid >= $instal->interest ? $instal->interest_payment_date : Carbon::now();
                        }
                    }

                    $instal->update([
                        'last_payment_date' => Carbon::now(),
                        'amount_paid' => $instal->amount_paid + $rem,
                        'interest_payment_date' => $interest_payment_date
                    ]);
                    $rem -= $rem;

                    //check if loan has been completed
                    if ($loan->settled){
                        //delete pre interaction if active
                        try {
                            $this->handle_preinteraction($insta_id, $arrear_id);
                        } catch (Exception $e) {
                            info('some error happened on handling preinteraction on installment balance');
                        }
                    }
                }
            }
        }
    }

    public function handle_preinteraction($inst_id, $arrear_id)
    {
        $cat = CustomerInteractionCategory::where(['name' => 'Prepayment'])->first();
        $due_cat = CustomerInteractionCategory::where(['name' => 'Due Collection'])->first();

        $pre = Pre_interaction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])->whereDate('due_date', '>=', Carbon::now())->first();
        if ($pre){

            $pre->delete();
        }

        $cat1 = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();
        if ($arrear_id != null){
            $pre1 = Pre_interaction::where(['model_id' => $arrear_id, 'interaction_category_id' => $cat1->id])->first();
            if ($pre1){

                $pre1->delete();
            }
        }

        //check if we have any interaction under prepayment with this model id
       // $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])->first();
        $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])
            ->orWhere(function ($query) use ($due_cat, $inst_id) {
                $query->where('model_id', $inst_id)->where('interaction_category_id', $due_cat->id);
            })
            ->first();
        if ($int){
            //check installment due date if has been passed
            $installment = Installment::where(['id' => $inst_id])->whereDate('due_date', '>=', Carbon::now())->first();
            if ($installment) {
                //Means it was paid on time so mark as a success
                $int->update(['target' => 1, 'closed_by' => 19, 'closed_date'=>Carbon::now(), 'status' => 2]);
            } else {
                $int->update(['target' => 2, 'closed_by' => 19, 'closed_date'=>Carbon::now(), 'status' => 2]);
            }
        }
        //check if we have any interaction under arrear payment with this model id
        if ($arrear_id != null) {
            $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat1->id])->first();
            if ($int){
                //mark the interaction as success
                $int->update(['target' => 1, 'closed_by' => 19, 'closed_date'=>Carbon::now(), 'status' => 2]);
            }
        }
    }
}
