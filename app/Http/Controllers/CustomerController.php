<?php

namespace App\Http\Controllers;

use App\CustomerInteractionCategory;
use App\models\Arrear;
use App\models\Customer;
use App\models\CustomerInteraction;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Pre_interaction;
use App\models\Product;
use App\models\Regpayment;
use App\models\RepaymentMpesaTransaction;
use App\models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function refactorLoan(Request $request)
    {
        $loan = Loan::find($request->loan_id);
        $customer = Customer::find($loan->customer_id);
        $loan->update([
            'total_amount_paid' => 0,
            'settled' => false
        ]);

        Payment::where('loan_id', $loan->id)->delete();
        Installment::where('loan_id', $loan->id)->delete();
        Arrear::where('loan_id', $loan->id)->delete();
        Regpayment::where('customer_id', $loan->customer_id)->delete();
        DB::table('reconsiliation_transactions')->where('customer_id', $loan->customer_id)->delete();

        $start_date = Carbon::parse($loan->disbursement_date)->addDays(2);

        $settings = Setting::first();

        //create installments
        $product = Product::find($loan->product_id);

        $lp_fee = 0;
        if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
        {
            //break down loans to daily installments based on product duration
            $principle_amount = round($loan->total_amount / $product->duration);
            $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
            if ($settings->lp_fee){
                $lp_fee = $settings->lp_fee / $product->duration;
            }
        }
        else //WEEKLY REPAYMENTS
        {
            $principle_amount = round($loan->total_amount / $product->installments);
            $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
            if ($settings->lp_fee){
                $lp_fee = $settings->lp_fee / $product->installments;
            }
        }

        $amountPayable = $principle_amount;

        $days = 0;

        if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
        {
            for ($i = 0; $i < $product->duration; $i++) {
                $days = $days + 1;
                $start_date_formatted = Carbon::parse($start_date)->subDays(1)->addDays($days);
                if ($i == 0) {
                    Installment::create([
                        "loan_id" => $loan->id,
                        "principal_amount" => $principle_amount,
                        "total" => $amountPayable,
                        "interest" => $interest_payable,
                        "lp_fee" => $lp_fee,
                        "due_date" => $start_date ? Carbon::parse($start_date)->subDays(1)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                        "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                        "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                        "being_paid" => true,
                        "amount_paid" => 0,
                        "position" => $i + 1
                    ]);
                } else {
                    Installment::create([
                        "loan_id" => $loan->id,
                        "principal_amount" => $principle_amount,
                        "total" => $amountPayable,
                        "interest" => $interest_payable,
                        "lp_fee" => $lp_fee,
                        "due_date" => $start_date ? Carbon::parse($start_date)->subDays(1)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                        "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                        "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                        "amount_paid" => 0,
                        "position" => $i + 1
                    ]);
                }
            }
        } else {
            for ($i = 0; $i < $product->installments; $i++) {
                $days = $days + 7;
                $start_date_formatted = Carbon::parse($start_date)->subDays(1)->addDays($days);
                if ($i == 0) {
                    Installment::create([
                        "loan_id" => $loan->id,
                        "principal_amount" => $principle_amount,
                        "total" => $amountPayable,
                        "interest" => $interest_payable,
                        "lp_fee" => $lp_fee,
                        "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                        "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                        "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                        "being_paid" => true,
                        "amount_paid" => 0,
                        "position" => $i + 1
                    ]);
                } else {
                    Installment::create([
                        "loan_id" => $loan->id,
                        "principal_amount" => $principle_amount,
                        "total" => $amountPayable,
                        "interest" => $interest_payable,
                        "lp_fee" => $lp_fee,
                        "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                        "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                        "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                        "amount_paid" => 0,
                        "position" => $i + 1
                    ]);
                }
            }
        }

        // $repayment_transactions = RepaymentMpesaTransaction::where('customer_id', $loan->customer_id)->get();

        // foreach ($repayment_transactions as $repayment_transaction) {
        //     $result = [
        //         "amount" => $repayment_transaction->amount,
        //         "mpesaReceiptNumber" => $repayment_transaction->mpesaReceiptNumber,
        //         "customer_id" => $repayment_transaction->customer_id,
        //         "transactionDate" => $repayment_transaction->transactionDate,
        //         "phoneNumber" => $repayment_transaction->phoneNumber
        //     ];

        //     $reg = Regpayment::where('customer_id', $loan->customer_id)->first();
        //     $setting = Setting::first();
        //     if ($reg) {
        //         if ($reg->amount > $setting->registration_fee) {
        //             //Registration amount is more than set registration
        //             $remaining_reg = (int)$reg->amount - (int)$setting->registration_fee;

        //             $reg->update([
        //                 "amount" => (int)$setting->registration_fee,
        //                 "transaction_id" => $repayment_transaction->mpesaReceiptNumber,
        //             ]);

        //             //remaider after reg
        //             $remaiderafter_reg = (int)$repayment_transaction->amount + $remaining_reg;

        //             $this->rem_after_reg($repayment_transaction, $customer, $remaiderafter_reg);
        //         } else if ($reg->amount == $setting->registration_fee) {
        //             /*************************if paid registration amount equal to set registration fee*****************/
        //             $reg_fee = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 3])->sum('amount');
        //             if ((int) $repayment_transaction->amount < (int) $loan->balance) {
        //                 //amount remaining is less than or equal to loan amount
        //                 $pay_loan = Payment::create([
        //                     'loan_id' => $loan->id,
        //                     'date_payed' => Carbon::now("Africa/Nairobi"),
        //                     'transaction_id' => $repayment_transaction->mpesaReceiptNumber,
        //                     'amount' => $repayment_transaction->amount,
        //                     'channel' => "MPESA",
        //                     'payment_type_id' => 1,
        //                 ]);
        //             } else {
        //                 //amount remaining is greator than loan balance so put the remaining in reg fee account
        //                 $over_pay = (int)$repayment_transaction->amount - (int)$loan->balance;

        //                 $pay_loan = Payment::create([
        //                     'loan_id' => $loan->id,
        //                     'date_payed' => Carbon::now("Africa/Nairobi"),
        //                     'transaction_id' => $repayment_transaction->mpesaReceiptNumber,
        //                     'amount' => $loan->balance,
        //                     'channel' => "MPESA",
        //                     'payment_type_id' => 1,
        //                 ]);

        //                 //set loan as paid
        //                 Loan::find($loan->id)->update(['settled' => true]);
        //                 $reg2 = Regpayment::where('customer_id', $customer->id)->first();
        //                 $add_to_reg = $reg2->update([
        //                     "amount" => $reg2->amount + $over_pay,
        //                     "transaction_id" => $repayment_transaction->mpesaReceiptNumber,
        //                 ]);
        //             }

        //             $this->handle_installments($loan, $repayment_transaction->amount);
        //         } else {
        //             /*************************if paid registration amount is less than set registration fee*****************/
        //             $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;

        //             if ((int) $repayment_transaction->amount <= $remaining_reg) {
        //                 $reg->update([
        //                     'date_payed' => Carbon::now('Africa/Nairobi'),
        //                     "amount" => (int)$reg->amount + $repayment_transaction->amount,
        //                     "transaction_id" => $repayment_transaction->mpesaReceiptNumber,
        //                 ]);
        //             } else {
        //                 //more amount than registration
        //                 $reg->update([
        //                     'date_payed' => Carbon::now('Africa/Nairobi'),
        //                     "amount" => (int)$setting->registration_fee,
        //                     "transaction_id" => $repayment_transaction->mpesaReceiptNumber,
        //                 ]);

        //                 //remaider after reg
        //                 $remaiderafter_reg = (int)$repayment_transaction->amount - $remaining_reg;
        //                 $this->rem_after_reg($repayment_transaction, $customer, $remaiderafter_reg);
        //             }
        //         }
        //     }
        //     /****************************************very unlickely to happen that customer have approved loan and he has not paid registration fee *********************/
        //     else {
        //         if ($repayment_transaction->amount <= (int)$setting->registration_fee) {
        //             $regi = Regpayment::create([
        //                 'customer_id' => $customer->id,
        //                 'date_payed' => Carbon::now('Africa/Nairobi'),
        //                 "amount" => $repayment_transaction->amount,
        //                 "transaction_id" => $repayment_transaction->mpesaReceiptNumber,
        //                 "channel" => "MPESA",
        //             ]);
        //         }
        //         else {
        //             //more amount than registration
        //             $remaining_reg = (int)$repayment_transaction->amount - (int)$setting->registration_fee;

        //             $regi = Regpayment::create([
        //                 'customer_id' => $loan->customer_id,
        //                 'date_payed' => Carbon::now('Africa/Nairobi'),
        //                 "amount" => (int)$setting->registration_fee,
        //                 "transaction_id" => $repayment_transaction->mpesaReceiptNumber,
        //                 "channel" => "MPESA",
        //             ]);

        //             //remaider after reg
        //             $remaiderafter_reg = $remaining_reg;
        //             $this->rem_after_reg($repayment_transaction, $customer, $remaiderafter_reg);
        //         }
        //     }
        // }

        return response()->json('Updated');
    }

    public function rem_after_reg($reg_payment, $customer, $remaiderafter_reg)
    {
        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

        /********************if balance is less or equal to loan balance**************/
        if ((int)$remaiderafter_reg < $loan->balance) {
            //amount remaining is less than or equal to loan balance
            Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::parse($reg_payment->transactionDate)->format('Y-m-d'),
                'transaction_id' => $reg_payment->transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
        } elseif ((int)$remaiderafter_reg == $loan->balance) {
            Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::parse($reg_payment->transactionDate)->format('Y-m-d'),
                'transaction_id' => $reg_payment->transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
            Loan::find($loan->id)->update(['settled' => true]);
        } else {
            //amount remaining is greator than loan amount so put the remaining in reg fee account
            Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::parse($reg_payment->transactionDate)->format('Y-m-d'),
                'transaction_id' => $reg_payment->transaction_id,
                'amount' => (int)$loan->balance,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);

            //set loan as paid
            Loan::find($loan->id)->update(['settled' => true]);

            $over_pay = $remaiderafter_reg - $loan->balance;

            $reg2 = Regpayment::where('customer_id', $customer->id)->first();

            $reg2->update([
                "amount" => $reg2->amount + $over_pay,
                "transaction_id" => $reg_payment->transaction_id,
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
