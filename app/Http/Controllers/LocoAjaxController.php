<?php

namespace App\Http\Controllers;

use App\CustomerInteractionCategory;
use App\models\Branch;
use App\models\Customer;
use App\models\CustomerInteraction;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Pre_interaction;
use App\models\Product;
use App\models\Rollover;
use App\models\RoTarget;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocoAjaxController extends Controller
{
    public function loco_performance(Request $request, $id){
        $cred_officer = User::find(decrypt($id));

        //$customers = Customer::where('field_agent_id', $user->id)->count();
        //customers
        if ($request->name == 'customers') {
            $data = $this->customers($cred_officer);
        }
        //active loans
        if ($request->name == 'active_loans') {
            $data = $this->loco_active_loans($cred_officer);
        }

        //active loans total amount
        if ($request->name == 'total_amount') {
            $data = $this->total_amount($cred_officer);
        }
        //disbursed loans
        if ($request->name == 'disbursed_loans') {
            $data = $this->disbursed_loans($cred_officer);
        }

        //due today amount
        if ($request->name == 'due_today_amount') {
            $data = $this->due_today_amount($cred_officer);
        }
        //non performing_loans
        if ($request->name == 'non_performing_loans') {
            $data = $this->non_performing_loans($cred_officer);
        }

        //interest collection
        if ($request->name == 'interest_figures') {
            $data = $this->co_and_manager_interest_figures($cred_officer, date('m'));
        }

        //interest collection
        if ($request->name == 'interactions') {
            $data = $this->interactions($cred_officer);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data

        ], 200);




    }

    function customers($field_agent)
    {
        $customers = Customer::where('field_agent_id', $field_agent->id)->count();
        //$clone = clone $customers;
        $roTraget = RoTarget::whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->where('user_id',$field_agent->id)->first();
        $this_month_customers = Customer::where('field_agent_id', $field_agent->id)->whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->count();
        $cus_target = $roTraget->customer_target;
        $t_achieved = ($this_month_customers/$cus_target)*100;
        return ['customers' => $customers,
            'this_month_customers' => $this_month_customers,
            'this_month_customers_onborded_target' => $cus_target,
            'this_month_customers_onborded_target_achieved' => number_format($t_achieved)

        ];


    }
    function loco_active_loans($field_agent)
    {
       // $us = User::find($this->user->field_agent_id);
        $loans = $field_agent->loans()->where(['settled' => false, 'disbursed' => true])->get();

        $lns_arr = [];
        foreach ($loans as $loan) {
            $last_payment_date = $loan->last_payment_date;
            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                array_push($lns_arr, $loan);
            }
        }
        return count($lns_arr);
    }

    function total_amount($field_agent)
    {
        $totalAmount = 0;
        $amount_paid = 0;
        $TotalLoanAmount = 0;

        $br = Branch::where(['branches.status' => true, 'branches.id' => $field_agent->branch_id])
            ->join('customers', 'branches.id', '=', 'customers.branch_id')
            ->join('loans', function ($join) use ($field_agent) {
                $join->on('customers.id', '=', 'loans.customer_id')
                    ->where('customers.field_agent_id', '=', $field_agent->id)
                    ->where('loans.disbursed', '=', true)
                    ->where('loans.settled', '=', false);
            })
            ->select([
                // 'installments.*',
                'loans.product_id',
                'loans.rolled_over',
                'loans.loan_amount',
                'loans.total_amount',
                'loans.total_amount_paid',
                'loans.has_lp_fee'
            ])
            ->get();

        foreach ($br as $r1) {

            //total amount
            $last_payment_date = $r1->last_payment_date;
            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90)) {
                $t = $r1->total_amount - $r1->total_amount_paid;
                $totalAmount += $t;
                $TotalLoanAmount += $r1->total_amount;
                $amount_paid += $r1->total_amount_paid;

            }


        }

        return ['totalAmount' => number_format(floatval($totalAmount)), 'amount_paid' => number_format(floatval($amount_paid)), 'TotalLoanAmount' => number_format(floatval($TotalLoanAmount))];


    }

    function disbursed_loans($field_agent){
        //disbursed loans
        $disbursed = $field_agent->loans()->where('disbursed', true)->get();
        $disbTotalAmount = 0;
        $disbPaidAmount = 0;
        foreach ($disbursed as $disb) {
            $disbTotalAmount += $disb->loan_amount;
            $disbPaidAmount += $disb->getAmountPaidAttribute();
        }
        $exceeded = 0;
        if (count($disbursed) > 0) {
            $loanSize1 = $disbTotalAmount / count($disbursed);
            $loanSize = number_format($loanSize1);
            if ($loanSize1 > 8000){
                $exceeded = 1;
            }
        } else {
            $loanSize = 0;
        }

        $disbursedMonth = $field_agent->loans()->where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get();
        $disbTotalAmountMonth = 0;
        $disbPaidAmountMonth = 0;
        foreach ($disbursedMonth as $disb) {
            $disbTotalAmountMonth += $disb->loan_amount;
            $disbPaidAmountMonth += $disb->getAmountPaidAttribute();
        }
        $monthyly_eceeded = 0;
        if (count($disbursedMonth) > 0) {
            $loanSizeMonth = $disbTotalAmountMonth / count($disbursedMonth);
            if ($loanSizeMonth > 8000){
                $monthyly_eceeded = 1 ;
            }
        } else {
            $loanSizeMonth = 0;
        }
        //targets
        $roTraget = RoTarget::whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->where('user_id', $field_agent->id)->first();
        $dis_target = $roTraget->disbursement_target_amount;
        $per_disb_achieved = number_format(($disbTotalAmountMonth/$dis_target)*100);

        return ['disbTotalAmount' => number_format($disbTotalAmount),
            'disbursed_loans' => count($disbursed),
            'loanSize' => $loanSize,
            'disbursedMonthAmount'=>number_format($disbTotalAmountMonth),
            'disbursedMonth'=>count($disbursedMonth),
            'loanSizeMonth' => number_format($loanSizeMonth),
            'disbursedMonthTarget' => number_format($dis_target),
            'disbursedMonthTargetAchieved' => number_format($per_disb_achieved),
            'monthyly_eceeded' => $monthyly_eceeded,
            'exceeded' => $exceeded,

        ];


    }

    function due_today_amount($field_agent)
    {

            $inst = Installment::whereDate('due_date', Carbon::now())
                ->join('loans', function ($join) {
                    $join->on('loans.id', '=', 'installments.loan_id')
                        ->where('loans.disbursed', '=', true)
                        ->where('loans.settled', '=', false);
                })
                ->join('customers', function ($join) use ($field_agent) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        ->where('customers.branch_id', $field_agent->branch_id)
                        ->where('customers.field_agent_id', '=', $field_agent->id);

                })
                ->select('installments.*')
                ->get();



        //$due_today_amount = $inst->sum('total') - $inst->sum('amount_paid');
        $repayment_rate = $inst->sum('amount_paid')/$inst->sum('total')*100;

        return ['due_today_amount' => number_format($inst->sum('total')),
            'amount_paid' =>  number_format($inst->sum('amount_paid')),
            'repayment_rate' => number_format($repayment_rate, 1),


        ];


    }

    function non_performing_loans($field_agent){
        //non performing loans
        $non_performing_loans = array();
        $unsettled_loans = $field_agent->loans()->where(['settled' => false, 'disbursed' => true])->get();
        foreach ($unsettled_loans as $lns) {
            $last_payment_date = $lns->last_payment_date;
            if ($last_payment_date != null && $last_payment_date < Carbon::now()->subDays(180)) {
                array_push($non_performing_loans, $lns);
            }
        }
        $non_performing_count = count($non_performing_loans);

        $non_performing_balance = 0;
        foreach ($non_performing_loans as $lo) {
            $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
            $product = Product::find($lo->product_id);
            if ($lo->rolled_over) {
                $rollover = Rollover::where('loan_id', $lo->id)->first();
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            } else {
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
            }
            $non_performing_balance += $total - $payments;
        }

        return ['non_performing_loans' => $non_performing_count,
            'non_performing_balance' =>  number_format($non_performing_balance),


        ];
    }

   function co_and_manager_interest_figures($user, $month)
    {
            if ($month) {
                $loans = $user->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', Carbon::now())->get();
            } else {
                $loans = $user->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->get();
            }


        $total_principle = 0;
        $total_loan_amount = 0;  //total amount to be paid
        $total_paid_interest = 0;  //total amount to be paid
        $total_interest = 0;  //total amount to be paid
        if ($loans) {
            foreach ($loans as $loan) {
                $principle = $loan->loan_amount;
                $total = $loan->total;
                $interest = $total - $principle;
                $total_loan_amount += $total;
                $total_principle += $principle;
                $total_interest += $interest;
                //if loan is settled, include interest in total paid interest
                if ($loan->settled == true) {
                    $total_paid_interest += $interest;
                }
            }
        }
        return [
            'total_principle' => number_format($total_principle),
            'total_loan_amount' => number_format($total_loan_amount),
            'total_paid_interest' => number_format($total_paid_interest),
            'total_interest' => number_format($total_interest)
        ];
    }

    function interactions($user){
        $pre = CustomerInteractionCategory::where('name', 'Prepayment')->first();
        $category = CustomerInteractionCategory::whereIn('name', ['Prepayment', 'Due Collection'])->pluck('id');
        $other_cat = CustomerInteractionCategory::whereIn('name', ['Customer Satisfaction survey', 'First Visit Lo', 'First Visit Co'])->pluck('id');

      //  $co = User::hasRole('collection_officer')->where(['field_agent_id' => $user->id])->first();
        $co = User::where(['field_agent_id' => $user->id])->whereHas('roles', function ($query) {
            return $query->where([['name', '=', 'collection_officer']]);
        })->first();
        $arry_ids = [$user->id];
        if ($co){
            $arry_ids = [$user->id, $co->id];

        }
        //dd($arry_ids, [131, 148]);
        //$int = CustomerInteraction::WhereIn('user_id', $arry_ids)->whereIn('interaction_category_id', [1,5,6])->where('status', 1)->get();
        //$int1 = CustomerInteraction::WhereIn('user_id', $arry_ids)->whereIn('interaction_category_id', [2,3])->where('status', 1)->get();

        //dd($arry_ids, $category);

        $data = CustomerInteraction::where(function ($query) use ($category){
                $query->whereIn('interaction_category_id', $category);
            })

        ->join('customers', function ($join) use ($user) {
            $join->on('customers.id', '=', 'customer_interactions.customer_id')
                ->where(['customers.field_agent_id' => $user->id]);
        })
           ->select('customer_interactions.*');

        $data2 = CustomerInteraction::where(function ($query) use ($other_cat,$arry_ids){
                $query->WhereIn('interaction_category_id', $other_cat)
                    ->WhereIn('user_id',$arry_ids);

            })

            ->join('customers', function ($join) use ($user) {
                $join->on('customers.id', '=', 'customer_interactions.customer_id')
                    ->where(['customers.field_agent_id' => $user->id]);
            })
            ->select('customer_interactions.*');


        $interactions = Pre_interaction::query()->where(['interaction_category_id' => $pre->id])
            ->join('customers', function ($join) use ($user) {
                $join->on('customers.id', '=', 'pre_interactions.customer_id')
                    ->where('customers.field_agent_id', '=', $user->id);;
            })
            ->select('pre_interactions.*');



       // $pre_clone = clone $interactions;
        $success_rate = 0;

        $active1 = (clone $data)->where('customer_interactions.status', '=',1)->count();
        $active2 = (clone $data2)->where('customer_interactions.status', '=',1)->count();
        $active = $active1 + $active2;


        $inactive1 = (clone $data)->where('customer_interactions.status', '=',2)->count();
        $inactive2 = (clone $data2)->where('customer_interactions.status', '=',2)->count();
        $inactive = $inactive1 + $inactive2;



        $interactions_success1 = (clone $data)->where(['target' => 1, 'customer_interactions.status' => 2])->count();
        $interactions_success2 = (clone $data2)->where(['target' => 1, 'customer_interactions.status' => 2])->count();
        $interactions_success = $interactions_success1 + $interactions_success2;

        $target = $interactions->count() + $data->count() + $data2->count();

        if ($target > 0){
            $success_rate = number_format(($interactions_success/$target)*100);

        }


        $due1 = (clone $data)->where(['customer_interactions.status' => 1])->whereDate('customer_interactions.next_scheduled_interaction', '=', Carbon::now())
            ->orWhere(function ($query) {
                $query->join('customer_interaction_followups', function ($join) {
                    $join->on('customer_interactions.id', '=', 'customer_interaction_followups.follow_up_id')
                        ->whereDate('customer_interaction_followups.next_scheduled_interaction', '<=', Carbon::now())
                        ->where(['customer_interactions.status' => 1, 'customer_interaction_followups.status' => 2]);

                });
            })->count();

        $due2 = (clone $data2)->where(['customer_interactions.status' => 1])->whereDate('customer_interactions.next_scheduled_interaction', '=', Carbon::now())
            ->orWhere(function ($query) {
                $query->join('customer_interaction_followups', function ($join) {
                    $join->on('customer_interactions.id', '=', 'customer_interaction_followups.follow_up_id')
                        ->whereDate('customer_interaction_followups.next_scheduled_interaction', '<=', Carbon::now())
                        ->where(['customer_interactions.status' => 1, 'customer_interaction_followups.status' => 2]);

                });
            })->count();
        $due = $due1 + $due2;

        $overdue1 = (clone $data)->where(['customer_interactions.status' => 1])->whereDate('customer_interactions.next_scheduled_interaction', '<', Carbon::now())
            ->orWhere(function ($query) {
                $query->join('customer_interaction_followups', function ($join) {
                    $join->on('customer_interactions.id', '=', 'customer_interaction_followups.follow_up_id')
                        ->whereDate('customer_interaction_followups.next_scheduled_interaction', '<', Carbon::now())
                        ->where(['customer_interactions.status' => 1, 'customer_interaction_followups.status' => 2]);

                });
            })->count();
        $overdue2 = (clone $data2)->where(['customer_interactions.status' => 1])->whereDate('customer_interactions.next_scheduled_interaction', '<', Carbon::now())
            ->orWhere(function ($query) {
                $query->join('customer_interaction_followups', function ($join) {
                    $join->on('customer_interactions.id', '=', 'customer_interaction_followups.follow_up_id')
                        ->whereDate('customer_interaction_followups.next_scheduled_interaction', '<', Carbon::now())
                        ->where(['customer_interactions.status' => 1, 'customer_interaction_followups.status' => 2]);

                });
            })->count();
        $overdue = $overdue1 + $overdue2;
        //$this_month_preclone = clone $interactions;

//        if ($request->month) {
//            $this_month_interactions = $clone4->whereMonth('customer_interactions.created_at', '=', $request->month)->whereYear('customer_interactions.created_at', '=', Carbon::now())->count();
//            $this_month_interactions_closed = $clone5->whereMonth('customer_interactions.closed_date', '=', $request->month)->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['customer_interactions.status' => 2])->count();
//            $this_month_interactions_success = $monthly_success_clone->whereMonth('customer_interactions.closed_date', '=', $request->month)->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['target' => 1, 'customer_interactions.status' => 2])->count();
//
//            $this_month_pre = $this_month_preclone->whereMonth('pre_interactions.created_at', '=', $request->month)->whereYear('pre_interactions.created_at', '=', Carbon::now())->count();
//
//
//
//        }
//        else{
//            $this_month_interactions =  $clone4->whereMonth('customer_interactions.created_at', '=', Carbon::now())->whereYear('customer_interactions.created_at', '=', Carbon::now())->count();
//            $this_month_interactions_closed =  $clone5->whereMonth('customer_interactions.closed_date', '=', Carbon::now())->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['customer_interactions.status' => 2])->count();
//            $this_month_interactions_success = $monthly_success_clone->whereMonth('customer_interactions.closed_date', '=',  Carbon::now())->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['target' => 1, 'customer_interactions.status' => 2])->where([])->count();
//            $this_month_pre = $this_month_preclone->whereMonth('pre_interactions.created_at', '=', Carbon::now())->whereYear('pre_interactions.created_at', '=', Carbon::now())->count();
//
//
//
//        }

        $this_month_interactions1 =  (clone $data)->whereMonth('customer_interactions.created_at', '=', Carbon::now())->whereYear('customer_interactions.created_at', '=', Carbon::now())->count();
        $this_month_interactions2 =  (clone $data2)->whereMonth('customer_interactions.created_at', '=', Carbon::now())->whereYear('customer_interactions.created_at', '=', Carbon::now())->count();
        $this_month_interactions = $this_month_interactions1 + $this_month_interactions2;

        $this_month_interactions_closed1 =  (clone $data)->whereMonth('customer_interactions.closed_date', '=', Carbon::now())->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['customer_interactions.status' => 2])->count();
        $this_month_interactions_closed2 =  (clone $data2)->whereMonth('customer_interactions.closed_date', '=', Carbon::now())->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['customer_interactions.status' => 2])->count();
        $this_month_interactions_closed = $this_month_interactions_closed1 + $this_month_interactions_closed2;

        $this_month_interactions_success1 = (clone $data)->whereMonth('customer_interactions.closed_date', '=',  Carbon::now())->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['target' => 1, 'customer_interactions.status' => 2])->count();
        $this_month_interactions_success2 = (clone $data2)->whereMonth('customer_interactions.closed_date', '=',  Carbon::now())->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['target' => 1, 'customer_interactions.status' => 2])->count();
        $this_month_interactions_success = $this_month_interactions_success1 + $this_month_interactions_success2;
        $this_month_pre = (clone $interactions)->whereMonth('pre_interactions.created_at', '=', Carbon::now())->whereYear('pre_interactions.created_at', '=', Carbon::now())->count();

        $monthly_target = $this_month_interactions + $this_month_pre;
        $monthly_success_rate = 0;
        if ($monthly_target > 0){
            $monthly_success_rate = number_format(($this_month_interactions_success/$monthly_target)* 100 );

        }




        $pdue = (clone $interactions)->whereDate('pre_interactions.due_date', '=', Carbon::now())->where(['pre_interactions.interaction_category_id' => 2])->count();
        $poverdue = (clone $interactions)->whereDate('pre_interactions.due_date', '<', Carbon::now())->count();
        //total pre interactions for due collection overdue
        $p1 = (clone $interactions)->whereDate('pre_interactions.due_date', '<', Carbon::now())->where(['pre_interactions.interaction_category_id' => 2])->count();
        $Pre_arrears = (clone $interactions)->where(['pre_interactions.interaction_category_id' => 4])->count();




        $data1 = [
            'interactions' => $data->count() + $data2->count(),
            'active' => $active,
            'inactive' => $inactive, 'due' => $due,
            'this_month_interactions' => $this_month_interactions,
            'this_month_interactions_closed' => $this_month_interactions_closed,
            'over_due' => $overdue,
            'pre' => $interactions->count() - $p1,
            'passed_unttanded_pre_interactions' =>  $p1,

            'pdue' => $pdue,
            'poverdue' => $poverdue,
            'interactions_success' => $interactions_success,
            'this_month_interactions_success' => $this_month_interactions_success,
            'monthly_success_rate'=>$monthly_success_rate,
            'success_rate' => $success_rate,
            'pre_arrears' => $Pre_arrears,
        ];

        return $data1;
    }





}
