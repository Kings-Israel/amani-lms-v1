<?php

namespace App\Exports;

use App\models\Loan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;

class LoansExport implements FromCollection, ShouldQueue
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        ini_set('max_execution_time', 300);
//        $today = Carbon::today()->format('Y-m-d');
        return Loan::query()->where(['disbursed' => true, 'settled' => false, 'rolled_over'=>false])->get();
//        $lo = array();
//        foreach ($loans_w_arrears as $lns){
//            $last_payment_date = $lns->last_payment_date;
//            $product = Product::find($lns->product_id);
//            $skipped_installments = Installment::where(['loan_id'=>$lns->id, 'completed'=>false])->where('due_date', '<', $today)->get();
//            $skipped_installments_count = count($skipped_installments);
//            //next payment date
//            $next_payment_date = Installment::where(['loan_id' => $lns->id, 'current' => true])->first();
//            //principle paid & principle due & Interest Paid , Interest Due
//            $instals = Installment::where(['loan_id' => $lns->id])->get();
//            $ppaid = 0;
//            $Ipaid = 0;
//            foreach ($instals as $instal) {
//                if ($instal->amount_paid >= $instal->principal_amount) {
//                    $ppaid += $instal->principal_amount;
//                } else {
//                    $ppaid += $instal->amount_paid;
//                }
//                if ($instal->amount_paid > $instal->principal_amount) {
//                    $iP = $instal->amount_paid - $instal->principal_amount;
//                    $Ipaid += $iP;
//                }
//            }
//            $pdue = $lns->loan_amount - $ppaid;
//            $loan_total_interest = $lns->loan_amount * ($product->interest / 100);
//            $interest_due = $loan_total_interest - $Ipaid;
//            //Loan Officer
//            $Customer = Customer::find($lns->customer_id);
//            $user = User::find($Customer->field_agent_id);
//            $branch = Branch::find($Customer->branch_id);
//            $branch = $branch->bname;
//            $field_agent = $user->name;
//            //Amount Paid
//            $payments = Payment::where(['loan_id' => $lns->id, 'payment_type_id' => 1])->sum('amount');
//            //TOTAL
//            if ($lns->rolled_over) {
//                $rollover = Rollover::where('loan_id', $lo['id'])->first();
//                $total = $lns->loan_amount + ($lns->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
//            } else {
//                $total = $lns->loan_amount + ($lns->loan_amount * ($product->interest / 100));
//
//            }
//            //Balance
//            $balance = $total - $payments;
//            //Total Arrears
//            $amount = 0;
//            $arrears = Arrear::where('loan_id', $lns->id)->get();
//            if ($arrears->first()) {
//                foreach ($arrears as $arrear) {
//                    $amount += $arrear->amount;
//                }
//            }
//            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90))
//            {
//                if ($skipped_installments_count > 0){
//                    array_push($lo,array(
//                        'id'=>$lns->id,
//                        'loan_account'=>$lns->loan_account,
//                        'loan_amount'=>$lns->loan_amount,
//                        'date_created'=>$lns->date_created,
//                        'field_agent'=>$field_agent,
//                        'branch'=>$branch,
//                        'end_date'=>$lns->end_date,
//                        'disbursement_date'=>$lns->disbursement_date,
//                        'created_at'=>$lns->created_at,
//                        'last_payment_date'=>$last_payment_date,
//                        'purpose'=>$lns->purpose,
//                        'phone'=>$lns->customer->phone,
//                        'product_name'=>$product->product_name,
//                        'installments'=>$product->installments,
//                        'interest'=>$product->interest,
//                        'owner'=>$lns->customer->fname ." ". $lns->customer->lname,
//                        'skipped_installments'=>$skipped_installments_count,
//                        'next_payment_date'=>$next_payment_date,
//                        'principle_paid'=>$ppaid,
//                        'principle_due'=>$pdue,
//                        'interest_paid'=>$Ipaid,
//                        'interest_due'=>$interest_due,
//                        'amount_paid'=>$payments,
//                        'total'=>$total,
//                        'balance'=>$balance,
//                        'total_arrears'=>$amount,
//                    ));
//                }
//            }
//        }
//        return $loans_w_arrears;
    }
}
