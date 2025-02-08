<?php

namespace App\Http\Controllers;

use App\CustomerInteractionCategory;
use App\LoginToken;
use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\Customer_interaction_followup;
use App\models\CustomerInteraction;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Pre_interaction;
use App\models\Product;
use App\models\Raw_payment;
use App\models\Rollover;
use App\models\Setting;
use App\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class HomeController extends Controller
{
    protected $firstDayOfMonth;

    public function __construct()
    {
        $this->middleware('auth');
        // Initialize the first day of the current month
        $this->firstDayOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
    }

    // public function index(Request $request)
    // {
    //     // Initialize data
    //     $this->data['month_array'] = 0;
    //     $this->data['payments_month_array'] = 0;
    //     $this->data['field_agents'] = User::role('field_agent')->where('status', true)->get();
    //     $this->data['branches'] = Branch::where('status', true)->get();
    //     $this->data['current_branch'] = $request->branch ?? 'all';
    //     $this->data['current_officer'] = $request->field_agent ?? 'all';

    //     // Set active branch
    //     $branch_id = Branch::where('bname', 'Bungoma')->first()->id;
    //     $this->data['active_branch'] = $branch_id;

    //     if (auth()->user()->hasRole('field_agent')) {
    //         $logged_in_agent = auth()->user();
    //         $branch_id = $this->data['current_branch'] == 'all' ? null : $this->data['current_branch'];
    //         $this->data['commission'] = $this->calculateFieldAgentCommission($branch_id, $logged_in_agent->id);
    //     } else {
    //         $this->data['commission'] = 0;
    //     }

    //     // MTD Loan Calculation
    //     $this->data = array_merge($this->data, $this->calculateMtdLoans($request));

    //     // Return view with data
    //     return view('home.home2', $this->data);
    // }

    public function index(Request $request)
    {
        $this->data['month_array'] = 0;
        $this->data['payments_month_array'] = 0;
        $this->data['field_agents'] = User::role('field_agent')->where('status', true)->get();
        $this->data['branches'] = Branch::where('status', true)->get();
        $this->data['current_branch'] = $request->branch ?? 'all';
        $this->data['current_officer'] = $request->field_agent ?? 'all';

        $branch_id = Branch::where('bname', 'Bungoma')->first()->id;
        $this->data['active_branch'] = $branch_id;

        if (auth()->user()->hasRole('field_agent')) {
            $logged_in_agent = auth()->user();
            $branch_id = $this->data['current_branch'] == 'all' ? null : $this->data['current_branch'];
            $this->data['commission'] = $this->calculateFieldAgentCommission($branch_id, $logged_in_agent->id);
        } else {
            $this->data['commission'] = 0;
        }

        $this->data = array_merge($this->data, $this->calculateMtdLoans($request));

        $this->data['repeat_applicants_count'] = Customer::where('times_loan_applied', '>=', 2)
        ->whereHas('loans', function($query) {
            $query->where('approved', '1');
        })->count();

        $this->data['payments'] = $this->getBranchPayments();

        // Calculate total unpaid loan balance
        $this->data['loan_balance'] = Loan::getTotalUnpaidBalance();

        return view('home.home2', $this->data);
    }

    private function getBranchPayments()
    {
        $branches = Branch::where('status', true)->get();
        $paymentsData = [];
        $totalPayments = [
            'daily_target' => 0,
            'daily_achieved' => 0,
            'percentage' => 0
        ];

        foreach ($branches as $branch) {
            // Calculate daily target (sum of installments due today)
            $dailyTarget = Installment::whereHas('loan', function ($query) use ($branch) {
                $query->whereHas('customer', function ($customerQuery) use ($branch) {
                    $customerQuery->where('branch_id', $branch->id);
                })->where('disbursed', true)
                  ->where('settled', false);
            })->whereDate('due_date', now())
              ->sum('total');

            // Calculate daily achieved (sum of payments made today)
            $dailyAchieved = Payment::where('payment_type_id', 1) // Assuming payment_type_id for payments is 1
                ->whereHas('loan', function ($query) use ($branch) {
                    $query->whereHas('customer', function ($customerQuery) use ($branch) {
                        $customerQuery->where('branch_id', $branch->id);
                    })->where('disbursed', true)
                      ->where('settled', false);
                })
                ->whereDate('date_payed', now()) // Payments made today
                ->sum('amount');

            // Calculate performance percentage
            $percentage = $dailyTarget > 0 ? round(($dailyAchieved / $dailyTarget) * 100, 2) : 0;

            // Add data to the paymentsData array
            $paymentsData[] = [
                'branch_name' => $branch->bname,
                'daily_target' => $dailyTarget,
                'daily_achieved' => $dailyAchieved,
                'percentage' => $percentage
            ];

            // Update totals
            $totalPayments['daily_target'] += $dailyTarget;
            $totalPayments['daily_achieved'] += $dailyAchieved;
        }

        // Calculate overall percentage
        $totalPayments['percentage'] = $totalPayments['daily_target'] > 0
            ? round(($totalPayments['daily_achieved'] / $totalPayments['daily_target']) * 100, 2)
            : 0;

        // Add totals to the end of the array
        $paymentsData[] = [
            'branch_name' => 'Total',
            'daily_target' => $totalPayments['daily_target'],
            'daily_achieved' => $totalPayments['daily_achieved'],
            'percentage' => $totalPayments['percentage']
        ];

        return $paymentsData;
    }











    private function calculateMtdLoans($request)
    {
        $mtd_loans = 0;
        $mtd_loan_amount = 0;
        $mtd_loan_applied_amount = 0;

        // For field agent
        if (isset($request->field_agent)) {
            $field_agent = User::find($request->field_agent);
            $mtd_loans = $field_agent->loans()->where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->count();
            $mtd_loan_amount = $field_agent->loans()->where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->sum('loan_amount');
            $mtd_loan_applied_amount = $field_agent->loans()->where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->sum('total_amount');

        // For branch
        } elseif (isset($request->branch)) {
            $branch = Branch::find($request->branch);
            $mtd_loans = $branch->loans()->where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->count();
            $mtd_loan_amount = $branch->loans()->where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->sum('loan_amount');
            $mtd_loan_applied_amount = $branch->loans()->where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->sum('total_amount');

        // For admin or other roles
        } elseif (auth()->user()->hasAnyRole(['admin', 'accountant', 'agent_care', 'collection_officer', 'sector_manager'])) {
            $mtd_loans = Loan::where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->count();
            $mtd_loan_amount = Loan::where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->sum('loan_amount');
            $mtd_loan_applied_amount = Loan::where('disbursed', true)
                ->whereBetween('disbursement_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])
                ->sum('total_amount');
        }

        return [
            'mtd_loans' => $mtd_loans,
            'mtd_loan_amount' => $mtd_loan_amount,
            'mtd_loan_applied_amount' => $mtd_loan_applied_amount
        ];
    }

    public function calculateFieldAgentCommission($branch_id = null, $field_agent_id = null)
    {
        $customer_count = 0;

        if ($field_agent_id) {
            $customer_count = Customer::where('field_agent_id', $field_agent_id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereIn('times_loan_applied', [0, 1]) 
                ->count();
        } elseif ($branch_id) {
            $customer_count = Customer::where('branch_id', $branch_id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereIn('times_loan_applied', [0, 1])
                ->count();
        }

        $commission = $customer_count * 250;

        return $commission;
    }



    // public function index(Request $request)
    // {
    //     //$this->data['month_array'] = $this->disbursement_chart_data($request);
    //     $this->data['month_array'] = 0;
    //     $this->data['payments_month_array'] = 0;

    //    // $this->data['payments_month_array'] = $this->repayment_chart_data($request);
    //     $this->data['field_agents'] = User::role('field_agent')->where('status', true)->get();
    //     $this->data['branches'] = Branch::query()->where('status', '=', true)->get();

    //     if ($request->branch == null) {
    //         $this->data['current_branch'] = 'all';
    //     } else {
    //         $this->data['current_branch'] = $request->branch;
    //     }
    //     if ($request->field_agent == null) {
    //         //$this->data['current_officer'] = 132;
    //         $this->data['current_officer'] = 'all';
    //     } else {
    //         $this->data['current_officer'] = $request->field_agent;
    //     }

    //     $branch_id = Branch::where('bname', 'Bungoma')->first()->id;

    //     $this->data['active_branch'] = $branch_id;

    //     return view('home.home2', $this->data);
    // }

    public function index1(Request $request)
    {
        $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        if ($request->branch && $request->branch != 'all') {
            $branch = Branch::where('id', $request->branch)->first();
            $customers = $branch->customers()->count();
            $loans = $branch->loans()->where(['settled' => false, 'disbursed' => true])->get();
            $lns_arr = [];
            foreach ($loans as $loan) {
                $last_payment_date = $loan->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lns_arr, $loan);
                }
            }
            $loans = count($lns_arr);

            $totalAmount = $branch->getActiveLoanBalanceAttribute();  //was getLoanBalanceAttribute()
            // $amount_paid = $branch->getTotalPaidAttribute();
            // $TotalLoanAmount = $branch->getTotalLoanAttribute();
            $amount_paid = $branch->getMonthTotalPaidAttribute();
            $TotalLoanAmount = $branch->getMonthTotalLoanAttribute();
            $due_today_count = $branch->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
            $due_today_amount = $branch->today_inst_amount();
            $pending_approval = $branch->loans()->where(['approved' => false, 'disbursed' => false])->count();
            $pending_disbursements = $branch->loans()->where(['approved' => true, 'disbursed' => false])->count();

            $branch_loans_w_arrears = $branch->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();

            $l = array();
            foreach ($branch_loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($l, $lns);
                }
            }
            $arrears_count = count($l);
            $arrears_total = 0;
            foreach ($l as $t) {
                $arrears_total += $t->total_arrears;
            }

            $mtd_loans = $branch->loans()->where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->get()->count();
            $mtd_loan_amount = $branch->loans()->where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->sum('loan_amount');

            //REMOVED ROLLED OVER LOANS LOGIC

            // $rolled_over_loans = $branch->loans()->whereHas('rollover', function (Builder $query) {
            //                                     $query->whereMonth('rollover_date', '=', Carbon::now())
            //                                         ->whereYear('rollover_date', '=', Carbon::now());
            //                                     })
            //                                     ->where(['settled' => false, 'disbursed' => true, 'rolled_over'=>true])->get();
            // $rolled_over_loans_count = $rolled_over_loans->count();
            // $rolled_over_balance = 0;
            // foreach ($rolled_over_loans as $lo){
            //     $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
            //     $product = Product::find($lo->product_id);
            //     if ($lo->rolled_over) {
            //         $rollover = Rollover::where('loan_id', $lo->id)->first();
            //         $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            //     } else {
            //         $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
            //     }
            //     $rolled_over_balance += $total - $payments;
            // }

            //non performing loans
            $non_performing_loans = array();
            $branch_unsettled_loans = $branch->loans()->where(['settled' => false, 'disbursed' => true])->get();
            foreach ($branch_unsettled_loans as $lns) {
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

            //new repayment percentage
            $rp_loans = $branch->loans()->where('disbursed', true)->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
            $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $installments = Installment::whereBetween('due_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();
            $paid = 0;
            $due = 0;
            foreach ($installments as $installment) {
                $paid += $installment->amount_paid;
                $due += $installment->total;
            }

            $this->data['mtd_loans'] = $mtd_loans;
            $this->data['mtd_loan_amount'] = $mtd_loan_amount;

            $this->data['non_performing_count'] = $non_performing_count;
            $this->data['non_performing_balance'] = $non_performing_balance;
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['current_branch'] = $branch->id;
            $this->data['current_officer'] = 'all';

        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $due_today_amount = 0;
            $totalAmount = 0;
            $amount_paid = 0;
            $TotalLoanAmount = 0;
            $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
            $customers = Customer::query()->whereIn('branch_id', $activeBranches)->get()->count();
            $loans = Loan::query()->where(['settled' => false, 'disbursed' => true])
                ->whereHas('customer', function ($q) use ($activeBranches) {
                    $q->whereIn('branch_id', $activeBranches);
                })
                ->get();
            $lns_arr = [];
            foreach ($loans as $loan) {
                $last_payment_date = $loan->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lns_arr, $loan);
                }
            }
            $loans = count($lns_arr);

            $due_today_count = Loan::where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now()->format('Y-m-d'));
            })->whereHas('customer', function ($q) use ($activeBranches) {
                $q->whereIn('branch_id', $activeBranches);
            })->count();

            $br = Branch::query()->where('status', '=', true)->get();
            foreach ($br as $r1) {
                $due_today_amount += $r1->today_inst_amount();
                $totalAmount += $r1->getActiveLoanBalanceAttribute();  //was getLoanBalanceAttribute()
                $amount_paid += $r1->getMonthTotalPaidAttribute();
                $TotalLoanAmount += $r1->getMonthTotalLoanAttribute();
            }

            $pending_approval = Loan::where(['approved' => false, 'disbursed' => false, 'source' => 'main'])->count();
            $pending_disbursements = Loan::where(['approved' => true, 'disbursed' => false])->count();

            $loans_with_arreas = Loan::where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->whereHas('customer', function ($q) use ($activeBranches) {
                $q->whereIn('branch_id', $activeBranches);
            })->get();
            $l = array();
            foreach ($loans_with_arreas as $lns) {
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($l, $lns);
                }
            }

            $arrears_count = count($l);
            $arrears_total = 0;
            foreach ($l as $t) {
                $arrears_total += $t->total_arrears;
            }

            $mtd_loans = Loan::where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->get()->count();
            $mtd_loan_amount = Loan::where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->sum('loan_amount');

            //non performing loans
            $unsettled_loans = Loan::where(['settled' => false, 'disbursed' => true])
                ->whereHas('customer', function ($q) use ($activeBranches) {
                    $q->whereIn('branch_id', $activeBranches);
                })->get();
            $non_performing_loans = array();
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

            //new repayment percentage
            $rp_loans = Loan::where('disbursed', true)
                ->whereHas('customer', function ($q) use ($activeBranches) {
                    $q->whereIn('branch_id', $activeBranches);
                })->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
            $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $installments = Installment::whereBetween('due_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();
            $paid = 0;
            $due = 0;
            foreach ($installments as $installment) {
                $paid += $installment->amount_paid;
                $due += $installment->total;
            }

            $this->data['mtd_loans'] = $mtd_loans;
            $this->data['mtd_loan_amount'] = $mtd_loan_amount;

            $this->data['non_performing_count'] = $non_performing_count;
            $this->data['non_performing_balance'] = $non_performing_balance;
            $this->data['non_performing_count'] = $non_performing_count;
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['current_branch'] = 'all';
            $this->data['current_officer'] = 'all';


        } elseif (Auth::user()->hasRole('field_agent')) {
            $user = Auth::user();
            $due_today_amount = 0;
            $totalAmount = 0;
            $customers = Customer::where('field_agent_id', Auth::user()->id)->count();
            $loans = $user->loans()->where(['settled' => false, 'disbursed' => true])->get();
            $lns_arr = [];
            foreach ($loans as $loan) {
                $last_payment_date = $loan->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lns_arr, $loan);
                }
            }
            $loans = count($lns_arr);

            $due_today_count = $user->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
            /*  $br = Branch::all();
              foreach ($br as $r1){
                  $due_today_amount += $r1->today_loan_amount();
                  $totalAmount += $r1->getTotalLoanAttribute();
              }*/
            $due_today_amount = $user->today_inst_amount();
            $totalAmount = $user->getActiveLoanBalanceAttribute();  //was getLoanBalanceAttribute()
            $amount_paid = $user->getMonthTotalPaidAttribute();
            $TotalLoanAmount = $user->getMonthTotalLoanAttribute();

            $pending_approval = $user->loans()->where(['approved' => false, 'disbursed' => false])->count();
            $pending_disbursements = $user->loans()->where(['approved' => true, 'disbursed' => false])->count();

            $l = array();
            $loans_with_arreas = $user->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();
            foreach ($loans_with_arreas as $lns) {
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($l, $lns);
                }

            }
            $arrears_count = count($l);
            $arrears_total = 0;
            foreach ($l as $t) {
                $arrears_total += $t->total_arrears;
            }

            $mtd_loans = $user->loans()->where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->get()->count();
            $mtd_loan_amount = $user->loans()->where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->sum('loan_amount');

            //non performing loans
            $user_unsettled_loans = $user->loans()->where(['settled' => false, 'disbursed' => true])->get();
            $non_performing_loans = array();
            foreach ($user_unsettled_loans as $lns) {
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

            //new repayment percentage
            $rp_loans = $user->loans()->where('disbursed', true)->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
            $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $installments = Installment::whereBetween('due_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();
            $paid = 0;
            $due = 0;
            foreach ($installments as $installment) {
                $paid += $installment->amount_paid;
                $due += $installment->total;
            }

            $this->data['mtd_loans'] = $mtd_loans;
            $this->data['mtd_loan_amount'] = $mtd_loan_amount;

            $this->data['non_performing_balance'] = $non_performing_balance;
            $this->data['non_performing_count'] = $non_performing_count;

        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $customers = $branch->customers()->count();
            $loans = $branch->loans()->where(['settled' => false, 'disbursed' => true])->get();
            $lns_arr = [];
            foreach ($loans as $loan) {
                $last_payment_date = $loan->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lns_arr, $loan);
                }
            }
            $loans = count($lns_arr);

            $totalAmount = $branch->getActiveLoanBalanceAttribute();  //was getLoanBalanceAttribute()
            $amount_paid = $branch->getMonthTotalPaidAttribute();
            $TotalLoanAmount = $branch->getMonthTotalLoanAttribute();

            $due_today_count = $branch->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
            $due_today_amount = $branch->today_inst_amount();
            $pending_approval = $branch->loans()->where(['approved' => false, 'disbursed' => false])->count();
            $pending_disbursements = $branch->loans()->where(['approved' => true, 'disbursed' => false])->count();

            $l = array();
            $loans_with_arreas = $branch->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();
            foreach ($loans_with_arreas as $lns) {
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($l, $lns);
                }

            }
            $arrears_count = count($l);
            $arrears_total = 0;
            foreach ($l as $t) {
                $arrears_total += $t->total_arrears;
            }

            $mtd_loans = $branch->loans()->where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->get()->count();
            $mtd_loan_amount = $branch->loans()->where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->sum('loan_amount');

            //non performing loans
            $non_performing_loans = array();
            $branch_unsettled_loans = $branch->loans()->where(['settled' => false, 'disbursed' => true])->get();
            foreach ($branch_unsettled_loans as $lns) {
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
            //new repayment percentage
            $rp_loans = $branch->loans()->where('disbursed', true)->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
            $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $installments = Installment::whereBetween('due_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();
            $paid = 0;
            $due = 0;
            foreach ($installments as $installment) {
                $paid += $installment->amount_paid;
                $due += $installment->total;
            }
            $this->data['mtd_loans'] = $mtd_loans;
            $this->data['mtd_loan_amount'] = $mtd_loan_amount;

            $this->data['non_performing_balance'] = $non_performing_balance;
            $this->data['non_performing_count'] = $non_performing_count;
        }

        //area chart
        $this->data['customer_info'] = [];

        $months = [
            [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];
        $current_month = Carbon::now()->format('M');

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            if ($request->branch && $request->branch != 'all') {
                $customer = Customer::where('branch_id', $request->branch)->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->get();
            } else {
                $customer = Customer::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->get();
            }
        } elseif (Auth::user()->hasRole('field_agent')) {
            $customer = Customer::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('field_agent_id', Auth::user()->id)->get();
        } else {
            $customer = $branch->customers()->whereMonth('customers.created_at', date('m'))->whereYear('customers.created_at', date('Y'))->get();
        }
        $selc = [$current_month, $customer->count()];
        array_push($this->data['customer_info'], $selc);

        $this->data['month_array'] = $this->disbursement_chart_data($request);

        $this->data['payments_month_array'] = $this->repayment_chart_data($request);

        //repayment rate
        if ($due > 0) {
            $repayment_rate = ($paid / $due) * 100;

        } else {
            $repayment_rate = 0;
        }
        //online users
        $system_users = User::get(['id'])->each->setAppends([]);
        $online_count = 0;
        foreach ($system_users as $user) {
            if (Cache::has('is_online' . $user->id)) {
                $online_count = $online_count + 1;
            }
        }

        $this->data['online_count'] = $online_count;
        $this->data['amount_paid'] = $amount_paid;
        $this->data['TotalLoanAmount'] = $TotalLoanAmount;
        $this->data['repayment_rate'] = number_format($repayment_rate, 2);

        $this->data['dashboard'] = "set";
        $this->data['customers'] = $customers;
        $this->data['loans'] = $loans;
        $this->data['totalAmount'] = $totalAmount;
        $this->data['due_today_count'] = $due_today_count;
        $this->data['due_today_amount'] = $due_today_amount;
        $this->data['pending_approval'] = $pending_approval;
        $this->data['pending_disbursements'] = $pending_disbursements;
        $this->data['arrears_count'] = $arrears_count;
        $this->data['arrears_total'] = $arrears_total;

        $this->data['mtd_loans'] = $mtd_loans;
        $this->data['mtd_loan_amount'] = $mtd_loan_amount;
        $this->data['non_performing_balance'] = $non_performing_balance;
        $this->data['non_performing_count'] = $non_performing_count;

        $this->data['field_agents'] = User::role('field_agent')->where('status', true)->get();
        if ($totalAmount > 0) {
            $this->data['PAR'] = (int)(($arrears_total / $totalAmount) * 100);
        } else {
            $this->data['PAR'] = 0;
        }
        return view('home.home2', $this->data);
    }

    public function field_agent_filter1(Request $request)
    {

    }

    public function field_agent_filter(Request $request)
    {
        $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');

        if ($request->field_agent != null && $request->field_agent != 'all') {

            $field_agent_id = $request->field_agent;
            $field_agent = User::where('id', $field_agent_id)->first();
            $user = $field_agent;
            $due_today_amount = 0;
            $totalAmount = 0;
            $customers = Customer::where('field_agent_id', $field_agent->id)->count();
            $loans = $user->loans()->where(['settled' => false, 'disbursed' => true])->get();
            $lns_arr = [];
            foreach ($loans as $loan) {
                $last_payment_date = $loan->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lns_arr, $loan);
                }
            }
            $loans = count($lns_arr);

            $due_today_count = $user->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();

            $due_today_amount = $user->today_inst_amount();
            $totalAmount = $user->getActiveLoanBalanceAttribute();  //was getLoanBalanceAttribute()
            $amount_paid = $user->getMonthTotalPaidAttribute();
            $TotalLoanAmount = $user->getMonthTotalLoanAttribute();


            $pending_approval = $user->loans()->where(['approved' => false, 'disbursed' => false])->count();
            $pending_disbursements = $user->loans()->where(['approved' => true, 'disbursed' => false])->count();

            $loans_w_arrears = $user->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();
            $l = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($l, $lns);
                }
            }
            $arrears_count = count($l);
            $arrears_total = 0;
            foreach ($l as $t) {
                $arrears_total += $t->total_arrears;
            }

            $mtd_loans = $user->loans()->where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->get()->count();
            $mtd_loan_amount = $user->loans()->where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->sum('loan_amount');

            //non performing loans
            $non_performing_loans = array();
            $unsettled_loans = $user->loans()->where(['settled' => false, 'disbursed' => true])->get();
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
            //new repayment percentage
            $rp_loans = $user->loans()->where('disbursed', true)->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
            $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $installments = Installment::whereBetween('due_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();
            $paid = 0;
            $due = 0;
            foreach ($installments as $installment) {
                $paid += $installment->amount_paid;
                $due += $installment->total;
            }

            $this->data['mtd_loans'] = $mtd_loans;
            $this->data['mtd_loan_amount'] = $mtd_loan_amount;
            $this->data['non_performing_balance'] = $non_performing_balance;
            $this->data['non_performing_count'] = $non_performing_count;
            $this->data['current_officer'] = $field_agent->id;
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['current_branch'] = 'all';

        } else {
            $due_today_amount = 0;
            $totalAmount = 0;
            $amount_paid = 0;
            $TotalLoanAmount = 0;
            $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
            $customers = Customer::query()->whereIn('branch_id', $activeBranches)->get()->count();
            $loans = Loan::query()->where(['settled' => false, 'disbursed' => true])
                ->whereHas('customer', function ($q) use ($activeBranches) {
                    $q->whereIn('branch_id', $activeBranches);
                })
                ->get();
            $lns_arr = [];
            foreach ($loans as $loan) {
                $last_payment_date = $loan->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lns_arr, $loan);
                }
            }
            $loans = count($lns_arr);

            $due_today_count = Loan::where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
            $br = Branch::query()->where('status', '=', true)->get();
            foreach ($br as $r1) {
                $due_today_amount += $r1->today_inst_amount();
                $totalAmount += $r1->getActiveLoanBalanceAttribute();  //was getLoanBalanceAttribute()
                $amount_paid += $r1->getMonthTotalPaidAttribute();
                $TotalLoanAmount += $r1->getMonthTotalLoanAttribute();
            }
            $pending_approval = Loan::where(['approved' => false, 'disbursed' => false])->count();
            $pending_disbursements = Loan::where(['approved' => true, 'disbursed' => false])->count();
            $l = array();
            $loans_w_arrears = Loan::where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->whereHas('customer', function ($q) use ($activeBranches) {
                $q->whereIn('branch_id', $activeBranches);
            })->get();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($l, $lns);
                }
            }
            $arrears_count = count($l);
            $arrears_total = 0;
            foreach ($l as $t) {
                $arrears_total += $t->total_arrears;
            }
            $mtd_loans = Loan::where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->get()->count();
            $mtd_loan_amount = Loan::where('disbursed', true)->whereBetween('disbursement_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->sum('loan_amount');
            //non performing loans
            $unsettled_loans = Loan::where(['settled' => false, 'disbursed' => true])
                ->whereHas('customer', function ($q) use ($activeBranches) {
                    $q->whereIn('branch_id', $activeBranches);
                })->get();
            $non_performing_loans = array();
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

            //new repayment percentage
            $rp_loans = Loan::where('disbursed', true)
                ->whereHas('customer', function ($q) use ($activeBranches) {
                    $q->whereIn('branch_id', $activeBranches);
                })->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
            $firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $installments = Installment::whereBetween('due_date', [$firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();
            $paid = 0;
            $due = 0;
            foreach ($installments as $installment) {
                $paid += $installment->amount_paid;
                $due += $installment->total;
            }

            $this->data['mtd_loans'] = $mtd_loans;
            $this->data['mtd_loan_amount'] = $mtd_loan_amount;

            $this->data['non_performing_count'] = $non_performing_count;
            $this->data['non_performing_balance'] = $non_performing_balance;
            $this->data['non_performing_count'] = $non_performing_count;
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['current_branch'] = 'all';
            $this->data['current_officer'] = 'all';

        }
        //dd($branch);


        //area chart
        $this->data['customer_info'] = [];

        $months = [
            [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];

        $current_month = Carbon::now()->format('M');

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            if ($request->field_agent && $request->field_agent != 'all') {
                $customer = Customer::where('field_agent_id', $request->field_agent)->whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->get();

            } else {
                $customer = Customer::whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->get();
            }
        } elseif (Auth::user()->hasRole('field_agent')) {
            $customer = Customer::whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->where('field_agent_id', Auth::user()->id)->get();

        } else {
            $customer = Customer::whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->get();
        }
        $selc = [$current_month, $customer->count()];

        array_push($this->data['customer_info'], $selc);

        $this->data['month_array'] = $this->co_disbursement_chart_data($request);

        $this->data['payments_month_array'] = $this->co_repayment_chart_data($request);

        //repayment rate
        if ($due > 0) {
            $repayment_rate = ($paid / $due) * 100;

        } else {
            $repayment_rate = 0;
        }

        //online users
        $system_users = User::get(['id'])->each->setAppends([]);
        $online_count = 0;
        foreach ($system_users as $user) {
            if (Cache::has('is_online' . $user->id)) {
                $online_count = $online_count + 1;
            }
        }

        $this->data['online_count'] = $online_count;

        // dd(number_format($amount_paid), number_format($totalAmount), number_format($TotalLoanAmount));
        $this->data['amount_paid'] = $amount_paid;
        $this->data['TotalLoanAmount'] = $TotalLoanAmount;
        $this->data['repayment_rate'] = number_format($repayment_rate, 2);

        $this->data['dashboard'] = "set";
        $this->data['customers'] = $customers;
        $this->data['loans'] = $loans;
        $this->data['totalAmount'] = $totalAmount;
        $this->data['due_today_count'] = $due_today_count;
        $this->data['due_today_amount'] = $due_today_amount;
        $this->data['pending_approval'] = $pending_approval;
        $this->data['pending_disbursements'] = $pending_disbursements;
        $this->data['arrears_count'] = $arrears_count;
        $this->data['arrears_total'] = $arrears_total;

        $this->data['mtd_loans'] = $mtd_loans;
        $this->data['mtd_loan_amount'] = $mtd_loan_amount;

        $this->data['non_performing_balance'] = $non_performing_balance;
        $this->data['non_performing_count'] = $non_performing_count;

        $this->data['field_agents'] = User::role('field_agent')->where('status', true)->get();

        if ($totalAmount > 0) {
            $this->data['PAR'] = (int)(($arrears_total / $totalAmount) * 100);
        } else {
            $this->data['PAR'] = 0;
        }
        return view('home.home', $this->data);
    }

    public function disbursedLoansFilter(Request $request)
    {
        if (Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant') or Auth::user()->hasRole('sector_manager') or Auth::user()->hasRole('customer_informant')) {
            $start = Loan::first()->disbursement_date;
            $start = Carbon::parse($start);
            $end = Carbon::now();
            $sub = $end->diffInMonths($start);
            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->addMonth();
                array_push($months, $next->format('M-Y'));
            }
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['lfs'] = User::role('field_agent')->where('status', '=', true)->get();
        } elseif (Auth::user()->hasRole('field_agent')) {
            $user = Auth::user();
            $start = $user->loans()->first()->disbursement_date;
            $start = Carbon::parse($start);
            $end = Carbon::now();
            $sub = $end->diffInMonths($start);
            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->addMonth(1);
                array_push($months, $next->format('M-Y'));
            }
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $start = $branch->loans()->first()->disbursement_date;
            $start = Carbon::parse($start);
            $end = Carbon::now();
            $sub = $end->diffInMonths($start);
            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->addMonth(1);
                array_push($months, $next->format('M-Y'));
            }
        }

        $this->data['months'] = array_reverse($months);
        //if branch is specified and loan officer isn't
        if ($request->branch_id and $request->branch_id != 'all' and $request->lf == 'all') {
            $month = Carbon::parse($request->month);
            $branch = Branch::find($request->branch_id);
            $loans_dates = $branch->loans()->where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->orderBy('disbursement_date', 'ASC')->pluck('disbursement_date');
            $month_array = array();
            $loans_dates = json_decode($loans_dates);

            if (!empty($loans_dates)) {
                foreach ($loans_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_loan_count_array = array();
            $month_name_array = array();
            $monthly_loan_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_loan_count = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->get()->count();
                    $monthly_loan_amount = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->sum('loan_amount');
                    array_push($monthly_loan_count_array, $monthly_loan_count);
                    array_push($monthly_loan_amount_array, $monthly_loan_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_loan_count_array)) {
                $max_disb_no = max($monthly_loan_count_array);
                $max_disbursement = round(($max_disb_no + 10 / 2) / 10) * 10;
            } else {
                $max_disb_no = 0;
                $max_disbursement = 0;
            }

            if (!empty($monthly_loan_amount_array)) {
                $max_amount_no = max($monthly_loan_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_loan_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_loan_count_array,
                'loan_amount' => $monthly_loan_amount_array,
                'max_disbursement' => $max_disbursement,
                'max_amount' => $max_amount,
            );
            $this->data['current'] = $request->month;
            $this->data['current_branch'] = $request->branch_id;
            $this->data['current_lf'] = 'all';

            $disbAmount = array_sum($monthly_loan_amount_array);
            $this->data['disbAmount'] = $disbAmount;
            $this->data['disbCount'] = array_sum($monthly_loan_count_array);
        } //if lf is specified and branch isn't
        elseif ($request->lf and $request->lf != 'all' and $request->branch_id == 'all') {
            $month = Carbon::parse($request->month);
            $user = User::find($request->lf);
            $loans_dates = $user->loans()->where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->orderBy('disbursement_date', 'ASC')->pluck('disbursement_date');
            $month_array = array();
            $loans_dates = json_decode($loans_dates);

            if (!empty($loans_dates)) {
                foreach ($loans_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_loan_count_array = array();
            $month_name_array = array();
            $monthly_loan_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->get()->count();
                    $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->sum('loan_amount');
                    array_push($monthly_loan_count_array, $monthly_loan_count);
                    array_push($monthly_loan_amount_array, $monthly_loan_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_loan_count_array)) {
                $max_disb_no = max($monthly_loan_count_array);
                $max_disbursement = round(($max_disb_no + 10 / 2) / 10) * 10;
            } else {
                $max_disb_no = 0;
                $max_disbursement = 0;
            }

            if (!empty($monthly_loan_amount_array)) {
                $max_amount_no = max($monthly_loan_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_loan_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_loan_count_array,
                'loan_amount' => $monthly_loan_amount_array,
                'max_disbursement' => $max_disbursement,
                'max_amount' => $max_amount,
            );
            $this->data['current'] = $request->month;
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = $request->lf;

            $disbAmount = array_sum($monthly_loan_amount_array);
            $this->data['disbAmount'] = $disbAmount;
            $this->data['disbCount'] = array_sum($monthly_loan_count_array);
        } //if both lf and branch are specified
        elseif ($request->lf and $request->branch_id and $request->lf != 'all' and $request->branch_id != 'all') {
            $month = Carbon::parse($request->month);
            $user = User::find($request->lf);
            $branch = Branch::find($request->branch_id);
            if ($user->branch_id != $branch->id) {
                $monthly_loan_data_array = array(
                    'month' => [],
                    'post_count_data' => [],
                    'loan_amount' => [],
                    'max_disbursement' => 0,
                    'max_amount' => 0,
                );
                $this->data['month_array'] = json_encode($monthly_loan_data_array);
                $this->data['current'] = $request->month;
                $this->data['current_branch'] = $request->branch_id;
                $this->data['current_lf'] = $request->lf;
                $this->data['disbAmount'] = 0;
                $this->data['disbCount'] = 0;

                return view('home.disbursement_filter', $this->data);
            }
            $loans_dates = $user->loans()->where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->orderBy('disbursement_date', 'ASC')->pluck('disbursement_date');
            $month_array = array();
            $loans_dates = json_decode($loans_dates);

            if (!empty($loans_dates)) {
                foreach ($loans_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_loan_count_array = array();
            $month_name_array = array();
            $monthly_loan_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->get()->count();
                    $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->sum('loan_amount');
                    array_push($monthly_loan_count_array, $monthly_loan_count);
                    array_push($monthly_loan_amount_array, $monthly_loan_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_loan_count_array)) {
                $max_disb_no = max($monthly_loan_count_array);
                $max_disbursement = round(($max_disb_no + 10 / 2) / 10) * 10;
            } else {
                $max_disb_no = 0;
                $max_disbursement = 0;
            }

            if (!empty($monthly_loan_amount_array)) {
                $max_amount_no = max($monthly_loan_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_loan_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_loan_count_array,
                'loan_amount' => $monthly_loan_amount_array,
                'max_disbursement' => $max_disbursement,
                'max_amount' => $max_amount,
            );
            $this->data['current'] = $request->month;
            $this->data['current_branch'] = $request->branch_id;
            $this->data['current_lf'] = $request->lf;

            $disbAmount = array_sum($monthly_loan_amount_array);
            $this->data['disbAmount'] = $disbAmount;
            $this->data['disbCount'] = array_sum($monthly_loan_count_array);
        }
        //month only
        //elseif ($request->lf == 'all' and $request->branch_id == 'all'){
        elseif ($request->month) {
            $month = Carbon::parse($request->month);
            $loans_dates = Loan::where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->orderBy('disbursement_date', 'ASC')->pluck('disbursement_date');
            $month_array = array();
            $loans_dates = json_decode($loans_dates);

            if (!empty($loans_dates)) {
                foreach ($loans_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_loan_count_array = array();
            $month_name_array = array();
            $monthly_loan_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                        $monthly_loan_count = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->get()->count();
                        $monthly_loan_amount = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->sum('loan_amount');
                    } elseif (Auth::user()->hasRole('field_agent')) {
                        $user = Auth::user();
                        $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->get()->count();
                        $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->sum('loan_amount');

                    } else {
                        $branch = Branch::where('id', Auth::user()->branch_id)->first();
                        $monthly_loan_count = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->get()->count();
                        $monthly_loan_amount = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $month)->sum('loan_amount');
                    }
                    array_push($monthly_loan_count_array, $monthly_loan_count);
                    array_push($monthly_loan_amount_array, $monthly_loan_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_loan_count_array)) {
                $max_disb_no = max($monthly_loan_count_array);
                $max_disbursement = round(($max_disb_no + 10 / 2) / 10) * 10;
            } else {
                $max_disb_no = 0;
                $max_disbursement = 0;
            }

            if (!empty($monthly_loan_amount_array)) {
                $max_amount_no = max($monthly_loan_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_loan_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_loan_count_array,
                'loan_amount' => $monthly_loan_amount_array,
                'max_disbursement' => $max_disbursement,
                'max_amount' => $max_amount,
            );
            $this->data['current'] = $month->format('M-Y');

            $disbAmount = array_sum($monthly_loan_amount_array);
            $this->data['disbAmount'] = $disbAmount;
            $this->data['disbCount'] = array_sum($monthly_loan_count_array);
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = 'all';
        } //default page data
        else {
            $loans_dates = Loan::where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->orderBy('disbursement_date', 'ASC')->pluck('disbursement_date');
            $month_array = array();
            $loans_dates = json_decode($loans_dates);

            if (!empty($loans_dates)) {
                foreach ($loans_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_loan_count_array = array();
            $month_name_array = array();
            $monthly_loan_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                        $monthly_loan_count = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                        $monthly_loan_amount = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                    } elseif (Auth::user()->hasRole('field_agent')) {
                        $user = Auth::user();
                        $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                        $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');

                    } else {
                        $branch = Branch::where('id', Auth::user()->branch_id)->first();
                        $monthly_loan_count = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                        $monthly_loan_amount = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                    }
                    array_push($monthly_loan_count_array, $monthly_loan_count);
                    array_push($monthly_loan_amount_array, $monthly_loan_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_loan_count_array)) {
                $max_disb_no = max($monthly_loan_count_array);
                $max_disbursement = round(($max_disb_no + 10 / 2) / 10) * 10;
            } else {
                $max_disb_no = 0;
                $max_disbursement = 0;
            }

            if (!empty($monthly_loan_amount_array)) {
                $max_amount_no = max($monthly_loan_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_loan_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_loan_count_array,
                'loan_amount' => $monthly_loan_amount_array,
                'max_disbursement' => $max_disbursement,
                'max_amount' => $max_amount,
            );
            $this->data['current'] = $start->format('M-Y');

            $disbAmount = array_sum($monthly_loan_amount_array);
            $this->data['disbAmount'] = $disbAmount;
            $this->data['disbCount'] = array_sum($monthly_loan_count_array);
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = 'all';
        }

        $this->data['month_array'] = json_encode($monthly_loan_data_array);

        return view('home.disbursement_filter', $this->data);
    }

    public function loanRepaymentsFilter(Request $request)
    {
        $start = Carbon::now();
        if (Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant') or Auth::user()->hasRole('sector_manager') or Auth::user()->hasRole('customer_informant')) {
            if (Payment::first()) {
                $start = Payment::first()->date_payed;
                $start = Carbon::parse($start);
            }
            $end = Carbon::now()->subMonths(6);
            $sub = $end->diffInMonths($start);

            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->subMonth();
                array_push($months, $next->format('M-Y'));
            }
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['lfs'] = User::role('field_agent')->where('status', '=', true)->get();
        } elseif (Auth::user()->hasRole('field_agent')) {
            $user = Auth::user();
            if (Payment::where('loan_id', '=', $user->loans()->first()->id)->first()) {
                $start = Payment::where('loan_id', '=', $user->loans()->first()->id)->first()->date_payed;
                $start = Carbon::parse($start);
            }
            $end = Carbon::now();
            $sub = $end->diffInMonths($start);
            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->subMonth();
                array_push($months, $next->format('M-Y'));
            }
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            if (Payment::where('loan_id', '=', $branch->loans()->first()->id)->first()) {
                $start = Payment::where('loan_id', '=', $branch->loans()->first()->id)->first()->date_payed;
                $start = Carbon::parse($start);
            }
            $end = Carbon::now();
            $sub = $end->diffInMonths($start);
            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->subMonth();
                array_push($months, $next->format('M-Y'));
            }
        }

        $this->data['months'] = $months;
        //if branch is specified and loan officer isn't
        if ($request->branch_id and $request->branch_id != 'all' and $request->lf == 'all') {
            $month = Carbon::parse($request->month);
            $branch = Branch::find($request->branch_id);
            $payment_dates = Payment::whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);
            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }
            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );
            $this->data['current'] = $request->month;
            $this->data['current_branch'] = $request->branch_id;
            $this->data['current_lf'] = 'all';

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);

            $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

        } //if lf is specified and branch isn't
        elseif ($request->lf and $request->lf != 'all' and $request->branch_id == 'all') {
            $month = Carbon::parse($request->month);
            $user = User::find($request->lf);
            $payment_dates = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);
            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->sum('amount');
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }

            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );

            $this->data['current'] = $request->month;
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = $request->lf;

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);

            $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

        } //if both lf and branch are specified
        elseif ($request->lf and $request->branch_id and $request->lf != 'all' and $request->branch_id != 'all') {
            $month = Carbon::parse($request->month);
            $user = User::find($request->lf);
            $branch = Branch::find($request->branch_id);
            if ($user->branch_id != $branch->id) {
                $monthly_repayment_data_array = array(
                    'month' => [],
                    'post_count_data' => [],
                    'repayment_amount' => [],
                    'max_repayment' => 0,
                    'max_amount' => 0,
                );
                $this->data['month_array'] = json_encode($monthly_repayment_data_array);
                $this->data['current'] = $request->month;
                $this->data['current_branch'] = $request->branch_id;
                $this->data['current_lf'] = $request->lf;
                $this->data['repaymentAmount'] = 0;
                $this->data['repaymentCount'] = 0;
                $this->data['loan_processing_fee_count'] = 0;
                $this->data['loan_processing_fee_amount'] = 0;

                $this->data['loan_settlements_count'] = 0;
                $this->data['loan_settlements_amount'] = 0;


                return view('home.repayment_filter', $this->data);
            }
            $payment_dates = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);
            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->sum('amount');
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }

            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );

            $this->data['current'] = $request->month;
            $this->data['current_branch'] = $request->branch_id;
            $this->data['current_lf'] = $request->lf;

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);

            $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

        }
        // month only
        // elseif ($request->lf == 'all' and $request->branch_id == 'all'){
        elseif ($request->month) {
            $month = Carbon::parse($request->month);
            $payment_dates = Payment::whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);
            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                        $monthly_payment_count = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                        $monthly_payment_amount = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->sum('amount');
                    } elseif (Auth::user()->hasRole('field_agent')) {
                        $user = Auth::user();
                        $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->sum('amount');
                    } else {
                        $branch = Branch::where('id', Auth::user()->branch_id)->first();
                        $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                    }
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }

            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );

            $this->data['current'] = $month->format('M-Y');
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = 'all';

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);

            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                $this->data['loan_processing_fee_count'] = Payment::where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_settlements_amount'] = Payment::where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            } elseif (Auth::user()->hasRole('field_agent')) {
                $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            } else {
                $branch = Branch::where('id', Auth::user()->branch_id)->first();
                $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
            }
        } //default page data
        else {

            $payment_dates = Payment::whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);

            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                        if ($request->branch && $request->branch != 'all') {
                            $branch = Branch::find($request->branch);
                            $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                            $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                        } else {
                            $monthly_payment_count = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                            $monthly_payment_amount = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');
                        }
                    } elseif (Auth::user()->hasRole('field_agent')) {
                        $user = Auth::user();
                        $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');

                    } else {
                        $branch = Branch::where('id', Auth::user()->branch_id)->first();
                        $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                    }
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }

            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }


            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );

            $this->data['current'] = now()->format('M-Y');
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = 'all';

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                $this->data['loan_processing_fee_count'] = Payment::where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_settlements_amount'] = Payment::where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');

            } elseif (Auth::user()->hasRole('field_agent')) {
                $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');

            } else {
                $branch = Branch::where('id', Auth::user()->branch_id)->first();
                $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
            }

        }

        $this->data['month_array'] = json_encode($monthly_repayment_data_array);

        return view('home.repayment_filter', $this->data);
    }

    public function fieldAgentFilter(Request $request)
    {
        $this->data['periods'] = [
            'Today',
            'Yesterday',
            'This Week',
            'This Month',
            'Last Month',
            'Last 3 Months',
            'Last 6 Months',
            'Last Year',
            'Last 3 Years',
            'Last 5 Years',
            'All Time',
        ];

        $this->data['lfs'] = User::role('field_agent')->where('status', '=', true)->get();

        $start = Carbon::now();
        if (Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant') or Auth::user()->hasRole('sector_manager') or Auth::user()->hasRole('customer_informant')) {
            if (Payment::first()) {
                $start = Payment::first()->date_payed;
                $start = Carbon::parse($start);
            }
            $end = Carbon::now()->subMonths(6);
            $sub = $end->diffInMonths($start);

            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->subMonth();
                array_push($months, $next->format('M-Y'));
            }
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();

        } elseif (Auth::user()->hasRole('field_agent')) {
            $user = Auth::user();
            if (Payment::where('loan_id', '=', $user->loans()->first()->id)->first()) {
                $start = Payment::where('loan_id', '=', $user->loans()->first()->id)->first()->date_payed;
                $start = Carbon::parse($start);
            }
            $end = Carbon::now();
            $sub = $end->diffInMonths($start);
            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->subMonth();
                array_push($months, $next->format('M-Y'));
            }
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            if (Payment::where('loan_id', '=', $branch->loans()->first()->id)->first()) {
                $start = Payment::where('loan_id', '=', $branch->loans()->first()->id)->first()->date_payed;
                $start = Carbon::parse($start);
            }
            $end = Carbon::now();
            $sub = $end->diffInMonths($start);
            $months = array($start->format('M-Y'));
            for ($i = 0; $i <= $sub; ++$i) {
                $next = $start->subMonth();
                array_push($months, $next->format('M-Y'));
            }
        }

        $this->data['months'] = $months;
        //if branch is specified and loan officer isn't
        if ($request->branch_id and $request->branch_id != 'all' and $request->lf == 'all') {
            $month = Carbon::parse($request->month);
            $branch = Branch::find($request->branch_id);
            $payment_dates = Payment::whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);
            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }
            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );
            $this->data['current'] = $request->month;
            $this->data['current_branch'] = $request->branch_id;
            $this->data['current_lf'] = 'all';

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);

            $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

        } //if lf is specified and branch isn't
        elseif ($request->lf and $request->lf != 'all' and $request->branch_id == 'all') {
            $month = Carbon::parse($request->month);
            $user = User::find($request->lf);
            $payment_dates = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);
            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->sum('amount');
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }

            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );

            $this->data['current'] = $request->month;
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = $request->lf;

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);

            $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

        } //if both lf and branch are specified
        elseif ($request->lf and $request->branch_id and $request->lf != 'all' and $request->branch_id != 'all') {
            $month = Carbon::parse($request->month);
            $user = User::find($request->lf);
            $branch = Branch::find($request->branch_id);
            if ($user->branch_id != $branch->id) {
                $monthly_repayment_data_array = array(
                    'month' => [],
                    'post_count_data' => [],
                    'repayment_amount' => [],
                    'max_repayment' => 0,
                    'max_amount' => 0,
                );
                $this->data['month_array'] = json_encode($monthly_repayment_data_array);
                $this->data['current'] = $request->month;
                $this->data['current_branch'] = $request->branch_id;
                $this->data['current_lf'] = $request->lf;
                $this->data['repaymentAmount'] = 0;
                $this->data['repaymentCount'] = 0;
                $this->data['loan_processing_fee_count'] = 0;
                $this->data['loan_processing_fee_amount'] = 0;

                $this->data['loan_settlements_count'] = 0;
                $this->data['loan_settlements_amount'] = 0;


                return view('home.repayment_filter', $this->data);
            }
            $payment_dates = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);
            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->sum('amount');
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }

            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );

            $this->data['current'] = $request->month;
            $this->data['current_branch'] = $request->branch_id;
            $this->data['current_lf'] = $request->lf;

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);

            $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
            $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

        } elseif ($request->month) {
            $month = Carbon::parse($request->month);
            $payment_dates = Payment::whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);
            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                        $monthly_payment_count = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                        $monthly_payment_amount = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->sum('amount');
                    } elseif (Auth::user()->hasRole('field_agent')) {
                        $user = Auth::user();
                        $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->sum('amount');
                    } else {
                        $branch = Branch::where('id', Auth::user()->branch_id)->first();
                        $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                    }
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }

            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }

            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );

            $this->data['current'] = $month->format('M-Y');
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = 'all';

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);

            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                $this->data['loan_processing_fee_count'] = Payment::where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_settlements_amount'] = Payment::where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            } elseif (Auth::user()->hasRole('field_agent')) {
                $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');

            } else {
                $branch = Branch::where('id', Auth::user()->branch_id)->first();
                $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->count();
                $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', $month)->whereYear('date_payed', $month)->get()->sum('amount');
            }
        } //default page data
        else {
            $payment_dates = Payment::whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->orderBy('date_payed', 'ASC')->pluck('date_payed');
            $month_array = array();
            $payment_dates = json_decode($payment_dates);

            if (!empty($payment_dates)) {
                foreach ($payment_dates as $unformatted_date) {
                    $date = new \DateTime($unformatted_date);
                    $day = $date->format('d');
                    $month_name = $date->format('d-M');
                    $month_array[$day] = $month_name;
                }
            }
            $monthly_payment_count_array = array();
            $month_name_array = array();
            $monthly_payment_amount_array = array();
            if (!empty($month_array)) {
                foreach ($month_array as $day => $month_name) {
                    if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                        if ($request->branch && $request->branch != 'all') {
                            $branch = Branch::find($request->branch);
                            $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                            $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                        } else {
                            $monthly_payment_count = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                            $monthly_payment_amount = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');
                        }
                    } elseif (Auth::user()->hasRole('field_agent')) {
                        $user = Auth::user();
                        $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');

                    } else {
                        $branch = Branch::where('id', Auth::user()->branch_id)->first();
                        $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                    }
                    array_push($monthly_payment_count_array, $monthly_payment_count);
                    array_push($monthly_payment_amount_array, $monthly_payment_amount);
                    array_push($month_name_array, $month_name);
                }
            }
            if (!empty($monthly_payment_count_array)) {
                $max_repayment_no = max($monthly_payment_count_array);
                $max_repayment = round(($max_repayment_no + 10 / 2) / 10) * 10;
            } else {
                $max_repayment_no = 0;
                $max_repayment = 0;
            }

            if (!empty($monthly_payment_amount_array)) {
                $max_amount_no = max($monthly_payment_amount_array);
                $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
            } else {
                $max_amount_no = 0;
                $max_amount = 0;
            }


            $monthly_repayment_data_array = array(
                'month' => $month_name_array,
                'post_count_data' => $monthly_payment_count_array,
                'repayment_amount' => $monthly_payment_amount_array,
                'max_repayment' => $max_repayment,
                'max_amount' => $max_amount,
            );

            $this->data['current'] = now()->format('M-Y');
            $this->data['current_branch'] = 'all';
            $this->data['current_lf'] = 'all';

            $repaymentAmount = array_sum($monthly_payment_amount_array);
            $this->data['repaymentAmount'] = $repaymentAmount;
            $this->data['repaymentCount'] = array_sum($monthly_payment_count_array);
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                $this->data['loan_processing_fee_count'] = Payment::where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_settlements_amount'] = Payment::where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');

            } elseif (Auth::user()->hasRole('field_agent')) {
                $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', Auth::user()->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');

            } else {
                $branch = Branch::where('id', Auth::user()->branch_id)->first();
                $this->data['loan_processing_fee_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_processing_fee_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 3)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                $this->data['loan_settlements_count'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                $this->data['loan_settlements_amount'] = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->where('payment_type_id', 1)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
            }

        }

        return view('home.agent-repayment-fitler', $this->data);
    }

    protected function repayment_chart_data($request)
    {
        $payment_dates = Payment::whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->orderBy('date_payed', 'ASC')->pluck('date_payed');
        $month_array = array();
        $payment_dates = json_decode($payment_dates);

        if (!empty($payment_dates)) {
            foreach ($payment_dates as $unformatted_date) {
                $date = new \DateTime($unformatted_date);
                $day = $date->format('d');
                $month_name = $date->format('d-M');
                $month_array[$day] = $month_name;
            }
        }
        $monthly_payment_count_array = array();
        $month_name_array = array();
        $monthly_payment_amount_array = array();
        if (!empty($month_array)) {
            foreach ($month_array as $day => $month_name) {
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                    if ($request->branch && $request->branch != 'all') {
                        $branch = Branch::find($request->branch);
                        $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                    } else {
                        $monthly_payment_count = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                        $monthly_payment_amount = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');
                    }
                } elseif (Auth::user()->hasRole('field_agent')) {
                    $user = Auth::user();
                    $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');

                } else {
                    $branch = Branch::where('id', Auth::user()->branch_id)->first();
                    $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                }
                array_push($monthly_payment_count_array, $monthly_payment_count);
                array_push($monthly_payment_amount_array, $monthly_payment_amount);
                array_push($month_name_array, $month_name);
            }
        }
        if (!empty($monthly_payment_count_array)) {
            $max_payment_no = max($monthly_payment_count_array);
            $max_payments = round(($max_payment_no + 10 / 2) / 10) * 10;
        } else {
            $max_payment_no = 0;
            $max_payments = 0;
        }

        if (!empty($monthly_payment_amount_array)) {
            $max_amount_no = max($monthly_payment_amount_array);
            $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
        } else {
            $max_amount_no = 0;
            $max_amount = 0;
        }


        $monthly_payment_data_array = array(
            'month' => $month_name_array,
            'post_count_data' => $monthly_payment_count_array,
            'loan_amount' => $monthly_payment_amount_array,
            'max_payment' => $max_payments,
            'max_amount' => $max_amount,
        );

        return json_encode($monthly_payment_data_array);
    }

    protected function disbursement_chart_data($request)
    {
        //loans chart
        $loans_dates = Loan::where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->orderBy('disbursement_date', 'ASC')->pluck('disbursement_date');
        $month_array = array();
        $loans_dates = json_decode($loans_dates);

        if (!empty($loans_dates)) {
            foreach ($loans_dates as $unformatted_date) {
                $date = new \DateTime($unformatted_date);
                $day = $date->format('d');
                $month_name = $date->format('d-M');
                $month_array[$day] = $month_name;
            }
        }
        $monthly_loan_count_array = array();
        $month_name_array = array();
        $monthly_loan_amount_array = array();
        if (!empty($month_array)) {
            foreach ($month_array as $day => $month_name) {
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('section_manager') || Auth::user()->hasRole('collection_officer')) {
                    if ($request->has('branch')) {
                        if ($request->branch && $request->branch != 'all') {
                            $branch = Branch::find($request->branch);
                            $monthly_loan_count = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                            $monthly_loan_amount = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                        } else {
                            $monthly_loan_count = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                            $monthly_loan_amount = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                        }
                    } else {
                        $monthly_loan_count = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                        $monthly_loan_amount = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                    }
                } elseif (Auth::user()->hasRole('field_agent')) {
                    $user = Auth::user();
                    $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                    $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');

                } else {
                    $branch = Branch::where('id', Auth::user()->branch_id)->first();
                    $monthly_loan_count = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                    $monthly_loan_amount = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                }
                array_push($monthly_loan_count_array, $monthly_loan_count);
                array_push($monthly_loan_amount_array, $monthly_loan_amount);
                array_push($month_name_array, $month_name);
            }
        }
        if (!empty($monthly_loan_count_array)) {
            $max_disb_no = max($monthly_loan_count_array);
            $max_disbursement = round(($max_disb_no + 10 / 2) / 10) * 10;
        } else {
            $max_disb_no = 0;
            $max_disbursement = 0;
        }

        if (!empty($monthly_loan_amount_array)) {
            $max_amount_no = max($monthly_loan_amount_array);
            $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
        } else {
            $max_amount_no = 0;
            $max_amount = 0;
        }

        return json_encode($monthly_loan_data_array = array(
            'month' => $month_name_array,
            'post_count_data' => $monthly_loan_count_array,
            'loan_amount' => $monthly_loan_amount_array,
            'max_disbursement' => $max_disbursement,
            'max_amount' => $max_amount,
        ));
    }

    protected function co_disbursement_chart_data($request)
    {
        $loans_dates = Loan::where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->orderBy('disbursement_date', 'ASC')->pluck('disbursement_date');
        $month_array = array();
        $loans_dates = json_decode($loans_dates);

        if (!empty($loans_dates)) {
            foreach ($loans_dates as $unformatted_date) {
                $date = new \DateTime($unformatted_date);
                $day = $date->format('d');
                $month_name = $date->format('d-M');
                $month_array[$day] = $month_name;
            }
        }
        $monthly_loan_count_array = array();
        $month_name_array = array();
        $monthly_loan_amount_array = array();
        if (!empty($month_array)) {
            foreach ($month_array as $day => $month_name) {
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('section_manager')) {
                    if ($request->field_agent && $request->field_agent != 'all') {
                        $user = User::find($request->field_agent);
                        $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                        $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                    } else {
                        $monthly_loan_count = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                        $monthly_loan_amount = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                    }
                } elseif (Auth::user()->hasRole('field_agent')) {
                    $user = Auth::user();
                    $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                    $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');

                } else {
                    $branch = Branch::where('id', Auth::user()->branch_id)->first();
                    $monthly_loan_count = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                    $monthly_loan_amount = $branch->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                }
                array_push($monthly_loan_count_array, $monthly_loan_count);
                array_push($monthly_loan_amount_array, $monthly_loan_amount);
                array_push($month_name_array, $month_name);
            }
        }

        if (!empty($monthly_loan_count_array)) {
            $max_disb_no = max($monthly_loan_count_array);
            $max_disbursement = round(($max_disb_no + 10 / 2) / 10) * 10;
        } else {
            $max_disb_no = 0;
            $max_disbursement = 0;
        }

        if (!empty($monthly_loan_amount_array)) {
            $max_amount_no = max($monthly_loan_amount_array);
            $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
        } else {
            $max_amount_no = 0;
            $max_amount = 0;
        }

        return json_encode($monthly_loan_data_array = array(
            'month' => $month_name_array,
            'post_count_data' => $monthly_loan_count_array,
            'loan_amount' => $monthly_loan_amount_array,
            'max_disbursement' => $max_disbursement,
            'max_amount' => $max_amount,
        ));
    }

    protected function co_repayment_chart_data($request)
    {
        $payment_dates = Payment::whereIn('payment_type_id', [1, 3])->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->orderBy('date_payed', 'ASC')->pluck('date_payed');
        $month_array = array();
        $payment_dates = json_decode($payment_dates);
        if (!empty($payment_dates)) {
            foreach ($payment_dates as $unformatted_date) {
                $date = new \DateTime($unformatted_date);
                $day = $date->format('d');
                $month_name = $date->format('d-M');
                $month_array[$day] = $month_name;
            }
        }
        $monthly_payment_count_array = array();
        $month_name_array = array();
        $monthly_payment_amount_array = array();
        if (!empty($month_array)) {
            foreach ($month_array as $day => $month_name) {
                if (Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                    if ($request->field_agent && $request->field_agent != 'all') {
                        $user = User::find($request->field_agent);
                        $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');
                    } else {
                        $monthly_payment_count = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                        $monthly_payment_amount = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');
                    }
                } elseif (Auth::user()->hasRole('field_agent')) {
                    $user = Auth::user();
                    $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');

                } else {
                    $branch = Branch::where('id', Auth::user()->branch_id)->first();
                    $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                }
                array_push($monthly_payment_count_array, $monthly_payment_count);
                array_push($monthly_payment_amount_array, $monthly_payment_amount);
                array_push($month_name_array, $month_name);
            }
        }
        if (!empty($monthly_payment_count_array)) {
            $max_payment_no = max($monthly_payment_count_array);
            $max_payments = round(($max_payment_no + 10 / 2) / 10) * 10;
        } else {
            $max_payment_no = 0;
            $max_payments = 0;
        }

        if (!empty($monthly_payment_amount_array)) {
            $max_amount_no = max($monthly_payment_amount_array);
            $max_amount = round(($max_amount_no + 10 / 2) / 10) * 10;
        } else {
            $max_amount_no = 0;
            $max_amount = 0;
        }

        $monthly_payment_data_array = array(
            'month' => $month_name_array,
            'post_count_data' => $monthly_payment_count_array,
            'loan_amount' => $monthly_payment_amount_array,
            'max_payment' => $max_payments,
            'max_amount' => $max_amount,
        );

        return json_encode($monthly_payment_data_array);
    }

    public function test_notification_scheduler()
    {
        $installments = Installment::where(['current' => true, 'completed' => false])
            ->whereDate('due_date', Carbon::now())
            ->get();
        $data = [];
        foreach ($installments as $installment) {
            $lon = Loan::where(['id' => $installment->loan_id, 'settled' => false])->first()->setAppends(['balance']);
            $cus = Customer::where('id', '=', $lon->customer_id)->whereIn('branch_id', [4, 5])
                ->select('fname', 'lname', 'phone', 'field_agent_id', 'branch_id')->first();
            $lf = User::find($cus->field_agent_id)->name;
            if ($lon and $cus) {
                $amount_to_be_paid = $installment->total - $installment->amount_paid;
                if ($amount_to_be_paid > 0) {
                    //disabled 22-09-2020 || re-enabled on 26-02-2021
                    $message = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Pay your due of Ksh. ' . number_format($amount_to_be_paid) . ' today. Your total balance is Ksh. ' . number_format($lon->balance) . "\r\n" . 'Your CO ' . $lf . '. Ref: ' . $installment->id . '.' . $cus->branch_id;
                }
                array_push($data, ['customer' => $cus->fname . ' ' . $cus->lname . ' - ' . $cus->phone, 'message' => $message]);
            }
        }
        return json_encode(['count' => count($data), 'data' => $data], true);
    }

