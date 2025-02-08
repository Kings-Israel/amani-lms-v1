<?php

namespace App\Console\Commands;

use App\models\Arrear;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Product;
use App\models\Regpayment;
use App\models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InstallmentsReconcilliation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installments:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile Transaction to show skipped days';

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
        $loans = Loan::where('disbursed', true)->where('settled', false)->get();
        $settings = Setting::first();

        foreach ($loans as $loan) {
            // Installment::where('loan_id', $loan->id)->delete();
            // Arrear::where('loan_id', $loan->id)->delete();

            // $installments = Installment::where('loan_id', $loan->id)->get();
            // if (count($installments) == 0) {
            // }

            // $this->createInstallments($loan);

            $this->updateInstallments($loan);

            // $this->updateLoan($loan);

            // $this->correctLoanPayments($loan);
        }

        // $this->updateArrears();
    }

    public function updateArrears()
    {
        $installments = Installment::whereDate('due_date', '<', now()->format('Y-m-d'))->where('completed', false)->get();

        foreach ($installments as $installment) {
            if (!$installment->completed) {
                $arrear = Arrear::where('installment_id', $installment->id)->first();

                if (!$arrear) {
                    Arrear::create([
                        'loan_id' => $installment->loan_id,
                        'amount' => $installment->total - $installment->amount_paid,
                        'installment_id' => $installment->id
                    ]);

                    $installment->update([
                        'in_arrear' => true
                    ]);
                } else {
                    $arrear->update([
                        'amount' => $installment->total - $installment->amount_paid
                    ]);
                }
            }
        }
    }

    public function updateInstallments($loan)
    {
        $installments = Installment::where('loan_id', $loan->id)->get();
        $first_incomplete_installment = Installment::where('loan_id', $loan->id)->where('completed', false)->first();
        // $payments = Payment::where('loan_id', $loan->id)->where('payment_type_id', 1)->sum('amount');

        // $amount_remainder = $payments;
        foreach ($installments as $key => $installment) {
            if (Carbon::parse($installment->due_date)->equalTo(now()->format('Y-m-d'))) {
                $installment->update([
                    'current' => true,
                ]);
            } else {
                $installment->update([
                    'current' => false,
                ]);
            }

            if (Carbon::parse($installment->due_date)->lessThan(now()->format('Y-m-d'))) {
                if (!$installment->completed) {
                    $arrear = Arrear::where(['installment_id' => $installment->id, 'loan_id' => $installment->loan_id])->first();
                    $installment->update([
                        'in_arrear' => true,
                    ]);

                    if ($arrear) {
                        $arrear->update([
                            'amount' => $installment->total - $installment->amount_paid,
                        ]);
                    } else {
                        Arrear::create([
                            'installment_id' => $installment->id,
                            'loan_id' => $installment->loan_id,
                            'amount' => $installment->total
                        ]);
                    }

                    if ($installment->id == $first_incomplete_installment->id) {
                        $installment->update([
                            'being_paid' => true
                        ]);
                    } else {
                        $installment->update([
                            'being_paid' => false
                        ]);
                    }
                } else {
                    $installment->update([
                        'in_arrear' => false,
                        'being_paid' => false,
                    ]);

                    $arrear = Arrear::where(['installment_id' => $installment->id, 'loan_id' => $installment->loan_id])->delete();
                }
            }
        }

        // foreach ($installments as $installment) {
        //     if (Carbon::parse($installment->due_date)->equalTo(now()->format('Y-m-d'))) {
        //         $installment->update([
        //             'current' => true,
        //         ]);
        //     } else {
        //         $installment->update([
        //             'current' => false,
        //         ]);
        //     }
        // }

        // if ($payments > 0) {
        //     $loan->update([
        //         'total_amount_paid' => $payments
        //     ]);

        //     if ($payments >= $loan->total_amount) {
        //         $loan->update([
        //             'settled' => true,
        //         ]);
        //     } else {
        //         $loan->update([
        //             'settled' => false,
        //         ]);
        //     }

        //     foreach ($installments as $installment) {
        //         $amount_remainder -= $installment->total;

        //         // if (Carbon::parse($installment->due_date)->equalTo(now()->format('Y-m-d'))) {
        //         //     $installment->update([
        //         //         'current' => true,
        //         //     ]);
        //         // }

        //         // if (Carbon::parse($installment->due_date)->format('Y-m-d') !== Carbon::now()->format('Y-m-d')) {
        //         //     $installment->update([
        //         //         'current' => false,
        //         //     ]);
        //         // }

        //         if ($amount_remainder > 0) {
        //             // If remainder of payments sum is less than installment amount
        //             if ($amount_remainder < $installment->total) {
        //                 // Update Installment and set it as the one being paid
        //                 $installment->update([
        //                     'amount_paid' => $amount_remainder,
        //                     'being_paid' => true,
        //                     'last_payment_date' => Carbon::parse($installment->due_date)->addDay()->format('Y-m-d H:i:s'),
        //                     'in_arrear' => true,
        //                 ]);

        //                 $arrear = Arrear::where(['installment_id' => $installment->id, 'loan_id' => $installment->loan_id])->first();

        //                 // Create or update arrear if installment due date is past current date
        //                 if (Carbon::parse($installment->due_date)->lessThan(now()->format('Y-m-d'))) {
        //                     $installment->update([
        //                         'in_arrear' => true,
        //                     ]);

        //                     if ($arrear) {
        //                         $arrear->update([
        //                             'amount' => $installment->total - $amount_remainder,
        //                         ]);
        //                     } else {
        //                         Arrear::create([
        //                             'installment_id' => $installment->id,
        //                             'loan_id' => $installment->loan_id,
        //                             'amount' => $installment->total - $amount_remainder
        //                         ]);
        //                     }
        //                 } else {
        //                     $installment->update([
        //                         'in_arrear' => false,
        //                     ]);
        //                 }
        //             } else {
        //                 // If remainder is equal to or more than installment amount
        //                 $installment->update([
        //                     'amount_paid' => $installment->total,
        //                     'completed' => true,
        //                     'in_arrear' => false,
        //                     'last_payment_date' => Carbon::parse($installment->due_date)->addDay()->format('Y-m-d H:i:s'),
        //                     'interest_payment_date' => Carbon::parse($installment->due_date)->addDay()->format('Y-m-d H:i:s'),
        //                     'being_paid' => false,
        //                 ]);

        //                 $next_installment = Installment::where(['position' => $installment->position + 1, 'loan_id' => $installment->loan_id])->first();

        //                 if ($next_installment) {
        //                     $next_installment->update([
        //                         'being_paid' => true,
        //                     ]);
        //                 }

        //                 $arrears = Arrear::where(['installment_id' => $installment->id, 'loan_id' => $installment->loan_id])->first();

        //                 if ($arrears) {
        //                     $arrears->delete();
        //                 }
        //             }
        //         } else {
        //             $arrear = Arrear::where(['installment_id' => $installment->id, 'loan_id' => $installment->loan_id])->first();

        //             // Create or update arrear if installment due date is past current date
        //             if (Carbon::parse($installment->due_date)->lessThan(now()->format('Y-m-d'))) {
        //                 $installment->update([
        //                     'in_arrear' => true,
        //                 ]);

        //                 if ($arrear) {
        //                     $arrear->update([
        //                         'amount' => $installment->total,
        //                     ]);
        //                 } else {
        //                     Arrear::create([
        //                         'installment_id' => $installment->id,
        //                         'loan_id' => $installment->loan_id,
        //                         'amount' => $installment->total
        //                     ]);
        //                 }
        //             }
        //         }
        //     }
        // } else {
        //     foreach ($installments as $installment) {
        //         if (Carbon::parse($installment->due_date)->lessThan(now()->format('Y-m-d'))) {
        //             $installment->update([
        //                 'completed' => false,
        //                 'in_arrear' => true,
        //             ]);

        //             $arrear = Arrear::where(['installment_id' => $installment->id, 'loan_id' => $installment->loan_id])->first();

        //             if ($arrear) {
        //                 $arrear->update([
        //                     'amount' => $installment->total,
        //                 ]);
        //             } else {
        //                 Arrear::create([
        //                     'installment_id' => $installment->id,
        //                     'loan_id' => $installment->loan_id,
        //                     'amount' => $installment->total
        //                 ]);
        //             }
        //         }
        //     }
        // }
    }

    public function createInstallments($loan)
    {
        $settings = Setting::first();
        // Create installements
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
                if ($i == 0) {
                    Installment::create([
                        "loan_id" => $loan->id,
                        "principal_amount" => $principle_amount,
                        "total" => $amountPayable,
                        "interest" => $interest_payable,
                        "lp_fee" => $lp_fee,
                        "due_date" => Carbon::parse($loan->disbursement_date)->addDays($days),
                        "start_date" => Carbon::parse($loan->disbursement_date),
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
                        "due_date" => Carbon::parse($loan->disbursement_date)->addDays($days),
                        "start_date" => Carbon::parse($loan->disbursement_date),
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
                        "due_date" => Carbon::parse($loan->disbursement_date)->addDays($days),
                        "start_date" => Carbon::parse($loan->disbursement_date),
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
                        "due_date" => Carbon::parse($loan->disbursement_date)->addDays($days),
                        "start_date" =>Carbon::parse($loan->disbursement_date),
                        "current" => false,
                        "amount_paid" => 0,
                        "position" => $i + 1
                    ]);
                }
            }
        }
    }

    public function updateLoan($loan)
    {
        $loan_amount = (int)$loan->loan_amount;
        $total_amount = (int)$loan->total_amount;

        if ($loan_amount > $total_amount) {
            $loan->update([
                'loan_amount' => $total_amount,
                'total_amount' => $loan_amount
            ]);
        }
    }

    public function correctLoanPayments($loan)
    {
        $setting = Setting::first();

        if ($loan->settled) {
            $payments = Payment::where('loan_id', $loan->id)->where('payment_type_id', 1)->get();

            $reg_payment = Regpayment::where('customer_id', $loan->customer_id)->where('amount', '>', $setting->registration_fee)->first();

            foreach ($payments as $payment) {
                if ($reg_payment) {
                    if ($payment->amount == ($loan->total_amount - ($reg_payment->amount - $setting->registration_fee))) {
                        $transaction_id = $payment->transaction_id;
                        $payment->delete();
                        $update_payment = Payment::where('transaction_id', $transaction_id)->first();
                        if ($update_payment) {
                            $update_payment->update([
                                'amount' => $update_payment->amount + ($reg_payment->amount - $setting->registration_fee),
                            ]);
                        } else {
                            Payment::create([
                                'amount' => $reg_payment->amount - $setting->registration_fee,
                                'loan_id' => $loan->id,
                                'transaction_id' => $transaction_id,
                                'payment_type_id' => 1
                            ]);
                        }

                        $reg_payment->update([
                            'amount' => $setting->registration_fee
                        ]);
                    }
                    $amount_paid = Payment::where('loan_id', $loan->id)->where('payment_type_id', 1)->sum('amount');

                    $loan->update([
                        'settled' => false,
                        'total_amount_paid' => $amount_paid
                    ]);
                }
            }

        }
    }
}
