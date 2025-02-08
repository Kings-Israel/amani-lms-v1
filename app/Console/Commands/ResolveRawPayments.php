<?php

namespace App\Console\Commands;

use App\Http\Controllers\MpesaPaymentController;
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
use App\models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResolveRawPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:resolve-raw-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = json_decode(Storage::get('/public/logs/laravel-2024-01-17.json'));
        foreach ($data as $key => $value) {
            $customer = Customer::where('id_no', trim($value->BillRefNumber))->first();
            if ($customer) {
                $reg = Regpayment::where('transaction_id', trim($value->TransID))->first();
                $pay = Payment::where('transaction_id', trim($value->TransID))->first();
                $rec = DB::table('reconsiliation_transactions')->where(['transaction_id' => trim($value->TransID)])->first();

                if ($rec || $reg || $pay) {
                    $raw_payment = Raw_payment::where('mpesaReceiptNumber', trim($value->TransID))->first();

                    if ($raw_payment) {
                        $raw_payment->delete();
                    }
                } else {
                    DB::table('reconsiliation_transactions')->insert([
                        'customer_id' => $customer->id,
                        'reconsiled_by' => 182,
                        'amount' => $value->TransAmount,
                        'transaction_id' => trim($value->TransID),
                        'phone_number' => $customer->phone,
                        'channel' => 'MPESA',
                        'date_paid' => '2024-01-17 12:00:00',
                        'created_at' => Carbon::now()
                    ]);

                    $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

                    if ($loan) {
                        //meaning has an active loan so first if he has paid reg fee
                        $reg = Regpayment::where('customer_id', $customer->id)->first();

                        $setting = Setting::first();
                        if ($reg) {
                            if ($reg->amount > $setting->registration_fee) {
                                //Registration amount is more than set registration
                                $remaining_reg = (int)$reg->amount - (int)$setting->registration_fee;
                                $reg->update([
                                    'date_payed' => '2024-01-17 12:00:00',
                                    "amount" => (int)$setting->registration_fee,
                                    "transaction_id" => trim($value->TransID),
                                ]);

                                //remaider after am
                                $remaiderafter_reg = (int)$value->TransAmount + $remaining_reg;
                                $this->rem_after_reg(trim($value->TransID), $customer, $remaiderafter_reg);
                            } elseif ($reg->amount == $setting->registration_fee) {
                                if ($value->TransAmount < $loan->balance) {
                                    //amount remaining is less than or equal to loan amount
                                    $pay_loan = Payment::create([
                                        'loan_id' => $loan->id,
                                        'date_payed' => '2024-01-17 12:00:00',
                                        'transaction_id' => trim($value->TransID),
                                        'amount' => $value->TransAmount,
                                        'channel' => 'MPESA',
                                        'payment_type_id' => 1,
                                    ]);
                                } else {
                                    //amount remaining is greator than loan balance so put the remaining in reg fee account
                                    $over_pay = $value->TransAmount - $loan->balance;

                                    $pay_loan = Payment::create([
                                        'loan_id' => $loan->id,
                                        'date_payed' => '2024-01-17 12:00:00',
                                        'transaction_id' => trim($value->TransID),
                                        'amount' => $loan->balance,
                                        'channel' => 'MPESA',
                                        'payment_type_id' => 1,
                                    ]);

                                    //set loan as paid
                                    Loan::find($loan->id)->update(['settled' => true]);
                                    $reg2 = Regpayment::where('customer_id', $customer->id)->first();
                                    $add_to_reg = $reg2->update([
                                        'date_payed' => '2024-01-17 12:00:00',
                                        "amount" => $reg2->amount + $over_pay,
                                        "transaction_id" => trim($value->TransID),
                                    ]);
                                }

                                $this->handle_installments($loan, $value->TransAmount);
                            } else {
                                /*************************if paid registration amount is less than set registration fee*****************/
                                $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;

                                if ($value->TransAmount <= $remaining_reg) {
                                    $reg->update([
                                        'date_payed' => Carbon::now('Africa/Nairobi'),
                                        "amount" => (int)$reg->amount + $value->TransAmount,
                                        "transaction_id" => trim($value->TransID),
                                    ]);
                                } else {
                                    //more amount than registration
                                    $reg->update([
                                        'date_payed' => '2024-01-17 12:00:00',
                                        "amount" => (int)$setting->registration_fee,
                                        "transaction_id" => trim($value->TransID),
                                    ]);

                                    //remaider after reg
                                    $remaiderafter_reg = (int)$value->TransAmount - (int)$remaining_reg;
                                    $this->rem_after_reg(trim($value->TransID), $customer, $remaiderafter_reg);
                                }
                            }
                        } else {
                            if ($value->TransAmount <= (int)$setting->registration_fee) {
                                $regi = Regpayment::create([
                                    'customer_id' => $customer->id,
                                    'date_payed' => '2024-01-17 12:00:00',
                                    "amount" => $value->TransAmount,
                                    "transaction_id" => trim($value->TransID),
                                    "channel" => 'MPESA',
                                ]);
                            } else {
                                //more amount than registration
                                $remaining_reg = (int)$value->TransAmount - (int)$setting->registration_fee;
                                $regi = Regpayment::create([
                                    'customer_id' => $customer->id,
                                    'date_payed' => '2024-01-17 12:00:00',
                                    "amount" => (int)$setting->registration_fee,
                                    "transaction_id" => trim($value->TransID),
                                    "channel" => 'MPESA',
                                ]);

                                //remaider after reg
                                $remaiderafter_reg = $remaining_reg;
                                $this->rem_after_reg(trim($value->TransID), $customer, $remaiderafter_reg);
                            }
                        }
                    } else {
                        //meaning he has no active loan so check if registration fee is paid
                        $reg = Regpayment::where('customer_id', $customer->id)->first();
                        if ($reg) {
                            $reg->update([
                                'date_payed' => '2024-01-17 12:00:00',
                                "amount" => $value->TransAmount + $reg->amount,
                                "transaction_id" => trim($value->TransID),
                            ]);
                        } else {
                            $regi = Regpayment::create([
                                'customer_id' => $customer->id,
                                'date_payed' => '2024-01-17 12:00:00',
                                "amount" => $value->TransAmount,
                                "transaction_id" => trim($value->TransID),
                                "channel" => 'MPESA',
                            ]);
                        }
                    }
                }
            } else {
                $raw_payment = Raw_payment::where('mpesaReceiptNumber', trim($value->TransID))->first();

                if(!$raw_payment) {
                    Raw_payment::create([
                        'amount' => $value->TransAmount,
                        'mpesaReceiptNumber' => trim($value->TransID),
                        'customer' => $value->FirstName,
                        'phoneNumber' => $value->MSISDN,
                        'BusinessShortCode' => $value->BusinessShortCode,
                        'account_number' => $value->BillRefNumber
                    ]);
                } else {
                    $raw_payment->update([
                        'account_number' => $value->BillRefNumber,
                    ]);
                }
            }

        }
    }

    public function rem_after_reg($transaction_id, $customer, $remaiderafter_reg)
    {
        $setting = Setting::first();

        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

        /********************if balance is less or equal to loan balance**************/
        if ($remaiderafter_reg < $loan->balance) {
            //amount remaining is less than or equal to loan balance
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => '2024-01-17 12:00:00',
                'transaction_id' => $transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
        } elseif ($remaiderafter_reg == $loan->balance) {
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => '2024-01-17 12:00:00',
                'transaction_id' => $transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
            Loan::find($loan->id)->update(['settled' => true]);
        } else {
            //amount remaining is greator than loan amount so put the remaining in reg fee account
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => '2024-01-17 12:00:00',
                'transaction_id' => $transaction_id,
                'amount' => (int)$loan->balance,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);

            //set loan as paid
            Loan::find($loan->id)->update(['settled' => true]);
            $over_pay = $remaiderafter_reg - $loan->balance;
            $reg2 = Regpayment::where('customer_id', $customer->id)->first();
            $add_to_reg = $reg2->update([
                //'date_payed' => Carbon::now('Africa/Nairobi'),
                "amount" => $reg2->amount + $over_pay,
                "transaction_id" => $transaction_id,
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
                        Log::info('some error happened on handling preinteraction');
                    }
                } elseif ($rem != 0 && $rem < $balance) {
                    if ($instal->in_arrear) {
                        $arrear = Arrear::where('installment_id', $instal->id)->first();
                        $arrear_id = $arrear->id;
                        // check if amount paid is greater than arrear
                        $check = $arrear->amount - $rem;
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
                            if ($instal->amount_paid >= $instal->interest){
                                $interest_payment_date = $instal->interest_payment_date;
                            }
                            else{
                                $interest_payment_date = Carbon::now();
                            }
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
                            Log::info('some error happened on handling preinteraction on installment balance');
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