//    function getLoanMonths()
//    {
//        $loans_dates = Loan::where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->orderBy( 'disbursement_date', 'ASC' )->pluck( 'disbursement_date' );
//        $month_array = array();
//        $loans_dates = json_decode( $loans_dates );
//
//        if ( ! empty( $loans_dates ) ) {
//            foreach ( $loans_dates as $unformatted_date ) {
//                $date = new \DateTime( $unformatted_date );
//                $day = $date->format( 'd' );
//                $month_name = $date->format( 'd-M' );
//                $month_array[ $day ] = $month_name;
//            }
//        }
//        return $month_array;
//    }
//
//    function getDailyDisbursedLoansCount( $day ) {
//
//        $monthly_loan_count = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
//
//        return $monthly_loan_count;
//    }
//
//    function getDailyDisbursedLoansAmount( $day ) {
//
//        $monthly_loan_amount = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
//
//        return $monthly_loan_amount;
//    }
//
//    function getMonthlyLoanDisbursementData() {
//
//        $monthly_loan_count_array = array();
//        $month_name_array = array();
//        $monthly_loan_amount_array = array();
//        $month_array = $this->getLoanMonths();
//
//        if ( ! empty( $month_array ) ) {
//            foreach ( $month_array as $day => $month_name ){
//                $monthly_loan_count = $this->getDailyDisbursedLoansCount( $day );
//                $monthly_loan_amount = $this->getDailyDisbursedLoansAmount( $day );
//                array_push( $monthly_loan_count_array, $monthly_loan_count );
//                array_push( $monthly_loan_amount_array, $monthly_loan_amount );
//                array_push( $month_name_array, $month_name );
//            }
//        }
//
//        $max_disb_no = max( $monthly_loan_count_array );
//        $max_amount_no = max( $monthly_loan_amount_array );
//        $max_disbursement = round(( $max_disb_no + 10/2 ) / 10 ) * 10;
//        $max_amount = round(( $max_amount_no + 10/2 ) / 10 ) * 10;
//
//        $monthly_loan_data_array = array(
//            'month' => $month_name_array,
//            'post_count_data' => $monthly_loan_count_array,
//            'loan_amount' => $monthly_loan_amount_array,
//            'max_disbursement' => $max_disbursement,
//            'max_amount' => $max_amount,
//        );
//        return $monthly_loan_data_array;
//    }


    public function update_loans_amount()
    {
        $loans = DB::table('loans')->where(['disbursed' => true])->get();
        $setting = Setting::query()->first();

        foreach ($loans as $loan) {
            $loan_product = DB::table('products')->where(['id' => $loan->product_id])->first();

            if ($loan->rolled_over) {
                $rollover = DB::table('rollovers')->where(['loan_id' => $loan->id])->first();
                if ($rollover) {
                    $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100)) + $rollover->rollover_interest;

                } else {
                    $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100));

                }

            } elseif ($loan->has_lp_fee) {
                if ($setting->lp_fee) {
                    $lp_fee = $setting->lp_fee;
                } else {
                    $lp_fee = 0;
                }
                $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100)) + $lp_fee;
            } else {
                $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100));
            }


            $total_amount_paid = DB::table('payments')->where(['loan_id' => $loan->id, 'payment_type_id' => 1])->sum('amount');

            DB::table('loans')->where(['id' => $loan->id])->update(['total_amount' => $total, 'total_amount_paid' => $total_amount_paid]);

        }
        return 'done';


    }

    public function update_amount_paid()
    {
        $loans = DB::table('loans')->where(['disbursed' => true])->get();

        foreach ($loans as $loan) {
            $total = DB::table('payments')->where(['loan_id' => $loan->id, 'payment_type_id' => 1])->sum('amount');
            DB::table('loans')->where(['id' => $loan->id])->update(['total_amount_paid' => $total]);
        }

        return $total;
    }
}
