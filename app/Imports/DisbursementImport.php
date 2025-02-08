<?php

namespace App\Imports;

use App\Http\Controllers\MpesaPaymentController;
use App\models\Branch;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Product;
use App\models\Regpayment;
use App\models\Setting;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use App\models\Customer;
use App\models\LoanType;
use App\models\Pre_interaction;
use App\models\Referee;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DisbursementImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $start_date = Date::excelToDateTimeObject($row['start_date'])->format('Y-m-d');

            $customer = Customer::where('phone', $row['phone'])->first();

            if ($customer) {
                $loan = Loan::where('customer_id', $customer->id)->where('approved', false)->where('disbursed', false)->first();

                if ($loan) {
                    $start_date = Carbon::parse($start_date)->subDays(2);
                    /**************check if loan processing and reg fee has been paid**************/
                    $payment = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 3])->sum('amount');

                    $reg = Regpayment::where('customer_id', $loan->customer_id)->first();

                    if ($reg) {
                        $check_pay = $payment + (int) $reg->amount;
                    } else {
                        $check_pay = $payment;
                    }

                    if ($check_pay >= (int) Setting::first()->required_pay()) {
                        $loan->update([
                            'approved' => true,
                            "approved_date" => Carbon::parse($start_date),
                            "approved_by" => 181,
                            "approve_loan_ip" => '105.62.201.116'
                        ]);

                        $settings = Setting::first();

                        //create installments
                        $product = Product::find($loan->product_id);
                        // $amountPayable = $loan->loan_amount + ($loan->loan_amount * $product->interest/100);
                        $lp_fee = 0;
                        if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                        {
                            //break down loans to daily installments based on product duration
                            $principle_amount = round($loan->total_amount / $product->duration);
                            $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                            if ($settings->lp_fee) {
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
                                if ($i == 0) {
                                    Installment::create([
                                        "loan_id" => $loan->id,
                                        "principal_amount" => $principle_amount,
                                        "total" => $amountPayable,
                                        "interest" => $interest_payable,
                                        "lp_fee" => $lp_fee,
                                        "due_date" => Carbon::parse($start_date)->addDays(1)->addDays($days),
                                        "start_date" => Carbon::parse($start_date)->addDays(2),
                                        "current" => true,
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
                                        "due_date" => Carbon::parse($start_date)->addDays(1)->addDays($days),
                                        "start_date" => Carbon::parse($start_date)->addDays(2),
                                        "current" => false,
                                        "amount_paid" => 0,
                                        "position" => $i + 1
                                    ]);
                                }
                            }
                        } else {
                            for ($i = 0; $i < $product->installments; $i++) {
                                $days = $days + 7;
                                if ($i == 0) {
                                    Installment::create([
                                        "loan_id" => $loan->id,
                                        "principal_amount" => $principle_amount,
                                        "total" => $amountPayable,
                                        "interest" => $interest_payable,
                                        "lp_fee" => $lp_fee,
                                        "due_date" => Carbon::parse($start_date)->addDays($days),
                                        "start_date" => Carbon::parse($start_date),
                                        "current" => true,
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
                                        "due_date" => Carbon::parse($start_date)->addDays($days),
                                        "start_date" => Carbon::parse($start_date),
                                        "current" => false,
                                        "amount_paid" => 0,
                                        "position" => $i + 1
                                    ]);
                                }
                            }
                        }

                        Payment::create([
                            'loan_id' => $loan->id,
                            'amount' => $loan->loan_amount,
                            'transaction_id' => Str::random(10),
                            'date_payed' => Carbon::parse($start_date)->format('Y-m-d'),
                            'channel' => "MPESA",
                            'payment_type_id' => 2,
                        ]);

                        $loan->update([
                            "has_lp_fee" => true,
                            "disbursed" => true,
                            "disbursement_date" => Carbon::parse($start_date),
                            "end_date" => Carbon::parse($start_date)->addDays($loan->product()->first()->duration + 2),
                            "disbursed_by" => 181,
                            'disburse_loan_ip' => '105.62.201.116'
                        ]);

                        //check if registration payment is more than required
                        $reg = Regpayment::where('customer_id', $loan->customer_id)->first();

                        if ($reg) {
                            //meaning the registration is greater than required so put the extra in loan processing fee
                            if ($reg->amount > $settings->registration_fee) {
                                //balance after registration
                                $bal = $reg->amount - $settings->registration_fee;
                                if ($settings->loan_processing_fee > 0) {
                                    //meaning the remaining balance is greater than loan processing fee
                                    if ($bal > $settings->loan_processing_fee) {
                                        Payment::create([
                                            'payment_type_id' => 3,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::parse($start_date),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $settings->loan_processing_fee
                                        ]);
                                        $rem = $bal - $settings->loan_processing_fee;

                                        //then add the remaining to the loan settlement
                                        if ($rem >= $loan->balance){
                                            $remainda = $rem - $loan->balance;
                                            Payment::create([
                                                'payment_type_id' => 1,
                                                'loan_id' => $loan->id,
                                                'date_payed' => Carbon::parse($start_date),
                                                'transaction_id' => $reg->transaction_id,
                                                'channel' => 'MPESA',
                                                'amount' => $loan->balance
                                            ]);
                                            $loan->update(['settled' => true]);
                                        } else {
                                            Payment::create([
                                                'payment_type_id' => 1,
                                                'loan_id' => $loan->id,
                                                'date_payed' => Carbon::parse($start_date),
                                                'transaction_id' => $reg->transaction_id,
                                                'channel' => 'MPESA',
                                                'amount' => $rem
                                            ]);
                                            $remainda = 0;
                                        }
                                        //add the amount to current installment being paid
                                        $handle_installments = new MpesaPaymentController();
                                        $handle_installments->handle_installments($loan, $rem);
                                    } //amount remaining is not greater than loan processing fee
                                    else {
                                        Payment::create([
                                            'payment_type_id' => 3,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::parse($start_date),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $bal
                                        ]);
                                        $remainda = 0;
                                    }
                                    $reg->update([
                                        'amount' => $settings->registration_fee + $remainda
                                    ]);
                                } else {
                                    if ($bal >= $loan->balance){
                                        $remainda = $bal - $loan->balance;
                                        Payment::create([
                                            'payment_type_id' => 1,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::parse($start_date),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $loan->balance
                                        ]);
                                        $loan->update(['settled' => true]);
                                    } else {
                                        Payment::create([
                                            'payment_type_id' => 1,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::parse($start_date),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $bal
                                        ]);
                                        $remainda = 0;
                                    }
                                    //add the amount to current installment being paid
                                    $handle_installments = new MpesaPaymentController();
                                    $handle_installments->handle_installments($loan, $bal);

                                    $reg->update([
                                        'amount' => $settings->registration_fee + $remainda
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
