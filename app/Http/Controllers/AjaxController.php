<?php

namespace App\Http\Controllers;

use App\CustomerInteractionCategory;
use App\Jobs\Sms;
use App\LoginToken;
use App\models\Activity_otp;
use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\CustomerInteraction;
use App\models\Installment;
use App\models\Loan;
use App\models\Payment;
use App\models\Pre_interaction;
use App\models\Product;
use App\models\Rollover;
use App\models\Setting;
use App\Services\Custom;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AjaxController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private $user;
    /**
     * @var string
     */
    private $firstDayOfMonth;
    private $us;
    private $user_branch;
    private $activeBranches;

    public function __construct()
    {

        $this->firstDayOfMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $this->activeBranches = Branch::query()->where('status', '=', true)->pluck('id');

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->user_branch = Branch::where('id', \auth()->user()->branch_id)->first();
            // $this->user_branch = DB::table('branches')->where('id', \auth()->user()->branch_id)->select('id')->first();

            $this->us = User::find($this->user->field_agent_id);
            //$this->us = DB::table('users')->where(['id' => $this->user->field_agent_id])->select('id', 'branch_id', 'field_agent_id')->first();


            return $next($request);
        });


    }

    public function dashboard(Request $request, $id)
    {

        if ($request->branch_id != "all") {
            $branch = Branch::find($request->branch_id);
            //$branch = DB::table('branches')->where('id', $request->branch_id)->select('id')->first();
        } else {
            $branch = "all";
        }

        if ($request->field_agent_id != "all") {
            $field_agent = User::find($request->field_agent_id);
        } else {
            $field_agent = "all";
        }

        //online users
        if ($id == 'online_count') {
            $data = $this->online();
        }

        //total customers
        if ($id == 'customers') {
            $customers = $this->customers($branch, $field_agent);

            $data = $customers;
        }

        //customer info
        if ($id == 'customer_info') {
            $customer_info = $this->customer_info($branch, $field_agent);
            $data = $customer_info;
        }

        //totalAmount ,
        if ($id == 'total_amount') {
            $total_amount = $this->total_amount($branch, $field_agent);
            $data = $total_amount;
        }

        //active loans
        if ($id == 'active_loans') {
            $active_loans = $this->active_loans($branch, $field_agent);
            $data = $active_loans;
        }

        //total_arrears
        if ($id == 'total_arrears') {
            $arrears_total = $this->arrears_loans($branch, $field_agent);
            $data = $arrears_total;
        }


        // loans due today
        if ($id == 'due_loans_count') {
            $due_loans_count = $this->loans_due_today($branch, $field_agent);
            $data = $due_loans_count;
        }

        // due today amount
        if ($id == 'due_today_amount') {
            $due_today_amount = $this->due_today_amount($branch, $field_agent);
            $data = $due_today_amount;
        }

        //mtd loans
        if ($id == 'mtd_loans') {
            $mtd_loans = $this->mtd_loans($branch, $field_agent);
            $data = $mtd_loans;
        }
        //loans pending approval
        if ($id == 'pending_approval') {
            $data = $this->pending_approval($branch, $field_agent);
        }


        //repayment_rate
        if ($id == 'repayment_rate') {
            $repayment_rate = $this->repayment_rate($branch, $field_agent);
            $data = number_format($repayment_rate, 1);
        }

        //PAR
        if ($id == 'PAR') {
            $total_amount = (float)str_replace(',', '', $this->total_amount($branch, $field_agent)['totalAmount']);
            $arrears_total = $this->arrears_loans($branch, $field_agent)['arrears_total'];
            if ($total_amount > 0) {
                $this->data['PAR'] = (int)(($arrears_total / $total_amount) * 100);
            } else {
                $this->data['PAR'] = 0;
            }

            $data = number_format($this->data['PAR']);
        }

        if ($id == 'chart') {
            $data = $this->chart();
        }

        if ($id == 'total_commission') {
            $branch = $request->branch_id != "all" ? Branch::find($request->branch_id) : "all";
            $field_agent = $request->field_agent_id != "all" ? User::find($request->field_agent_id) : "all";

            $total_commission = $this->field_agent_commission($branch, $field_agent);
            return response()->json([
                'status' => 'success',
                'data' => $total_commission
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }


    function online()
    {
        $system_users = User::get(['id'])->each->setAppends([]);
        $online_count = 0;
        foreach ($system_users as $user) {
            if (Cache::has('is_online' . $user->id)) {
                $online_count = $online_count + 1;
            }
        }

        return $online_count;
    }

    function customers($branch, $field_agent)
    {
        if (isset($field_agent->id)) {
            $customers = Customer::where('field_agent_id', $field_agent->id)->count();
        } elseif (isset($branch->id)) {
            $customers = $branch->customers()->count();
        } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care') || $this->user->hasRole('sector_manager') || $this->user->hasRole('collection_officer')) {
            $customers = Customer::query()->whereIn('branch_id', $this->activeBranches)->get()->count();
        } elseif ($this->user->hasRole('field_agent')) {
            $customers = Customer::where('field_agent_id', $this->user->id)->count();
        } else {
            $customers = $this->user_branch->customers()->count();
        }

        return $customers;
    }

    // function customer_info($branch, $field_agent)
    // {
    //     $user = \auth()->user();

    //     $current_month = Carbon::now()->format('M');

    //     if (isset($field_agent->id)) {
    //         $customer = Customer::whereMonth('created_at', date('m'))
    //             ->whereYear('created_at', date('Y'))
    //             ->where('field_agent_id', $field_agent->id)
    //             ->whereHas('regpayments')
    //             ->get();
    //     } elseif (isset($branch->id)) {
    //         $customer = $branch->customers()
    //             ->whereMonth('customers.created_at', date('m'))
    //             ->whereYear('customers.created_at', date('Y'))
    //             ->whereHas('regpayments')
    //             ->get();
    //     } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care') || $user->hasRole('collection_officer') || $user->hasRole('sector_manager')) {
    //         $customer = Customer::whereMonth('created_at', date('m'))
    //             ->whereYear('created_at', date('Y'))
    //             ->whereHas('regpayments')
    //             ->get();
    //     } elseif ($user->hasRole('field_agent')) {
    //         $customer = Customer::whereMonth('created_at', date('m'))
    //             ->whereYear('created_at', date('Y'))
    //             ->where('field_agent_id', $user->id)
    //             ->whereHas('regpayments')
    //             ->get();
    //     } else {
    //         $customer = $this->user_branch->customers()
    //             ->whereMonth('customers.created_at', date('m'))
    //             ->whereYear('customers.created_at', date('Y'))
    //             ->whereHas('regpayments')
    //             ->get();
    //     }

    //     $selc = [$current_month, $customer->count()];
    //     $this->data['customer_info'][] = $selc;

    //     return $this->data['customer_info'];
    // }

    function customer_info($branch, $field_agent)
    {
        $user = auth()->user();
        $current_month = Carbon::now()->format('M');

        if (isset($field_agent->id)) {
            $customer = Customer::whereMonth('created_at', date('m'))
            ->whereIn('times_loan_applied', [0, 1])
            ->whereYear('created_at', date('Y'))
                ->where('field_agent_id', $field_agent->id)
                ->whereHas('regpayments')
                ->whereHas('loans', function ($query) {
                    $query->where('approved', 1)->whereNotNull('approved_date');
                })
                ->get();
        } elseif (isset($branch->id)) {
            $customer = $branch->customers()
                ->whereIn('times_loan_applied', [0, 1])
                ->whereMonth('customers.created_at', date('m'))
                ->whereYear('customers.created_at', date('Y'))
                ->whereHas('regpayments')
                ->whereHas('loans', function ($query) {
                    $query->where('approved', 1)->whereNotNull('approved_date');
                })
                ->get();
        } elseif ($user->hasAnyRole(['admin', 'accountant', 'agent_care', 'collection_officer', 'sector_manager'])) {
            $customer = Customer::whereMonth('created_at', date('m'))
            ->whereIn('times_loan_applied', [0, 1])
            ->whereYear('created_at', date('Y'))
                ->whereHas('regpayments')
                ->whereHas('loans', function ($query) {
                    $query->where('approved', 1)->whereNotNull('approved_date');
                })
                ->get();
        } elseif ($user->hasRole('field_agent')) {
            $customer = Customer::whereMonth('created_at', date('m'))
            ->whereIn('times_loan_applied', [0, 1])
            ->whereYear('created_at', date('Y'))
                ->where('field_agent_id', $user->id)
                ->whereHas('regpayments')
                ->whereHas('loans', function ($query) {
                    $query->where('approved', 1)->whereNotNull('approved_date');
                })
                ->get();
        } else {
            $customer = $this->user_branch->customers()
                ->whereIn('times_loan_applied', [0, 1])
                ->whereMonth('customers.created_at', date('m'))
                ->whereYear('customers.created_at', date('Y'))
                ->whereHas('regpayments')
                ->whereHas('loans', function ($query) {
                    $query->where('approved', 1)->whereNotNull('approved_date');
                })
                ->get();
        }

        $selc = [$current_month, $customer->count()];
        $this->data['customer_info'][] = $selc;

        return $this->data['customer_info'];
    }


    function total_amount($branch, $field_agent)
    {
        $totalAmount = 0;
        $amount_paid = 0;
        $TotalLoanAmount = 0;

        // if (isset($field_agent->id)) {
        //     $br = Branch::where(['branches.status' => true, 'branches.id' => $field_agent->branch_id])
        //         ->join('customers', 'branches.id', '=', 'customers.branch_id')
        //         ->join('loans', function ($join) use ($field_agent) {
        //             $join->on('customers.id', '=', 'loans.customer_id')
        //                 ->where('customers.field_agent_id', '=', $field_agent->id)
        //                 ->where('loans.disbursed', '=', true)
        //                 ->where('loans.settled', '=', false);
        //         })
        //         ->select([
        //             // 'installments.*',
        //             'loans.product_id',
        //             'loans.rolled_over',
        //             'loans.loan_amount',
        //             'loans.total_amount',
        //             'loans.total_amount_paid',
        //             'loans.has_lp_fee'
        //         ])
        //         ->get();

        // } elseif (isset($branch->id)) {

        //     $br = Branch::where(['branches.status' => true, 'branches.id' => $branch->id])->join('customers', 'branches.id', '=', 'customers.branch_id')
        //         ->join('loans', function ($join) {
        //             $join->on('customers.id', '=', 'loans.customer_id')
        //                 ->where('loans.disbursed', '=', true)
        //                 ->where('loans.settled', '=', false);
        //         })
        //         ->select([
        //             // 'installments.*',
        //             'loans.product_id',
        //             'loans.rolled_over',
        //             'loans.loan_amount',
        //             'loans.total_amount',
        //             'loans.total_amount_paid',
        //             'loans.has_lp_fee'
        //         ])
        //         ->get();

        // } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care')) {

        //     $br = Branch::where('branches.status', true)
        //                     ->join('customers', 'branches.id', '=', 'customers.branch_id')
        //                     ->join('loans', function ($join) {
        //                         $join->on('installments.load_id', '=', 'loans.id');
        //                     })
        //                     ->join('loans', function ($join) {
        //                         $join->on('customers.id', '=', 'loans.customer_id')
        //                             ->where('loans.disbursed', '=', true)
        //                             ->where('loans.settled', '=', false);
        //                     })
        //                     ->select([
        //                         'installments.*',
        //                         'loans.product_id',
        //                         'loans.rolled_over',
        //                         'loans.loan_amount',
        //                         'loans.total_amount',
        //                         'loans.total_amount_paid',
        //                         'loans.has_lp_fee'
        //                     ])
        //                     ->get();


        // } elseif ($this->user->hasRole('field_agent')) {


        //     $br = Branch::where(['branches.id' => $this->user->branch_id, 'branches.status' => true])
        //         ->join('customers', function ($join) {
        //             $join->on('branches.id', '=', 'customers.branch_id')
        //                 ->where('customers.field_agent_id', '=', $this->user->id);
        //         })
        //         ->join('loans', function ($join) {
        //             $join->on('customers.id', '=', 'loans.customer_id')
        //                 ->where('loans.disbursed', '=', true)
        //                 ->where('loans.settled', '=', false);
        //         })
        //         ->select([
        //             'loans.product_id',
        //             'loans.rolled_over',
        //             'loans.loan_amount',
        //             'loans.total_amount',
        //             'loans.total_amount_paid',
        //             'loans.has_lp_fee'
        //         ])
        //         ->get();


        // } elseif ($this->user->hasRole('collection_officer')) {


        //     $br = Branch::where(['branches.id' => $this->us->branch_id, 'branches.status' => true])
        //         ->join('customers', function ($join) {
        //             $join->on('branches.id', '=', 'customers.branch_id')
        //                 ->where('customers.field_agent_id', '=', $this->us->id);
        //         })
        //         ->join('loans', function ($join) {
        //             $join->on('customers.id', '=', 'loans.customer_id')
        //                 ->where('loans.disbursed', '=', true)
        //                 ->where('loans.settled', '=', false);
        //         })
        //         ->select([
        //             'loans.product_id',
        //             'loans.rolled_over',
        //             'loans.loan_amount',
        //             'loans.total_amount',
        //             'loans.total_amount_paid',
        //             'loans.has_lp_fee'
        //         ])
        //         ->get();

        // } else {

        //     $br = Branch::where(['branches.id' => $this->user_branch->id, 'branches.status' => true])->join('customers', 'branches.id', '=', 'customers.branch_id')
        //         ->join('loans', function ($join) {
        //             $join->on('customers.id', '=', 'loans.customer_id')
        //                 ->where('loans.disbursed', '=', true)
        //                 ->where('loans.settled', '=', false);
        //         })
        //         ->select([
        //             // 'installments.*',
        //             'loans.product_id',
        //             'loans.rolled_over',
        //             'loans.loan_amount',
        //             'loans.total_amount',
        //             'loans.total_amount_paid',
        //             'loans.has_lp_fee'
        //         ])
        //         ->get();

        // }

        if (isset($field_agent->id)) {
            // $br = Branch::where(['branches.status' => true, 'branches.id' => $field_agent->branch_id])
            //     ->join('customers', 'branches.id', '=', 'customers.branch_id')
            //     ->join('loans', function ($join) use ($field_agent) {
            //         $join->on('customers.id', '=', 'loans.customer_id')
            //             ->where('customers.field_agent_id', '=', $field_agent->id)
            //             ->where('loans.disbursed', '=', true)
            //             ->where('loans.settled', '=', false);
            //     })
            //     ->select([
            //         // 'installments.*',
            //         'loans.product_id',
            //         'loans.rolled_over',
            //         'loans.loan_amount',
            //         'loans.total_amount',
            //         'loans.total_amount_paid',
            //         'loans.has_lp_fee'
            //     ])
            //     ->get();
            $br = Branch::with('customers.loans.installments')->where('status', true)->where('id', $field_agent->branch_id)->get();
        } elseif (isset($branch->id)) {
            // $br = Branch::where(['branches.status' => true, 'branches.id' => $branch->id])->join('customers', 'branches.id', '=', 'customers.branch_id')
            //             ->join('loans', function ($join) {
            //                 $join->on('customers.id', '=', 'loans.customer_id')
            //                     ->where('loans.disbursed', '=', true)
            //                     ->where('loans.settled', '=', false);
            //             })
            //             ->select([
            //                 // 'installments.*',
            //                 'loans.product_id',
            //                 'loans.rolled_over',
            //                 'loans.loan_amount',
            //                 'loans.total_amount',
            //                 'loans.total_amount_paid',
            //                 'loans.has_lp_fee'
            //             ])
            //             ->get();
            $br = Branch::with('customers.loans.installments')->where('status', true)->where('id', $branch->id)->get();
        } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care') || $this->user->hasRole('sector_manager')) {
            // $br = Branch::where('branches.status', true)
            //         ->join('customers', 'branches.id', '=', 'customers.branch_id')
            //         ->join('loans', function ($join) {
            //             $join->on('customers.id', '=', 'loans.customer_id')
            //                 ->where('loans.disbursed', '=', true)
            //                 ->where('loans.settled', '=', false);
            //         })
            //         ->select([
            //             // 'installments.*',
            //             'loans.product_id',
            //             'loans.rolled_over',
            //             'loans.loan_amount',
            //             'loans.total_amount',
            //             'loans.total_amount_paid',
            //             'loans.has_lp_fee'
            //         ])
            //         ->get();
            $br = Branch::with('customers.loans.installments')->where('status', true)->get();
        } elseif ($this->user->hasRole('field_agent')) {
            // $br = Branch::where(['branches.id' => $this->user->branch_id, 'branches.status' => true])
            //     ->join('customers', function ($join) {
            //         $join->on('branches.id', '=', 'customers.branch_id')
            //             ->where('customers.field_agent_id', '=', $this->user->id);
            //     })
            //     ->join('loans', function ($join) {
            //         $join->on('customers.id', '=', 'loans.customer_id')
            //             ->where('loans.disbursed', '=', true)
            //             ->where('loans.settled', '=', false);
            //     })
            //     ->select([
            //         'loans.product_id',
            //         'loans.rolled_over',
            //         'loans.loan_amount',
            //         'loans.total_amount',
            //         'loans.total_amount_paid',
            //         'loans.has_lp_fee'
            //     ])
            //     ->get();
            $br = Branch::with('customers.loans.installments')->where('status', true)->where('id', $this->user->branch_id)->get();
        } elseif ($this->user->hasRole('collection_officer')) {
            // $br = Branch::where(['branches.id' => $this->us->branch_id, 'branches.status' => true])
            //     ->join('customers', function ($join) {
            //         $join->on('branches.id', '=', 'customers.branch_id')
            //             ->where('customers.field_agent_id', '=', $this->us->id);
            //     })
            //     ->join('loans', function ($join) {
            //         $join->on('customers.id', '=', 'loans.customer_id')
            //             ->where('loans.disbursed', '=', true)
            //             ->where('loans.settled', '=', false);
            //     })
            //     ->select([
            //         'loans.product_id',
            //         'loans.rolled_over',
            //         'loans.loan_amount',
            //         'loans.total_amount',
            //         'loans.total_amount_paid',
            //         'loans.has_lp_fee'
            //     ])
            //     ->get();
            $br = Branch::with('customers.loans.installments')->where('status', true)->where('id', $this->user->branch_id)->get();
        } else {
            // $br = Branch::where(['branches.id' => $this->user_branch->id, 'branches.status' => true])->join('customers', 'branches.id', '=', 'customers.branch_id')
            //     ->join('loans', function ($join) {
            //         $join->on('customers.id', '=', 'loans.customer_id')
            //             ->where('loans.disbursed', '=', true)
            //             ->where('loans.settled', '=', false);
            //     })
            //     ->select([
            //         // 'installments.*',
            //         'loans.product_id',
            //         'loans.rolled_over',
            //         'loans.loan_amount',
            //         'loans.total_amount',
            //         'loans.total_amount_paid',
            //         'loans.has_lp_fee'
            //     ])
            //     ->get();
            $br = Branch::with('customers.loans.installments')->where('status', true)->where('id', $this->user_branch->id)->get();
        }

        // foreach ($br as $r1) {
        //     //total amount
        //     $last_payment_date = $r1->last_payment_date;
        //     if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90)) {
        //         $t = $r1->total_amount - $r1->total_amount_paid;
        //         $totalAmount += $t;
        //         $TotalLoanAmount += $r1->total_amount;
        //         $amount_paid += $r1->total_amount_paid;
        //     }
        // }
        $setting = Setting::query()->first();
        if ($setting->lp_fee) {
            $lp_fee = $setting->lp_fee;
        } else {
            $lp_fee = 0;
        }
        foreach ($br as $r1) {
            foreach($r1->customers as $customer) {
                foreach($customer->loans as $loan) {
                    if ($loan->disbursed && !$loan->settled) {
                        $product = Product::find($loan->product_id);
                        $amount_paid = Installment::where('loan_id', $loan->id)->get()->sum('amount_paid');
                        $totalAmount += (($loan->loan_amount + ($loan->loan_amount * ($product->interest / 100)) + $lp_fee) - $amount_paid);
                    }
                }
            }

            // // total amount
            // $last_payment_date = $r1->last_payment_date;
            // $product = Product::find($r1->product_id);
            // if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(90)) {
            //     $amount_paid = Installment::where('loan_id', $r1->id)->get();
            //     $t = $r1->total_amount - $r1->total_amount_paid;
            //     $totalAmount += (($r1->loan_amount + ($r1->loan_amount * ($product->interest / 100)) + $lp_fee) - $amount_paid);
            //     $TotalLoanAmount += $r1->total_amount;
            //     $amount_paid += $r1->total_amount_paid;
            // }
        }

        // return ['totalAmount' => number_format(floatval($totalAmount)), 'amount_paid' => number_format(floatval($amount_paid)), 'TotalLoanAmount' => number_format(floatval($TotalLoanAmount))];
        return ['totalAmount' => number_format(floatval($totalAmount)), 'amount_paid' => number_format(floatval($amount_paid))];
    }


    function getLoanTotalAttribute($loan, $setting)
    {
        $loan_product = DB::table('products')->where(['id' => $loan->product_id])->first();

        if ($loan->rolled_over) {
            $rollover = DB::table('rollovers')->where(['loan_id' => $loan->id])->first();
            $rt = 0;
            if ($rollover) {
                $rt = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100)) + $rollover->rollover_interest;

            } else {
                $rt = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100));

            }

            return $rt;
        }

        if ($loan->has_lp_fee) {
            if ($setting->lp_fee) {
                $lp_fee = $setting->lp_fee;
            } else {
                $lp_fee = 0;
            }
            $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100)) + $lp_fee;
        } else {
            $total = $loan->loan_amount + ($loan->loan_amount * ($loan_product->interest / 100));
        }

        return $total;

    }

    public function getAmountPaidAttribute($loan)
    {

        $total = DB::table('payments')->where(['loan_id' => $loan->id])->sum('amount');
        return $total;


    }

    function active_loans($branch, $field_agent)
    {
        $activeBranches = $this->activeBranches;
        if (isset($field_agent->id)) {
            $loans = $field_agent->loans()->where(['settled' => false, 'disbursed' => true])->get();

        } elseif (isset($branch->id)) {

            $loans = $branch->loans()->where(['settled' => false, 'disbursed' => true])->get();
        } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care') || $this->user->hasRole('sector_manager')) {


            $loans = Loan::query()->where(['settled' => false, 'disbursed' => true])
                ->whereHas('customer', function ($q) use ($activeBranches) {
                    $q->whereIn('branch_id', $activeBranches);
                })
                ->get();

        } elseif ($this->user->hasRole('field_agent')) {
            $loans = $this->user->loans()->where(['settled' => false, 'disbursed' => true])->get();
        } elseif ($this->user->hasRole('collection_officer')) {
            $us = User::find($this->user->field_agent_id);
            $loans = $us->loans()->where(['settled' => false, 'disbursed' => true])->get();
        } else {
            $loans = $this->user_branch->loans()->where(['settled' => false, 'disbursed' => true])->get();
        }

        $lns_arr = [];
        foreach ($loans as $loan) {
            $last_payment_date = $loan->last_payment_date;
            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                array_push($lns_arr, $loan);
            }
        }
        return count($lns_arr);
    }


    function arrears_loans($branch, $field_agent)
    {
        $activeBranches = $this->activeBranches;
        if (isset($field_agent->id)) {
            $loans_with_arreas = $field_agent->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();
        } elseif (isset($branch->id)) {
            $loans_with_arreas = $branch->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $loans_with_arreas = Loan::where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->whereHas('customer', function ($q) use ($activeBranches) {
                $q->whereIn('branch_id', $activeBranches);
            })->get();

        } elseif (Auth::user()->hasRole('field_agent')) {
            $loans_with_arreas = $this->user->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();
        } elseif (Auth::user()->hasRole('collection_officer')) {
           // $us = User::find($this->user->field_agent_id);
            $loans_with_arreas = $this->us->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();
        } else {
            $loans_with_arreas = $this->user_branch->loans()->where('settled', false)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '>', 0);
            })->get();
        }


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
        return ['arrears_total' => $arrears_total, 'arrears_count' => $arrears_count];
    }

    function loans_due_today($branch, $field_agent)
    {
        $activeBranches = $this->activeBranches;

        if (isset($field_agent->id)) {
            $due_today_count = $field_agent->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
        } elseif (isset($branch->id)) {
            $due_today_count = $branch->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
        } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care')|| $this->user->hasRole('sector_manager')) {
            $due_today_count = Loan::where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now()->format('Y-m-d'));
            })->whereHas('customer', function ($q) use ($activeBranches) {
                $q->whereIn('branch_id', $activeBranches);
            })->count();
        } elseif ($this->user->hasRole('field_agent')) {
            $due_today_count = $this->user->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
        } elseif ($this->user->hasRole('collection_officer')) {


            $due_today_count = $this->us->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
        } else {
            $due_today_count = $this->user_branch->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
                $q->where([/*'current' => true,*/ 'completed' => false])->whereDate('due_date', Carbon::now());
            })->count();
        }

        return $due_today_count;
    }

    function due_today_amount($branch, $field_agent)
    {
        if (isset($field_agent->id)) {
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

        } elseif (isset($branch->id)) {
            $inst = Installment::whereDate('due_date', Carbon::now())
                ->join('loans', function ($join) {
                    $join->on('loans.id', '=', 'installments.loan_id')
                        ->where('loans.disbursed', '=', true)
                        ->where('loans.settled', '=', false);
                })
                ->join('customers', function ($join) use ($branch) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        //->where('customers.field_agent_id', '=', $this->us->id)
                        ->where('customers.branch_id', $branch->id);

                })
                ->select('installments.*')
                ->get();

        } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care') || $this->user->hasRole('collection_officer') || $this->user->hasRole('sector_manager')) {
            $inst = Installment::whereDate('due_date', Carbon::now())
                ->join('loans', function ($join) {
                    $join->on('loans.id', '=', 'installments.loan_id')
                        ->where('loans.disbursed', '=', true)
                        ->where('loans.settled', '=', false);
                })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        ->whereIn('customers.branch_id', $this->activeBranches);

                })
                ->select('installments.*')
                ->get();

        } elseif ($this->user->hasRole('field_agent')) {
            $inst = Installment::whereDate('due_date', Carbon::now())
                ->join('loans', function ($join) {
                    $join->on('loans.id', '=', 'installments.loan_id')
                        ->where('loans.disbursed', '=', true)
                        ->where('loans.settled', '=', false);
                })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        ->where('customers.field_agent_id', '=', $this->user->id)
                        ->where('customers.branch_id', $this->user->branch_id);

                })
                ->select('installments.*')
                ->get();

        } else {
            $inst = Installment::whereDate('due_date', Carbon::now())
                ->join('loans', function ($join) {
                    $join->on('loans.id', '=', 'installments.loan_id')
                        ->where('loans.disbursed', '=', true)
                        ->where('loans.settled', '=', false);
                })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        ->whereIn('customers.branch_id', $this->activeBranches)
                        ->where('customers.branch_id', $this->user->branch_id);

                })
                ->select('installments.*')
                ->get();
        }

        $due_today_amount = $inst->sum('total') - $inst->sum('amount_paid');

        return number_format($due_today_amount);
    }

    function mtd_loans($branch, $field_agent)
    {
        if (isset($field_agent->id)) {
            $mtd_loans = $field_agent->loans()->where('disbursed', true)->get()->count();
            $mtd_loan_amount = $field_agent->loans()->where('disbursed', true)->sum('loan_amount');
            $mtd_loan_applied_amount = $field_agent->loans()->where('disbursed', true)->sum('total_amount');

        } elseif (isset($branch->id)) {
            $mtd_loans = $branch->loans()->where('disbursed', true)->get()->count();
            $mtd_loan_amount = $branch->loans()->where('disbursed', true)->sum('loan_amount');
            $mtd_loan_applied_amount = $branch->loans()->where('disbursed', true)->sum('total_amount');

        } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care') || $this->user->hasRole('collection_officer') || $this->user->hasRole('sector_manager')) {
            $mtd_loans = Loan::where('disbursed', true)->get()->count();
            $mtd_loan_amount = Loan::where('disbursed', true)->sum('loan_amount');
            $mtd_loan_applied_amount = Loan::where('disbursed', true)->sum('total_amount');

        } elseif ($this->user->hasRole('field_agent')) {
            $mtd_loans = $this->user->loans()->where('disbursed', true)->get()->count();
            $mtd_loan_amount = $this->user->loans()->where('disbursed', true)->sum('loan_amount');
            $mtd_loan_applied_amount = $this->user->loans()->sum('total_amount');


        } else {
            $mtd_loans = $this->user_branch->loans()->where('disbursed', true)->get()->count();
            $mtd_loan_amount = $this->user_branch->loans()->where('disbursed', true)->sum('loan_amount');
            $mtd_loan_applied_amount = $this->user_branch->loans()->where('disbursed', true)->sum('total_amount');

        }

        return [
            'mtd_loans' => $mtd_loans,
            'mtd_loan_amount' => $mtd_loan_amount,
            'mtd_loan_applied_amount' => $mtd_loan_applied_amount
        ];
    }

    function pending_approval($branch, $field_agent)
    {
        if (isset($field_agent->id)) {
            $pending_approval = $field_agent->loans()->where(['approved' => false, 'disbursed' => false, 'source' => 'main'])->count();
            $pending_disbursements = $field_agent->loans()->where(['approved' => true, 'disbursed' => false])->count();

        } elseif (isset($branch->id)) {
            $pending_approval = $branch->loans()->where(['approved' => false, 'disbursed' => false, 'source' => 'main'])->count();
            $pending_disbursements = $branch->loans()->where(['approved' => true, 'disbursed' => false])->count();
        } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care') || $this->user->hasRole('collection_officer') || $this->user->hasRole('sector_manager')) {
            $pending_approval = Loan::where(['approved' => false, 'disbursed' => false, 'source' => 'main'])->count();
            $pending_disbursements = Loan::where(['approved' => true, 'disbursed' => false])->count();

        } elseif ($this->user->hasRole('field_agent')) {
            $pending_approval = $this->user->loans()->where(['approved' => false, 'disbursed' => false, 'source' => 'main'])->count();
            $pending_disbursements = $this->user->loans()->where(['approved' => true, 'disbursed' => false])->count();
        } else {
            $pending_approval = $this->user_branch->loans()->where(['approved' => false, 'disbursed' => false, 'source' => 'main'])->count();
            $pending_disbursements = $this->user_branch->loans()->where(['approved' => true, 'disbursed' => false])->count();
        }

        return ['pending_approval' => $pending_approval, 'pending_disbursements' => $pending_disbursements];
    }

    function repayment_rate($branch, $field_agent)
    {
        $activeBranches = $this->activeBranches;
        if (isset($field_agent->id)) {
            $rp_loans = $field_agent->loans()->where('disbursed', true)->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
        } elseif (isset($branch->id)) {
            $rp_loans = $branch->loans()->where('disbursed', true)->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
        } elseif ($this->user->hasRole('admin') || $this->user->hasRole('accountant') || $this->user->hasRole('agent_care') || $this->user->hasRole('collection_officer') || $this->user->hasRole('sector_manager')) {
            //new repayment percentage
            $rp_loans = Loan::where('disbursed', true)
                ->whereHas('customer', function ($q) use ($activeBranches) {
                    $q->whereIn('branch_id', $activeBranches);
                })->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
        } elseif ($this->user->hasRole('field_agent')) {
            $rp_loans = $this->user->loans()->where('disbursed', true)->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
        } else {
            $rp_loans = $this->user_branch->loans()->where('disbursed', true)->get();
            $arr = array();
            foreach ($rp_loans as $loan) {
                array_push($arr, $loan->id);
            }
        }
        $paid = 0;
        $due = 0;
        $installments = Installment::whereBetween('due_date', [$this->firstDayOfMonth, Carbon::now()->format('Y-m-d')])->whereIn('loan_id', $arr)->get();

        foreach ($installments as $installment) {
            $paid += $installment->amount_paid;
            $due += $installment->total;
        }

        //repayment rate
        if ($due > 0) {
            $repayment_rate = ($paid / $due) * 100;

        } else {
            $repayment_rate = 0;
        }

        return $repayment_rate;
    }

    function chart()
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
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                    $monthly_loan_count = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                    $monthly_loan_amount = Loan::where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
                }
                elseif (Auth::user()->hasRole('field_agent')) {
                    $user = Auth::user();
                    $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                    $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');

                }
                elseif (Auth::user()->hasRole('collection_officer')) {
                    //$user = Auth::user();
                    $user = User::where(['id' => Auth::user()->field_agent_id])->first();

                    $monthly_loan_count = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get()->count();
                    $monthly_loan_amount = $user->loans()->where('disbursed', true)->whereDay('disbursement_date', $day)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');

                }

                else {
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

        $this->data['month_array'] = json_encode($monthly_loan_data_array);
        return $this->data['month_array'];
    }

    public function interactions(Request $request, $customer_identifier)
    {
        $user = \auth()->user();
        $category = CustomerInteractionCategory::where(['name' => 'Prepayment'])->first();
        $arrear_category = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();
        $cts = CustomerInteractionCategory::whereIn('name', ['Prepayment', 'Due Collection', 'Customer Satisfaction survey', 'First Visit Lo', 'First Visit Co'])->pluck('id');

        if ($customer_identifier != 'all') {
            $data = CustomerInteraction::where(['customer_id' => decrypt($customer_identifier)])
                ->select('customer_interactions.*');

            $interactions = Pre_interaction::where(['customer_id' => decrypt($customer_identifier)])->select('pre_interactions.*');
        } elseif ($request->lf != 'all') {
            $data = CustomerInteraction::where(['customer_interactions.user_id' => $request->lf])->join('customers', function ($join) use ($request) {
                $join->on('customers.id', '=', 'customer_interactions.customer_id');
            })
                ->select('customer_interactions.*');

            $user = User::find($request->lf);

            if ($user->hasRole('field_agent')) {
                $interactions = Pre_interaction::where(['interaction_category_id' => $category->id])
                    ->join('customers', function ($join) use ($user) {
                        $join->on('customers.id', '=', 'pre_interactions.customer_id')
                            ->where('customers.field_agent_id', '=', $user->id);;
                    })
                    ->select('pre_interactions.*');
            } elseif ($user->hasRole('collection_officer')) {
                $interactions = Pre_interaction::where(['interaction_category_id' => $category->id])
                    ->join('customers', function ($join) use ($user) {
                        $join->on('customers.id', '=', 'pre_interactions.customer_id')
                            ->where('customers.field_agent_id', '=', $user->field_agent_id);;
                    })
                    ->select('pre_interactions.*');
            } elseif ($user->hasRole('customer_informant', 'phone_handler')) {
                $interactions = Pre_interaction::where(['interaction_category_id' => $arrear_category->id])
                    ->join('customers', function ($join) use ($user) {
                        $join->on('customers.id', '=', 'pre_interactions.customer_id')
                            ->where('customers.field_agent_id', '=', $user->field_agent_id);;
                    })
                    ->select('pre_interactions.*');
            } else {
                $interactions = Pre_interaction::join('customers', function ($join) use ($user) {
                    $join->on('customers.id', '=', 'pre_interactions.customer_id')
                        ->where('customers.branch_id', $user->branch_id);;
                })
                    ->select('pre_interactions.*');
            }
        } elseif ($request->branch != 'all') {
            $data = CustomerInteraction::join('customers', function ($join) use ($request) {
                $join->on('customers.id', '=', 'customer_interactions.customer_id')
                    ->where(['customers.branch_id' => $request->branch]);
            })
                ->select('customer_interactions.*');

            $interactions = Pre_interaction::join('customers', function ($join) use ($request) {
                $join->on('customers.id', '=', 'pre_interactions.customer_id')
                    ->where('customers.branch_id', $request->branch);;
            })
                ->select('pre_interactions.*');
        } elseif (Auth::user()->hasRole('manager')) {
            $data = CustomerInteraction::join('customers', function ($join) use ($request) {
                $join->on('customers.id', '=', 'customer_interactions.customer_id')
                    ->where(['customers.branch_id' => \auth()->user()->branch_id]);
            })
                ->select('customer_interactions.*');

            $interactions = Pre_interaction::join('customers', function ($join) use ($request) {
                $join->on('customers.id', '=', 'pre_interactions.customer_id')
                    ->where('customers.branch_id', \auth()->user()->branch_id);;
            })
                ->select('pre_interactions.*');
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $data = CustomerInteraction::query()->join('customers', function ($join) use ($request) {
                $join->on('customers.id', '=', 'customer_interactions.customer_id');
            })
                ->select('customer_interactions.*');

            $interactions = Pre_interaction::join('customers', function ($join) {
                $join->on('customers.id', '=', 'pre_interactions.customer_id');
            })
                ->select('pre_interactions.*');
        } elseif (Auth::user()->hasRole('field_agent')) {

            $co = User::where(['field_agent_id' => $user->id])->whereHas('roles', function ($query) {
                return $query->where([['name', '=', 'collection_officer']]);
            })->first();
            $arry_ids = [$user->id];
            if ($co) {
                $arry_ids = [$user->id, $co->id];
            }

            $data = CustomerInteraction::where(function ($query) use ($arry_ids) {
                //$query->WhereIn('interaction_category_id', $other_cat)
                $query->WhereIn('user_id', $arry_ids);

            })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'customer_interactions.customer_id')
                        ->where(['customers.field_agent_id' => \auth()->id()]);
                })
                ->select('customer_interactions.*');

            $interactions = Pre_interaction::where(['interaction_category_id' => $category->id])
                ->join('customers', function ($join) use ($user) {
                    $join->on('customers.id', '=', 'pre_interactions.customer_id')
                        ->where('customers.field_agent_id', '=', $user->id);;
                })
                ->select('pre_interactions.*');
        } elseif (\auth()->user()->hasRole('customer_informant') || \auth()->user()->hasRole('phone_handler')) {
            $data = CustomerInteraction::where(['customer_interactions.user_id' => \auth()->id()])
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'customer_interactions.customer_id');
                    //->where(['customers.field_agent_id' => \auth()->user()->field_agent_id]);
                })
                ->select('customer_interactions.*');
            $cat = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();


            $interactions = Pre_interaction::where(['interaction_category_id' => $cat->id])->join('customers', function ($join) use ($user) {
                $join->on('customers.id', '=', 'pre_interactions.customer_id')
                    ->where(['customers.branch_id' => $user->branch_id]);
            })
                ->select('pre_interactions.*');
        } else {
            $data = CustomerInteraction::join('customers', function ($join) use ($request) {
                $join->on('customers.id', '=', 'customer_interactions.customer_id')
                    ->where(['customers.branch_id' => \auth()->user()->branch_id]);
            })
                ->select('customer_interactions.*');

            $interactions = Pre_interaction::join('customers', function ($join) use ($user) {
                $join->on('customers.id', '=', 'pre_interactions.customer_id')
                    ->where('customers.branch_id', $user->branch_id);;
            })
                ->select('pre_interactions.*');
        }

        $success_rate = 0;

        // $inactive = $clone->where(['customer_interactions.status' => 2])->count();
        $inactive = (clone $data)->where(['customer_interactions.status' => 2])->count();

        //$active = $clone1->where(['customer_interactions.status' => 1])->count();
        $active = (clone $data)->where(['customer_interactions.status' => 1])->count();

        //$interactions_success = $success_clone->where(['target' => 1, 'customer_interactions.status' => 2])->count();
        $interactions_success = (clone $data)->where(['target' => 1, 'customer_interactions.status' => 2])->count();

        if (Auth::user()->hasRole(['collection_officer', 'field_agent', 'customer_informant', 'phone_handler'])) {

            $target = $interactions->count() + $data->count();

            if ($target > 0) {
                $success_rate = number_format(($interactions_success / $target) * 100);

            }
        } else {

            //filtered user has role co or lo
            if ($request->lf != 'all') {
                $user = User::find($request->lf);
                if ($user->hasRole(['collection_officer', 'field_agent', 'customer_informant', 'phone_handler'])) {
                    $target = $interactions->count() + $data->count();

                    if ($target > 0) {
                        $success_rate = number_format(($interactions_success / $target) * 100);

                    }

                } else {
                    $target = $interactions->count();

                    if ($target > 0) {
                        $success_rate = number_format(($interactions_success / $target) * 100);

                    }
                }


            } else {
                $target = $interactions->count() + $data->count();

                if ($target > 0) {
                    $success_rate = number_format(($interactions_success / $target) * 100);

                }

            }

            $target = $interactions->count() + $data->count();

            if ($target > 0) {
                $success_rate = number_format(($interactions_success / $target) * 100);

            }
        }

        //$clone2
        $due = (clone $data)->where(['customer_interactions.status' => 1])->whereDate('customer_interactions.next_scheduled_interaction', '=', Carbon::now())
            ->orWhere(function ($query) {
                $query->join('customer_interaction_followups', function ($join) {
                    $join->on('customer_interactions.id', '=', 'customer_interaction_followups.follow_up_id')
                        ->whereDate('customer_interaction_followups.next_scheduled_interaction', '<=', Carbon::now())
                        ->where(['customer_interactions.status' => 1, 'customer_interaction_followups.status' => 2]);

                });
            })->count();
        //$clone3
        $overdue = (clone $data)->where(['customer_interactions.status' => 1])->whereDate('customer_interactions.next_scheduled_interaction', '<', Carbon::now())
            ->orWhere(function ($query) {
                $query->join('customer_interaction_followups', function ($join) {
                    $join->on('customer_interactions.id', '=', 'customer_interaction_followups.follow_up_id')
                        ->whereDate('customer_interaction_followups.next_scheduled_interaction', '<', Carbon::now())
                        ->where(['customer_interactions.status' => 1, 'customer_interaction_followups.status' => 2]);

                });
            })->count();
        $this_month_preclone = clone $interactions;

        if ($request->month) {
            //$clone4
            $this_month_interactions = (clone $data)->whereMonth('customer_interactions.created_at', '=', $request->month)->whereYear('customer_interactions.created_at', '=', Carbon::now())->count();
            //$clone5
            $this_month_interactions_closed = (clone $data)->whereMonth('customer_interactions.closed_date', '=', $request->month)->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['customer_interactions.status' => 2])->count();
            //$monthly_success_clone
            $this_month_interactions_success = (clone $data)->whereMonth('customer_interactions.closed_date', '=', $request->month)->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['target' => 1, 'customer_interactions.status' => 2])->count();
            //$this_month_preclone
            $this_month_pre = (clone $interactions)->whereMonth('pre_interactions.created_at', '=', $request->month)->whereYear('pre_interactions.created_at', '=', Carbon::now())->count();


        } else {
            $this_month_interactions = (clone $data)->whereMonth('customer_interactions.created_at', '=', Carbon::now())->whereYear('customer_interactions.created_at', '=', Carbon::now())->count();
            $this_month_interactions_closed = (clone $data)->whereMonth('customer_interactions.closed_date', '=', Carbon::now())->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['customer_interactions.status' => 2])->count();
            $this_month_interactions_success = (clone $data)->whereMonth('customer_interactions.closed_date', '=', Carbon::now())->whereYear('customer_interactions.closed_date', '=', Carbon::now())->where(['target' => 1, 'customer_interactions.status' => 2])->where([])->count();
            $this_month_pre = (clone $interactions)->whereMonth('pre_interactions.created_at', '=', Carbon::now())->whereYear('pre_interactions.created_at', '=', Carbon::now())->count();
        }
        $monthly_target = $this_month_interactions + $this_month_pre;
        $monthly_success_rate = 0;
        if ($monthly_target > 0) {
            $monthly_success_rate = number_format(($this_month_interactions_success / $monthly_target) * 100);
        }

        //$pclone
        $pdue = (clone $interactions)->whereDate('pre_interactions.due_date', '=', Carbon::now())->where(['pre_interactions.interaction_category_id' => 2])->count();
        //$pclone1
        $poverdue = (clone $interactions)->whereDate('pre_interactions.due_date', '<', Carbon::now())->count();
        //total pre interactions for due collection overdue
        //$pclone2
        $p1 = (clone $interactions)->whereDate('pre_interactions.due_date', '<', Carbon::now())->where(['pre_interactions.interaction_category_id' => 2])->count();
        //$pclone_arreas
        $Pre_arrears = (clone $interactions)->where(['pre_interactions.interaction_category_id' => 4])->count();


        $data1 = [
            'interactions' => $data->count(),
            'active' => $active,
            'inactive' => $inactive, 'due' => $due,
            'this_month_interactions' => $this_month_interactions,
            'this_month_interactions_closed' => $this_month_interactions_closed,
            'over_due' => $overdue,
            'pre' => $interactions->count() - $p1,
            'passed_unttanded_pre_interactions' => $p1,

            'pdue' => $pdue,
            'poverdue' => $poverdue,
            'interactions_success' => $interactions_success,
            'this_month_interactions_success' => $this_month_interactions_success,
            'monthly_success_rate' => $monthly_success_rate,
            'success_rate' => $success_rate,
            'pre_arrears' => $Pre_arrears,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data1
        ], 200);
    }

    function repayment_chart_data(Request $request)
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
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant')) {
                    if ($request->branch && $request->branch != 'all') {
                        $branch = Branch::find($request->branch);
                        $monthly_payment_count = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                        $monthly_payment_amount = Payment::whereIn('loan_id', $branch->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->sum('amount');
                    } else {
                        $monthly_payment_count = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->get()->count();
                        $monthly_payment_amount = Payment::whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');
                    }
                }

                elseif (Auth::user()->hasRole('field_agent')) {
                    $user = Auth::user();
                    $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');

                }
                elseif (Auth::user()->hasRole('collection_officer')) {
                    $user = User::where(['id' => Auth::user()->field_agent_id])->first();

                    $monthly_payment_count = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->count();
                    $monthly_payment_amount = Payment::whereIn('loan_id', $user->loans()->get()->pluck('id'))->whereIn('payment_type_id', [1, 3])->whereDay('date_payed', $day)->whereMonth('date_payed', Carbon::now())->whereYear('date_payed', Carbon::now())->sum('amount');

                }

                else {
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

    function disbursement_chart_data(Request $request)
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
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
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

                } elseif (Auth::user()->hasRole('collection_officer')) {
                    $user = User::where(['id' => \auth()->user()->field_agent_id])->first();
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

    public function ajax_send_token(Request $request)
    {
        $user = Auth::user();
        $token = rand(1000, 9999);
        //check if we have an active token
        //$newToken = Activity_otp::where(['activity' => $request->activity, 'status' => 1, 'user_id' => $user->id])->first();
        $newToken = Activity_otp::where(['activity' => $request->activity, 'status' => 1, 'user_id' => $user->id])->where('expire_at', '>', \Carbon\Carbon::now() )->first();

        if (!$newToken) {
            $newToken = new Activity_otp();
            $newToken->user_id = $user->id;
            $newToken->token = $token;
            $newToken->expire_at = Carbon::now()->addMinutes(3); //a user can use the same token for 5 minutes
            $newToken->activity = $request->activity;
            $newToken->created_at = Carbon::now();
            $newToken->updated_at = Carbon::now();
            $newToken->save();
        }

        $message = "Dear " . $user->name . "," . PHP_EOL . "Your activity token is: " . $newToken->token;
        //  $auser = Auth::user();
        Session::put('approval_token_session', encrypt($newToken->token));

        dispatch(new Sms(
            '+254' . substr($user->phone, -9), $message, $user, true
        ));

        return response()->json([
            'status' => 'success',
            // 'token' => $newToken->token

        ], 200);
    }

    public function ajax_verify_token($token, $activity)
    {
        $service = new Custom();
        $validity = $service->check_token_validity($token, $activity);

        return response()->json([
            'status' => 'success',
            'valid' => $validity

        ], 200);
    }


    // public function field_agents_performance(Request $request)
    // {
    //     $lf = $request->lf;
    //     $branch = $request->branch;
    //     $start_date = $request->start_date;
    //     $end_date = $request->end_date;

    //     $payments = 0;
    //     $target = 0;
    //     $performance = 0;

    //     $field_agents = User::role('field_agent')
    //                         ->with('branch')
    //                         ->whereHas('loans', function ($query) {
    //                             $query->where('disbursed', true)->where('settled', false);
    //                         })
    //                         ->where('status', true)
    //                         ->when($branch && $branch != 'all', function ($query) use ($branch) {
    //                             $query->where('branch_id', $branch);
    //                         })
    //                         ->when($lf && $lf != 'all', function ($query) use ($lf) {
    //                             $query->where('id', $lf);
    //                         })
    //                         ->select('id', 'name', 'phone')
    //                         ->get();

    //     if (($start_date && $start_date != '') || ($end_date && $end_date != '')) {
    //         foreach ($field_agents as $field_agent) {
    //             $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
    //             $payments += Payment::where('payment_type_id', 1)
    //                                 ->whereIn('loan_id', $loan_ids)
    //                                 ->when($start_date && $start_date != '', function ($query) use ($start_date) {
    //                                     $query->whereDate('date_payed', '>=', Carbon::parse($start_date));
    //                                 })
    //                                 ->when($end_date && $end_date != '', function ($query) use ($end_date) {
    //                                     $query->whereDate('date_payed', '<=', Carbon::parse($end_date));
    //                                 })
    //                                 ->sum('amount');
    //             $target += Installment::whereIn('loan_id', $loan_ids)
    //                                 ->when($start_date && $start_date != '', function ($query) use ($start_date) {
    //                                     $query->whereDate('created_at', '>=', Carbon::parse($start_date));
    //                                 })
    //                                 ->when($end_date && $end_date != '', function ($query) use ($end_date) {
    //                                     $query->whereDate('created_at', '<=', Carbon::parse($end_date));
    //                                 })
    //                                 ->sum('total');
    //         }
    //     } else {
    //         foreach ($field_agents as $field_agent) {
    //             $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
    //             $payments += Payment::where('payment_type_id', 1)->whereIn('loan_id', $loan_ids)->whereDate('date_payed', now())->sum('amount');
    //             $target += Installment::whereIn('loan_id', $loan_ids)->whereDate('due_date', now())->sum('total');
    //         }
    //     }

    //     $performance = $target > 0 ? round(($payments / $target) * 100) : 0;

    //     return response()->json([
    //         'payments' => $payments,
    //         'target' => $target,
    //         'performance' => $performance,
    //     ]);
    // }

    public function field_agents_performance(Request $request)
    {
        $lf = $request->lf;
        $branch = $request->branch;
        $date = $request->date;

        $payments = 0;
        $target = 0;
        $performance = 0;

        $field_agents = User::role('field_agent')
                            ->with('branch')
                            ->whereHas('loans', function ($query) {
                                $query->where('disbursed', true)->where('settled', false);
                            })
                            ->where('status', true)
                            ->when($branch && $branch != 'all', function ($query) use ($branch) {
                                $query->where('branch_id', $branch);
                            })
                            ->when($lf && $lf != 'all', function ($query) use ($lf) {
                                $query->where('id', $lf);
                            })
                            ->select('id', 'name', 'phone')
                            ->get();

        if ($date && $date != '') {
            foreach ($field_agents as $field_agent) {
                $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
                $payments += Payment::where('payment_type_id', 1)
                                    ->whereIn('loan_id', $loan_ids)
                                    ->whereDate('date_payed', Carbon::parse($date))
                                    ->sum('amount');
                $target += Installment::whereIn('loan_id', $loan_ids)
                                    ->whereDate('due_date', Carbon::parse($date))
                                    ->sum('total');
            }
        } else {
            foreach ($field_agents as $field_agent) {
                $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
                $payments += Payment::where('payment_type_id', 1)
                                    ->whereIn('loan_id', $loan_ids)
                                    ->whereDate('date_payed', now())
                                    ->sum('amount');
                $target += Installment::whereIn('loan_id', $loan_ids)
                                    ->whereDate('due_date', now())
                                    ->sum('total');
            }
        }

        $performance = $target > 0 ? round(($payments / $target) * 100) : 0;

        return response()->json([
            'payments' => $payments,
            'target' => $target,
            'performance' => $performance,
        ]);
    }


    public function field_agent_collection_data(Request $request)
    {

    }

    public function field_agent_commission($branch, $field_agent)
    {
        $customer_count = 0;

        if (isset($field_agent->id)) {
            $customer_count = Customer::where('field_agent_id', $field_agent->id)
                                      ->whereMonth('created_at', Carbon::now()->month)
                                      ->count();
        } elseif (isset($branch->id)) {
            $customer_count = $branch->customers()
                                     ->whereMonth('created_at', Carbon::now()->month)
                                     ->count();
        }

        // Commission is 250 per customer onboarded
        $commission = $customer_count * 250;
        return $commission;
    }


    public function totalCommission(Request $request)
    {
        $branch_id = $request->branch_id;
        $field_agent_id = $request->field_agent_id;

        // Assuming you have the logic to find the branch and field agent based on the IDs
        $branch = Branch::find($branch_id);
        $field_agent = FieldAgent::find($field_agent_id);

        // Get commission using your method
        $commission = $this->field_agent_commission($branch, $field_agent);

        return response()->json([
            'status' => 'success',
            'data' => $commission
        ]);
}


}
