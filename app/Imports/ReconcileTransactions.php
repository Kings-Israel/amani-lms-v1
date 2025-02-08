<?php

namespace App\Imports;

use App\Http\Controllers\MpesaPaymentController;
use App\models\Arrear;
use App\models\Customer;
use App\models\CustomerInteraction;
use App\models\CustomerInteractionCategory;
use App\models\Installment;
use App\models\Loan;
use App\models\Msetting;
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
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReconcileTransactions implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // $setting = Setting::first();

        foreach ($rows as $row) {
            $amount = (int) trim($row['paid_in']);
            if ($amount) {
                $details = explode(' ', $row['details']);
                $transaction_id = trim(str_replace(' ', '', $row['receipt_no']));
                $account_number = trim(str_replace(' ', '', $row['ac_no']));
                $paid_date = trim($row['completion_time']);
                $details = explode(' - ', $row['details'])[0];
                $get_phone = explode(' ', $details);
                $customer_phone = trim(end($get_phone));

                $setting = Setting::first();
                $phone_number = strlen(trim($account_number)) == 9 ? '254'.trim($account_number) : '254'.substr(trim($account_number), -9);

                $customer = Customer::where(['phone' => $phone_number])->first();

                $reg = Regpayment::where('transaction_id', $transaction_id)->first();
                $raw = Raw_payment::where('mpesaReceiptNumber', $transaction_id)->first();
                $payment = Payment::where('transaction_id', $transaction_id)->first();
                $reconciliation = DB::table('reconsiliation_transactions')->where('transaction_id', $transaction_id)->first();

                if (!$reg && !$raw && !$payment && !$reconciliation) {
                    if ($customer) {
                        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

                        $result = [
                            "amount" => $amount,
                            "mpesaReceiptNumber" => $transaction_id,
                            "customer_id" => $customer->id,
                            "transactionDate" => Carbon::parse($paid_date),
                            "phoneNumber" => $customer_phone
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
                                        "transaction_id" => $transaction_id,
                                    ]);

                                    //remaider after reg
                                    $remaiderafter_reg = (int) $amount + $remaining_reg;

                                    $this->rem_after_reg($transaction_id, $customer, $remaiderafter_reg, $paid_date);
                                } else if ($reg->amount == $setting->registration_fee) {
                                    /*************************if paid registration amount equal to set registration fee*****************/
                                    $reg_fee = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 3])->sum('amount');
                                    if ((int) $amount < (int) $loan->balance) {
                                        //amount remaining is less than or equal to loan amount
                                        $pay_loan = Payment::create([
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now("Africa/Nairobi"),
                                            'transaction_id' => $transaction_id,
                                            'amount' => $amount,
                                            'channel' => "MPESA",
                                            'payment_type_id' => 1,
                                        ]);
                                    } else {
                                        //amount remaining is greator than loan balance so put the remaining in reg fee account
                                        $over_pay = (int)$amount - (int)$loan->balance;

                                        $pay_loan = Payment::create([
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now("Africa/Nairobi"),
                                            'transaction_id' => $transaction_id,
                                            'amount' => $loan->balance,
                                            'channel' => "MPESA",
                                            'payment_type_id' => 1,
                                        ]);

                                        //set loan as paid
                                        Loan::find($loan->id)->update(['settled' => true]);
                                        $reg2 = Regpayment::where('customer_id', $customer->id)->first();
                                        $add_to_reg = $reg2->update([
                                            "amount" => $reg2->amount + $over_pay,
                                            "transaction_id" => $transaction_id,
                                        ]);
                                    }

                                    $this->handle_installments($loan, $amount, $paid_date);
                                } else {
                                    /*************************if paid registration amount is less than set registration fee*****************/
                                    $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;

                                    if ((int) $amount <= $remaining_reg) {
                                        $reg->update([
                                            'date_payed' => Carbon::now('Africa/Nairobi'),
                                            "amount" => (int)$reg->amount + $amount,
                                            "transaction_id" => $transaction_id,
                                        ]);
                                    } else {
                                        //more amount than registration
                                        $reg->update([
                                            'date_payed' => Carbon::now('Africa/Nairobi'),
                                            "amount" => (int)$setting->registration_fee,
                                            "transaction_id" => $transaction_id,
                                        ]);

                                        //remaider after reg
                                        $remaiderafter_reg = (int)$amount - $remaining_reg;
                                        $this->rem_after_reg($transaction_id, $customer, $remaiderafter_reg, $paid_date);
                                    }
                                }
                            }
                            /****************************************very unlickely to happen that customer have approved loan and he has not paid registration fee *********************/
                            else {
                                if ($amount <= (int)$setting->registration_fee) {
                                    $regi = Regpayment::create([
                                        'customer_id' => $customer->id,
                                        'date_payed' => Carbon::now('Africa/Nairobi'),
                                        "amount" => $amount,
                                        "transaction_id" => $transaction_id,
                                        "channel" => "MPESA",
                                    ]);
                                }
                                else {
                                    //more amount than registration
                                    $remaining_reg = (int)$amount - (int)$setting->registration_fee;

                                    $regi = Regpayment::create([
                                        'customer_id' => $customer->id,
                                        'date_payed' => Carbon::now('Africa/Nairobi'),
                                        "amount" => (int)$setting->registration_fee,
                                        "transaction_id" => $transaction_id,
                                        "channel" => "MPESA",
                                    ]);

                                    //remaider after reg
                                    $remaiderafter_reg = $remaining_reg;
                                    $this->rem_after_reg($transaction_id, $customer, $remaiderafter_reg, $paid_date);
                                }
                            }
                        } else {
                            //get the message
                            //meaning he has no active loan so check if registration fee is paid
                            $reg = Regpayment::where('customer_id', $customer->id)->first();
                            if ($reg) {
                                $reg->update([
                                    //'date_payed' => Carbon::now('Africa/Nairobi'),
                                    "amount" => $amount + $reg->amount,
                                    "transaction_id" => $transaction_id,
                                ]);
                            } else {
                                $regi = Regpayment::create([
                                    'customer_id' => $customer->id,
                                    'date_payed' => Carbon::now('Africa/Nairobi'),
                                    "amount" => $amount,
                                    "transaction_id" => $transaction_id,
                                    "channel" => "MPESA",
                                ]);
                            }
                        }

                        RepaymentMpesaTransaction::create($result);
                    } else {
                        $fname = trim(explode(' ', explode(' - ', $row['details'])[1])[0]);
                        $result2 = [
                            "amount" => $amount,
                            "mpesaReceiptNumber" => $transaction_id,
                            "customer" => $fname,
                            "phoneNumber" => $phone_number,
                            "BusinessShortCode" => '4123359',
                            "account_number" => $phone_number,
                        ];

                        $fnd = Raw_payment::where('mpesaReceiptNumber', $transaction_id)->first();
                        if (!$fnd){
                            Raw_payment::create($result2);
                        }
                    }
                }
            }
        }
    }

    //remaider after reg
    public function rem_after_reg($transaction_id, $customer, $remaiderafter_reg, $date)
    {
        $settings = Setting::first();

        $loan = Loan::where('customer_id', $customer->id)->first();

        if ($remaiderafter_reg <= $loan->balance) {
            //amount remaining is less than or equal to loan amount
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::parse($date),
                'transaction_id' => $transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
        } else {
            //amount remaining is greator than loan amount so put the remaining in reg fee account
            Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::parse($date),
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
                'date_payed' => Carbon::now('Africa/Nairobi'),
                "amount" => $reg2->amount + $over_pay,
                "transaction_id" => $transaction_id,
            ]);
        }

        // $this->handle_installments($loan, $remaiderafter_reg, $date);
    }

    public function handle_installments($loan, $rem, $date)
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
            $total_installments = Installment::where('loan_id','=', $loan->id)->count();
        }

        for ($i=$installment->position; $i<=$total_installments; $i++)
        {
            $instal = Installment::where(['position' => $i, 'loan_id' => $loan->id])->first();

            //additional check if installment exists
            if ($instal)
            {
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
                        $interest_payment_date = $date;
                    } else {
                        //  $interest_payment_date = $instal->interest_payment_date;
                        if ($instal->amount_paid >= $instal->interest){
                            $interest_payment_date = $instal->interest_payment_date;
                        } else {
                            $interest_payment_date = $date;
                        }
                    }

                    $instal->update([
                        'last_payment_date' => Carbon::parse($date),
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
                        $this->handle_preinteraction($insta_id, $arrear_id, $date);
                    } catch (Exception $e) {
                        info('some error happened on handling preinteraction');
                    }
                } elseif ($rem != 0 && $rem < $balance) {
                    if ($instal->in_arrear){

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
                            $interest_payment_date = $instal->amount_paid >= $instal->interest ? $instal->interest_payment_date : Carbon::now();
                        }
                    }

                    $instal->update([
                        'last_payment_date' => Carbon::parse($date),
                        'amount_paid' => $instal->amount_paid + $rem,
                        'interest_payment_date' => $interest_payment_date
                    ]);
                    $rem -= $rem;

                    //check if loan has been completed
                    if ($loan->settled){
                        //delete pre interaction if active
                        try {
                            $this->handle_preinteraction($insta_id, $arrear_id, $date);
                        } catch (Exception $e) {
                            info('some error happened on handling preinteraction on installment balance');
                        }
                    }
                }
            }
        }
    }

    public function handle_preinteraction($inst_id, $arrear_id, $date)
    {
        $cat = CustomerInteractionCategory::where(['name' => 'Prepayment'])->first();
        $due_cat = CustomerInteractionCategory::where(['name' => 'Due Collection'])->first();

        $pre = Pre_interaction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])->whereDate('due_date', '>=', Carbon::parse($date))->first();
        if ($pre) {
            $pre->delete();
        }

        $cat1 = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();
        if ($arrear_id != null){
            $pre1 = Pre_interaction::where(['model_id' => $arrear_id, 'interaction_category_id' => $cat1->id])->first();
            if ($pre1) {
                $pre1->delete();
            }
        }

        //check if we have any interaction under prepayment with this model id
        // $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])->first();
        $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])
            ->orWhere(function ($query) use ($due_cat, $inst_id) {
                $query->where('model_id', $inst_id)
                    ->where('interaction_category_id', $due_cat->id);
            })
            ->first();
        if ($int){
            //check installment due date if has been passed
            $installment = Installment::where(['id' => $inst_id])->whereDate('due_date', '>=', Carbon::parse($date))->first();
            if ($installment){
                //Means it was paid on time so mark as a success
                $int->update(['target' => 1, 'closed_by' => 19, 'closed_date'=>Carbon::parse($date), 'status' => 2]);
            } else {
                $int->update(['target' => 2, 'closed_by' => 19, 'closed_date'=>Carbon::parse($date), 'status' => 2]);
            }
        }
        //check if we have any interaction under arrear payment with this model id
        if ($arrear_id != null) {
            $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat1->id])->first();
            if ($int){
                //mark the interaction as success
                $int->update(['target' => 1, 'closed_by' => 19, 'closed_date'=>Carbon::parse($date), 'status' => 2]);
            }
        }
    }
}
