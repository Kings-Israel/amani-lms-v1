<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Carbon\Carbon;
use App\models\Loan;
use App\models\Group;
use App\models\Arrear;
use App\models\Branch;
use App\models\Report;
use GuzzleHttp\Client;
use App\models\Payment;
use App\models\Product;
use App\models\Referee;
use App\models\Setting;
use App\models\UserSms;
use App\models\Customer;
use App\models\Msetting;
use App\models\Rollover;
use App\models\RoTarget;
use App\models\Regpayment;
use App\models\CustomerSms;
use App\models\Installment;
use App\models\Raw_payment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\models\Customer_location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables as DT;
use App\Imports\LeadsImport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $this->data['title'] = "System Reports";

        $this->data['sub_title'] = "List of All System Reports";

        return view('pages.reports.index', $this->data);
    }

    public function data()
    {
        $lo = [];

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager')) {
            $lo = Report::select('*')->where('for_group', false);
        }

        if (Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer')) {
            $lo = Report::select('*')->where('for_group', false)->where('rname', 'CUSTOMER ACCOUNT STATEMENT');
        }

        if (Auth::user()->hasRole('manager')) {
            $lo = Report::select('*')->where('for_group', false)->where('rname', 'FIELD AGENT PERFORMANCE');
        }

        return Datatables::of($lo)
            ->addColumn('route', function ($lo) {
                $data = $lo->route;
                return '<a href="' . url($data) . '" class="sel-btn btn btn-xs btn-primary" ><i class="feather icon-eye text-danger" ></i> View</a>';
            })
            ->rawColumns(['route', 'checkbox'])
            ->make(true);
    }

    /**************************system user report***************/
    public function system_users_report()
    {
        $this->data['title'] = "System Users Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All System Users";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All System Users in " . $branch->bname;
        }
        return view('pages.reports.system_users', $this->data);
    }

    public function system_users_report_data()
    {
        $lo = User::get(['*'])->each->setAppends([]);
        return Datatables::of($lo)
            ->editColumn('status', function ($lo) {
                if ($lo->status) {
                    return '<p style="display: none">1</p>' . '<h6><span class="badge badge-success"><b>ACTIVE</b></span></h6>';
                } else {
                    return '<p style="display: none">0</p>' . '<h6><span class="badge badge-danger"><b>INACTIVE</b></span></h6>';
                }
            })
            ->addColumn('role', function ($lo) {
                return $lo->roles()->first()->name;
            })
            ->addColumn('branch', function ($lo) {
                $branch = Branch::find($lo->branch_id);
                return $branch->bname;
            })
            ->rawColumns(['status'])
            ->make(true);

    }

    /***********************Disbursed loans****************/
    public function disbursed_loans()
    {
        $this->data['title'] = "System Disbursed Loans Report";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        // $this->data['credit_officers'] = User::role('field_agent')->where('status', 1)->get();
        $branch = Branch::find(Auth::user()->branch_id);
        $user = \auth()->user();

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $this->data['sub_title'] = "List of All Disbursed Loans";
            $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
            $this->data['check_role'] = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant') or Auth::user()->hasRole('sector_manager') or Auth::user()->hasRole('customer_informant');
        } elseif ($user->hasRole('field_agent')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();
            $this->data['credit_officers'] = User::role('field_agent')->where(['status' => true, 'id' => $user->id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
            if (\auth()->user()->hasRole('field_agent')){
                $this->data['lfs'] = User::where(['id'=> Auth::user()->id])->where('status', true)->get();
            } else{
                $this->data['lfs'] = User::where(['id'=> Auth::user()->field_agent_id])->where('status', true)->get();
            }
            $this->data['check_role'] = false;
        } else {
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All Disbursed Loans in " . $branch->bname;
            $this->data['check_role'] = false;
            $this->data['lfs'] = User::where(['id'=> Auth::user()->field_agent_id])->where('status', true)->get();
        }

        return view('pages.reports.disbursed_loans', $this->data);
    }

    public function disbursed_loans_data(Request $request)
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $branch = $request->branch;
        $lf = $request->lf;

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.approved', true],
                    ['loans.disbursed', true],
                ])
                ->when($lf && $lf != 'all', function ($query) use ($lf) {
                    $query->where('customers.field_agent_id', '=', $lf);
                })
                ->when($branch && $branch != 'all', function ($query) use ($branch) {
                    $query->where('customers.branch_id', $branch);
                })
                ->when($start_date != '', function ($query) use ($start_date) {
                    $query->whereDate('disbursement_date', '>=', $start_date);
                })
                ->when($end_date != '', function ($query) use ($end_date) {
                    $query->whereDate('disbursement_date', '<=', $end_date);
                })
                ->whereIn('customers.branch_id', $activeBranches);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.approved', true],
                    ['loans.disbursed', true],
                    ['customers.field_agent_id', '=', Auth::user()->id]
                ])->whereIn('customers.branch_id', $activeBranches);
        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.approved', true],
                    ['loans.disbursed', true],
                    ['customers.branch_id', '=', Auth::user()->branch_id]
                ])->whereIn('customers.branch_id', $activeBranches);
        }
        return Datatables::of($lo)
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'Yes';
                } else {
                    return 'NO';
                }
            })
            ->addColumn('credit_officer', function ($lo) {
                $cust = Customer::find($lo->customer_id);
                $field_agent = User::find($cust->field_agent_id);
                return $field_agent->name;
            })
            ->addColumn('amount_paid', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                return $payments;
            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    // $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                    $total = $lo->total_amount + $rollover->rollover_interest;
                } else {
                    $total = $lo->total_amount;
                }

                return $total;


            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->total_amount + $rollover->rollover_interest;
                } else {
                    $total = $lo->total_amount;
                }
                $balance = $total - $payments;

                return $balance;
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);


                return '<a href="' . route('loans.post_disburse', ['id' => $data]) . '"    class="sel-btn btn btn-xs btn-primary"><i class="feather icon-eye text-info"></i> Disburse</a>';
                /* return '<div class="btn-group text-center">
                                                 <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                         <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                             <li><a href="' . route('loans.post_disburse', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Disburse</a></li>
                                                            <li><a href="' . route('loans.destroy', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>


                                                         </ul>
                                         </div>';*/
            })
            ->addColumn('checkbox', function ($lo) {
                return '<input type="checkbox" name="id" value="' . $lo->id . '" id="' . $lo->id . '">';
            })
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->rawColumns(['action', 'checkbox'])
            ->toJson();
    }

    /***********************loan_pending_approval loans****************/
    public function loan_pending_approval()
    {
        $this->data['title'] = "Loans Waiting Approval Report";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();


        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Loans Waiting Approval";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['branches'] = Branch::where('id', $branch->branch_id)->get();

            $this->data['sub_title'] = "List of All Loans Waiting Approval in " . $branch->bname;
        }

        return view('pages.reports.loan_pending_approval', $this->data);

    }

    /***********************loan_pending_approval loans****************/
    public function loan_pending_disbursements()
    {
        $this->data['title'] = "Loans Waiting Disbursement Report";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();


        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Loans Waiting Disbursement";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['branches'] = Branch::where('id', $branch->branch_id)->get();

            $this->data['sub_title'] = "List of All Loans Waiting Disbursement in " . $branch->bname;
        }

        return view('pages.reports.loan_pending_disbursements', $this->data);
    }

    /***********************loan_due_today loans****************/
    public function loan_due_today()
    {
        $this->data['title'] = "Loans Due Today Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Loans Due today";
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['lfs'] = User::role('field_agent')->where('status', 1)->get();
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All Loans Due today in " . $branch->bname;
            $this->data['branches'] = Branch::where('id', '=', $branch->id)->get();
            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'branch_id' => $branch->id])->get();
        }

        return view('pages.reports.loan_due_today', $this->data);
    }

    public function loans_due_today_data(Request $request)
    {
        //check date
        if ($request->due_date != null) {
            $due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        } else {
            $due_date = Carbon::now()->format('Y-m-d');
        }

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer')) {
            //credit officer and branch specified
            if ($request->lf != 'all' and $request->branch != 'all') {
                $lo = DB::table('loans')
                    ->join('customers', function ($join) use ($request) {
                        $join->on('customers.id', '=', 'loans.customer_id')
                            ->where('customers.field_agent_id', '=', $request->lf)
                            ->where('customers.branch_id', '=', $request->branch);
                    })
                    //->join('customers', 'customers.id', '=', 'loans.customer_id')
                    ->join('products', 'products.id', '=', 'loans.product_id')
                    ->join('installments', function ($join) use ($due_date) {
                        $join->on('installments.loan_id', '=', 'loans.id')
                            ->where([
                                ['installments.completed', false]
                            ])
                            ->whereDate('due_date', $due_date);
                    })
                    ->select('loans.*', 'installments.current', 'installments.completed', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'));

            } // credit officer specified
            elseif ($request->lf == 'all' and $request->branch != 'all') {
                $lo = DB::table('loans')
                    ->join('customers', function ($join) use ($request) {
                        $join->on('customers.id', '=', 'loans.customer_id')
                            ->where('customers.branch_id', '=', $request->branch);
                    })
                    //->join('customers', 'customers.id', '=', 'loans.customer_id')
                    ->join('products', 'products.id', '=', 'loans.product_id')
                    ->join('installments', function ($join) use ($due_date) {
                        $join->on('installments.loan_id', '=', 'loans.id')
                            ->where([
                                /*['installments.current', true],*/
                                ['installments.completed', false]
                            ])
                            ->whereDate('due_date', $due_date);
                    })
                    ->select('loans.*', 'installments.current', 'installments.completed', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'));
            } //branch specified
            elseif ($request->lf != 'all' and $request->branch == 'all') {
                $lo = DB::table('loans')
                    ->join('customers', function ($join) use ($request) {
                        $join->on('customers.id', '=', 'loans.customer_id')
                            ->where('customers.field_agent_id', '=', $request->lf);
                    })
                    //->join('customers', 'customers.id', '=', 'loans.customer_id')
                    ->join('products', 'products.id', '=', 'loans.product_id')
                    ->join('installments', function ($join) use ($due_date) {
                        $join->on('installments.loan_id', '=', 'loans.id')
                            ->where([
                                /*['installments.current', true],*/
                                ['installments.completed', false]
                            ])
                            ->whereDate('due_date', $due_date);
                    })
                    ->select('loans.*', 'installments.current', 'installments.completed', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'));

            } //view all
            else {
                $lo = DB::table('loans')
                    ->join('customers', 'customers.id', '=', 'loans.customer_id')
                    ->join('products', 'products.id', '=', 'loans.product_id')
                    ->join('installments', function ($join) use ($due_date) {
                        $join->on('installments.loan_id', '=', 'loans.id')
                            ->where([
                                /*['installments.current', true],*/
                                ['installments.completed', false]
                            ])
                            ->whereDate('due_date', $due_date);
                    })
                    ->select('loans.*', 'installments.current', 'installments.completed', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'));
            }
        } elseif (Auth::user()->hasRole('field_agent')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->join('installments', function ($join) use ($due_date) {
                    $join->on('installments.loan_id', '=', 'loans.id')
                        ->where([
                            /*['installments.current', true],*/
                            ['installments.completed', false]
                        ])
                        ->whereDate('due_date', $due_date);
                })
                ->select('loans.*', 'installments.current', 'installments.completed', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('customers.field_agent_id', '=', Auth::user()->id);
        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->join('installments', function ($join) use ($due_date) {
                    $join->on('installments.loan_id', '=', 'loans.id')
                        ->where([
                            /*['installments.current', true],*/
                            ['installments.completed', false]

                        ])
                        ->whereDate('due_date', $due_date);
                })
                ->select('loans.*', 'installments.current', 'installments.completed', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('customers.branch_id', '=', Auth::user()->branch_id);

        }

        return Datatables::of($lo)
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
            ->addColumn('installment_due', function ($lo) use ($due_date) {
                $instal = Installment::query()->where(['loan_id' => $lo->id, 'due_date' => $due_date])->first();
                if ($instal) {
                    return $instal->total - $instal->amount_paid;
                } else {
                    return '-';
                }
            })->addColumn('next_payment_date', function ($lo) use ($due_date) {
                $instal1 = Installment::query()->where(['loan_id' => $lo->id, 'due_date' => $due_date])->first();
                if ($instal1) {
                    $previous_installment_position = $instal1->position;
                    $next_installment_position = $previous_installment_position + 1;
                    $next_installment = Installment::query()->where(['loan_id' => $lo->id, 'position' => $next_installment_position])->first();
                    if ($next_installment) {
                        return $next_installment->due_date;
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }
            })
            ->addColumn('amount_paid', function ($lo) {
                $payments = Payment::query()->where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                return $payments;
            })
            ->addColumn('field_agent', function ($lo) {
                $Customer = Customer::query()->find($lo->customer_id);
                $user = User::query()->find($Customer->field_agent_id);

                return $user->name;
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);

                return '<a href="' . route('loans.post_disburse', ['id' => $data]) . '"    class="sel-btn btn btn-xs btn-primary"><i class="feather icon-eye text-info"></i> Disburse</a>';
            })
            ->addColumn('checkbox', function ($lo) {
                return '<input type="checkbox" name="id" value="' . $lo->id . '" id="' . $lo->id . '">';
            })
            ->rawColumns(['action', 'checkbox'])
            ->make(true);

    }

    //mpesa reports
    public function mpesa_repayments()
    {
        $this->data['title'] = "Mpesa Repayments Reports";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "MPESA Repayments Report";
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "MPESA Repayments Report: " . $branch->bname;
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();
        }

        return view('pages.reports.mpesa_repayments', $this->data);

    }

    public function mpesa_repayments_data(Request $request)
    {
        if ($request->start_date and $request->end_date and $request->branch != 'all') {
            $myArray = array();
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
                $branch = Branch::find($request->branch);
            } else {
                $branch = Branch::find(Auth::user()->branch_id);
            }
            foreach ($branch->loans()->get() as $lons) {
                array_push($myArray, $lons->id);
            }
            $lo = Payment::query()->where('payment_type_id', '=', 1)->whereIn('loan_id', $myArray)->whereBetween('date_payed', [$request->start_date, $request->end_date])->orderByDesc('id');

        } elseif ($request->start_date and $request->end_date and $request->branch == 'all') {
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
                $branches = Branch::query()->where('status', '=', true)->get();
            } else {
                $branches = Branch::query()->where('id', '=', Auth::user()->branch_id)->where('status', '=', true)->get();
            }
            $myArray = array();
            foreach ($branches as $branch) {
                foreach ($branch->loans()->get() as $lons) {
                    array_push($myArray, $lons->id);
                }
            }
            $lo = Payment::query()->where('payment_type_id', '=', 1)->whereIn('loan_id', $myArray)->whereBetween('date_payed', [$request->start_date, $request->end_date])->orderByDesc('id');
        } elseif (Auth::user()->hasRole('investor')) {
            $branch = Branch::find(Auth::user()->branch_id);
            $myArray = array();
            foreach ($branch->loans()->get() as $lons) {
                array_push($myArray, $lons->id);
            }
            $lo = Payment::where('payment_type_id', 1)->whereIn('loan_id', $myArray)->orderByDesc('id');
        } else {
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
                $branches = Branch::query()->where('status', '=', true)->get();
            } else {
                $branches = Branch::query()->where('id', '=', Auth::user()->branch_id)->where('status', '=', true)->get();
            }
            $myArray = array();
            foreach ($branches as $branch) {
                foreach ($branch->loans()->get() as $lons) {
                    array_push($myArray, $lons->id);
                }
            }
            $lo = Payment::query()->where('payment_type_id', '=', 1)->whereIn('loan_id', $myArray)->orderByDesc('id');
        }

        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {
                return $lo->Loan()->first()->getBranchAttribute()->bname;

            })
            ->addColumn('owner', function ($lo) {
                return $lo->Loan()->first()->owner;

            })
            ->addColumn('phone', function ($lo) {
                return $lo->Loan()->first()->phone;

            })
            ->addColumn('loan_account', function ($lo) {
                return $lo->Loan()->first()->loan_account;

            })
            ->toJson();
    }

    //inactive customers reports
    public function inactive_customers()
    {
        $this->data['title'] = "Inactive Customers Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Inactive Customers";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All Inactive Customers in " . $branch->bname;
        }

        return view('pages.reports.inactive_customers', $this->data);
    }

    public function inactive_customers_data()
    {

        $customers = Customer::whereNotIn('id', Customer::whereHas('loans', function ($query) {
            $query->where('settled', 0);
        })->pluck('id'));

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager')) {
            $customers = $customers;
        } elseif (Auth::user()->hasRole('field_agent')) {
            $customers = $customers->where('field_agent_id', Auth::id());
        } else {
            $customers = $customers->where('branch_id', Auth::user()->branch_id);
        }

        return DT::eloquent($customers)
            ->addColumn('fullName', function (\App\models\Customer $customer) {
                return sprintf("%s %s", $customer->title, $customer->fullName);
            })
            ->addColumn('branchName', function (\App\models\Customer $customer) {
                return $customer->branch->bname;
            })
            ->addColumn('loanOfficer', function (\App\models\Customer $customer) {
                return $customer->Officer->name;
            })
            ->addColumn('phoneNumber', function (\App\models\Customer $customer) {
                return $customer->phone;
            })
            ->addColumn('profession', function (\App\models\Customer $customer) {
                return $customer->industry->iname;
            })
            ->addColumn('dealingIn', function (\App\models\Customer $customer) {
                return $customer->businessType->bname;
            })
            ->addColumn('identificationNumber', function (\App\models\Customer $customer) {
                return $customer->id_no;
            })
            ->addColumn('prequalifiedAmount', function (\App\models\Customer $customer) {
                // $amount = \DB::table('prequalified_loans')->whereId($customer->prequalified_amount)->first();
                return $customer->prequalified_amount;
            })
            ->addColumn('customerCreatedDate', function (\App\models\Customer $customer) {

                $dateTime = \Carbon\Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('lastDisbursement', function (\App\models\Customer $customer) {

                $rawDate = $customer->disbursements()->orderByDesc('disbursement_date')->first()->disbursement_date ?? null;

                $dateTime = \Carbon\Carbon::parse($rawDate);
                return sprintf("%s/%s/%s %s:%s:%s %s",
                    $dateTime->day, $dateTime->month, $dateTime->year, $dateTime->hour, $dateTime->minute, $dateTime->second, $dateTime->format('A'));
            })
            ->addColumn('inactiveDays', function (\App\models\Customer $customer) {

                $lastCompletePayment = $customer->lastCompletePayment()->orderByDesc('date_payed')->first()->date_payed ?? null;
                $lastCompletePayment = \Carbon\Carbon::parse($lastCompletePayment);

                return \Carbon\Carbon::now()->diffInDays($lastCompletePayment);
            })
            ->addColumn('totalNumberOfLoans', function (\App\models\Customer $customer) {
                return $customer->loans->count();
            })
            ->toJson();
    }

    //blocked customers reports
    public function blocked_customers()
    {
        $data['title'] = "Blocked Customers Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $data['sub_title'] = "List of All Blocked Customers";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $data['sub_title'] = "List of All Blocked Customers in " . $branch->bname;

        }

        return view('pages.reports.blocked_customers', $data);

    }

    public function blocked_customers_data()
    {
        $customers = Customer::where('status', 0);

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {

            $customers = $customers;

        } elseif (Auth::user()->hasRole('field_agent')) {

            $customers = $customers->where('field_agent_id', Auth::id());
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $customers = $customers->where('field_agent_id', Auth::user()->field_agent_id);
        } else {

            $customers = $customers->where('branch_id', Auth::user()->branch_id);
        }

        return DT::eloquent($customers)
            ->addColumn('fullName', function (\App\models\Customer $customer) {
                return sprintf("%s %s", $customer->title, $customer->fullName);
            })
            ->addColumn('branchName', function (\App\models\Customer $customer) {
                return $customer->branch->bname;
            })
            ->addColumn('loanOfficer', function (\App\models\Customer $customer) {
                return $customer->Officer->name;
            })
            ->addColumn('phoneNumber', function (\App\models\Customer $customer) {
                return $customer->phone;
            })
            ->addColumn('profession', function (\App\models\Customer $customer) {
                return $customer->industry->iname;
            })
            ->addColumn('dealingIn', function (\App\models\Customer $customer) {
                return $customer->businessType->bname;
            })
            ->addColumn('identificationNumber', function (\App\models\Customer $customer) {
                return $customer->id_no;
            })
            ->addColumn('prequalifiedAmount', function (\App\models\Customer $customer) {
                // $amount = \DB::table('prequalified_loans')->whereId($customer->prequalified_amount)->first();
                return $customer->prequalified_amount;
            })
            ->addColumn('customerCreatedDate', function (\App\models\Customer $customer) {

                $dateTime = \Carbon\Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('lastDisbursement', function (\App\models\Customer $customer) {

                $rawDate = $customer->disbursements()->orderByDesc('disbursement_date')->first()->disbursement_date ?? null;

                $dateTime = \Carbon\Carbon::parse($rawDate);
                return sprintf("%s/%s/%s %s:%s:%s %s",
                    $dateTime->day, $dateTime->month, $dateTime->year, $dateTime->hour, $dateTime->minute, $dateTime->second, $dateTime->format('A'));
            })
            ->addColumn('inactiveDays', function (\App\models\Customer $customer) {

                $lastCompletePayment = $customer->lastCompletePayment()->orderByDesc('date_payed')->first()->date_payed ?? null;
                $lastCompletePayment = \Carbon\Carbon::parse($lastCompletePayment);

                return \Carbon\Carbon::now()->diffInDays($lastCompletePayment);
            })
            ->addColumn('totalNumberOfLoans', function (\App\models\Customer $customer) {
                return $customer->loans->count();
            })
            ->toJson();
    }


    //roll over loans
    public function rolled_over_loans()
    {
        $this->data['title'] = "Rolled Over Loans Report";
        $user = \auth()->user();
        $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();

        if ($user->hasRole('admin') || Auth::user()->hasRole('accountant')) {
            $this->data['sub_title'] = "List of All Rolled Over Loans";
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();


        } elseif ($user->hasRole('field_agent')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $branch = Branch::find($user->branch_id);
            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => $user->id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        } elseif ($user->hasRole('collection_officer')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $branch = Branch::find($user->branch_id);
            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => $user->field_agent_id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All Rolled Over Loans in " . $branch->bname;
            $this->data['lfs'] = User::role('field_agent')->where('branch_id', $branch->id)->get();
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();


        }

        return view('pages.reports.rolled_over_loans', $this->data);


    }

    public function rolled_over_loans_data(Request $request)
    {
        if ($request->name) {
            $user = User::find($request->name);
            //dd($user->id);

            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.rolled_over', '=', true],
                    // ['customers.branch_id', '=', $user->branch_id],
                    ['customers.field_agent_id', '=', $user->id],
                    ['loans.settled', '=', false],


                ]);
        } elseif ($request->branch) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['customers.branch_id', '=', $request->branch],
                    ['loans.rolled_over', '=', true],
                    ['loans.settled', '=', false],


                ]);
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            //$lo = Loan::where(['rolled_over' => true, 'settled' => false]);
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    //['customers.branch_id', '=', Auth::user()->branch_id],
                    ['loans.rolled_over', '=', true],
                    ['loans.settled', '=', false],


                ]);


        } elseif (Auth::user()->hasRole('field_agent')) {
            /*$branch = Auth::user();
            $lo = $branch->loans()->where(['rolled_over' => true, 'settled' => false])->get();*/
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.rolled_over', '=', true],
                    ['customers.branch_id', '=', Auth::user()->branch_id],
                    ['customers.field_agent_id', '=', Auth::user()->id],
                    ['loans.settled', '=', false],


                ]);
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.rolled_over', '=', true],
                    ['customers.branch_id', '=', Auth::user()->branch_id],
                    ['customers.field_agent_id', '=', Auth::user()->field_agent_id],
                    ['loans.settled', '=', false],


                ]);
        } else {
            /*  $branch = Branch::where('id', Auth::user()->branch_id)->first();
              $lo = $branch->loans()->where(['rolled_over' => true, 'settled' => false])->get();*/
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['customers.branch_id', '=', Auth::user()->branch_id],
                    ['loans.rolled_over', '=', true],
                    ['loans.settled', '=', false],


                ]);


        }

        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('amount_paid', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');


                return $payments;


            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));

                }

                return $total;


            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));


                }
                $balance = $total - $payments;


                return $balance;


            })
            ->addColumn('amount_paid_b4_rollover', function ($lo) {
                $ro = Rollover::where('loan_id', $lo->id)->first();
                $product = Product::find($lo->product_id);


                $rollover = ($total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100))) - (int)$ro->amount;
                return $rollover;

            })
            ->addColumn('balance_b4_rollover', function ($lo) {
                $ro = Rollover::where('loan_id', $lo->id)->first();


                $rollover = $ro->amount;
                return $rollover;

            })
            ->addColumn('rollover_fee', function ($lo) {
                $ro = Rollover::where('loan_id', $lo->id)->first();


                $rollover = (int)$ro->rollover_interest;
                return $rollover;

            })
            ->addColumn('rolled_over_date', function ($lo) {
                $ro = Rollover::where('loan_id', $lo->id)->first();


                $rollover = $ro->rollover_date;
                return $rollover;


            })
            ->addColumn('days', function ($lo) {
                $ro = Rollover::where('loan_id', $lo->id)->first();
                $rolledOvadate = Carbon::parse($ro->rollover_date);
                $overdue_days = $rolledOvadate->diffInDays(Carbon::now());
                return $overdue_days;

            })
            ->addColumn('field_agent', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                $user = User::find($Customer->field_agent_id);

                return $user->name;

            })
            ->toJson();

    }

    //loan collections
    public function loan_collections()
    {
        $this->data['title'] = "Loan Collections Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Collected Loans";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All Collected Loans in " . $branch->bname;
        }
        $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        }
        return view('pages.reports.loan_collections', $this->data);
    }

    public function loan_collections_data(Request $request)
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        if ($request->start_date and $request->end_date and $request->branch != 'all') {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['customers.branch_id', '=', $request->branch],
                    ['loans.disbursed', '=', true],
                ])
                ->whereBetween('disbursement_date', [$request->start_date, $request->end_date]);
        } elseif ($request->start_date and $request->end_date and $request->branch == 'all') {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                ])
                ->whereIn('customers.branch_id', $activeBranches)
                ->whereBetween('disbursement_date', [$request->start_date, $request->end_date]);
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('loans.disbursed', '=', true)
                ->whereIn('customers.branch_id', $activeBranches);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['customers.branch_id', '=', Auth::user()->branch_id],
                    ['loans.disbursed', '=', true],
                    ['customers.field_agent_id', '=', Auth::user()->id]
                ]);
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['customers.branch_id', '=', Auth::user()->branch_id],
                    ['loans.disbursed', '=', true],
                    ['customers.field_agent_id', '=', Auth::user()->field_agent_id]
                ]);
        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['customers.branch_id', '=', Auth::user()->branch_id],
                    ['loans.disbursed', '=', true],
                ]);
        }
        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('amount_paid', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');


                return $payments;


            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));

                }

                return $total;


            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));


                }
                $balance = $total - $payments;


                return $balance;


            })
            ->toJson();
    }

    //loan balances
    public function loans_balance()
    {
        $this->data['title'] = "Loan Balances Report";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
        $branch = Branch::find(Auth::user()->branch_id);
        $user = \auth()->user();


        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Loan Balances";
        } elseif ($user->hasRole('field_agent')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => $user->id])->get();
            $this->data['sub_title'] = "List of All Loan Balances in " . $branch->bname;

        } elseif ($user->hasRole('collection_officer')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => $user->field_agent_id])->get();
            $this->data['sub_title'] = "List of All Loan Balances in " . $branch->bname;

        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->where('status', '=', true)->get();
            $this->data['lfs'] = User::role('field_agent')->where('status', true)->where('branch_id', Auth::user()->branch_id)->get();
            $this->data['sub_title'] = "List of All Loan Balances in " . $branch->bname;
        }
        return view('pages.reports.loans_balance', $this->data);
    }

    public function loans_balance_data(Request $request)
    {

        //dd($request->all());
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        if (Auth::user()->hasRole('field_agent')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['customers.field_agent_id', '=', Auth::user()->id]
                ]);
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['customers.field_agent_id', '=', Auth::user()->field_agent_id]
                ]);
        } elseif ($request->lf != 'all' and $request->branch != 'all') {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['customers.field_agent_id', '=', $request->lf],
                    ['customers.branch_id', '=', $request->branch]
                ]);
        } elseif ($request->lf == 'all' and $request->branch == 'all') {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                ])
                ->whereIn('customers.branch_id', $activeBranches);
        } elseif ($request->branch != 'all' and $request->lf == 'all') {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['customers.branch_id', '=', $request->branch]
                ]);
        } elseif ($request->branch == 'all' and $request->lf != 'all') {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['customers.field_agent_id', '=', $request->lf],
                ]);
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                ])->whereIn('customers.branch_id', $activeBranches);

        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['customers.branch_id', '=', Auth::user()->branch_id]
                ]);
        }
        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('amount_paid', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');


                return $payments;


            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));

                }

                return $total;


            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));


                }
                $balance = $total - $payments;


                return $balance;


            })
            ->addColumn('field_agent', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                $user = User::find($Customer->field_agent_id);

                return $user->name;

            })
            ->addColumn('percentage_paid', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));


                }
                $percentage_paid = ($payments / $total) * 100;


                return number_format($percentage_paid, 2);

            })
            ->toJson();
    }

    //loan arrears
    public function loan_arrears(Request $request)
    {
        /* $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');


         $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
             $builder->where('amount', '!=', 0);
         })->whereHas('customer', function (Builder $builder) use ($request, $activeBranches) {
             $builder->whereIn('branch_id', $activeBranches);
         })->where(['disbursed' => true, 'settled' => false, 'id' => 9644])->get();
         dd($loans_w_arrears);*/


        $this->data['title'] = "Loan Arrears Report";
        $user = \auth()->user();
        $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
        if ($user->hasRole('admin') || $user->hasRole('accountant') || $user->hasRole('agent_care')) {
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();

            $this->data['sub_title'] = "List of Loan Arrears";
        } elseif ($user->hasRole('field_agent')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $branch = Branch::find($user->branch_id);
            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => $user->id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        } elseif ($user->hasRole('collection_officer')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $branch = Branch::find($user->branch_id);
            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => $user->field_agent_id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        } else {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $branch = Branch::find($user->branch_id);
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        }
        return view('pages.reports.loan_arrears', $this->data);
    }

    public function loan_arrears_data(Request $request)
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        if ($request->lf != 'all' and $request->branch != 'all') {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', $request->lf);
                $builder->where('branch_id', '=', $request->branch);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif ($request->lf == 'all' and $request->branch != 'all') {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('branch_id', '=', $request->branch);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif ($request->lf != 'all' and $request->branch == 'all') {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', $request->lf);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request, $activeBranches) {
                $builder->whereIn('branch_id', $activeBranches);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif (Auth::user()->hasRole('field_agent')) {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', Auth::user()->id);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', Auth::user()->field_agent_id);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } else {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('branch_id', '=', Auth::user()->branch_id);
            })->where(['disbursed' => true, 'settled' => false])->get();
        }
        $lo = array();
        foreach ($loans_w_arrears as $lns) {
            $inst = DB::table('installments')->where(['loan_id' => $lns->id])->orderBy('last_payment_date', 'desc')->first();
            // TODO: Remove installment check. All loans approved loans
            if ($inst) {
                $last_payment_date = $inst->last_payment_date;
                $product = Product::find($lns->product_id);

                if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                   // array_push($lo, $lns);
                    $customer = Customer::find($lns->customer_id);
                    $instal = DB::table('installments')->where(['loan_id' => $lns->id, 'current' => true])->first();
                    $next_payment_date = null;
                    if ($instal){
                        $next_payment_date = $instal->due_date;
                    }

                    $interest_paid = 0;

                    if ($lns->total_amount_paid > $lns->loan_amount){
                        $principal_paid = $lns->loan_amount;
                        $interest_paid = $lns->total_amount_paid - $lns->loan_amount;
                    } else {
                        $principal_paid = $lns->total_amount_paid;
                    }
                    $principal_due = $lns->loan_amount - $principal_paid;

                    $interest_due = ($lns->total_amount - $lns->loan_amount) - $interest_paid;

                    //overdue
                    $arrears = DB::table('arrears')->where(['loan_id' => $lns->id, ['amount', '>', 0]])->orderBy('id')->get();
                    $overdue_days = 0;
                    $total_arrears = $arrears->sum('amount');
                    // info($lns->customer->fname.' '.$lns->customer->lname.' - '.$lns->customer->phone.' - '.$total_arrears.' - '.$lns->id);
                    $principal_arrears = 0;
                    $interest_arrears = 0;

                    if ($arrears->first()){
                        $inst = Installment::find($arrears->first()->installment_id);
                        $created = Carbon::parse($inst->due_date);
                        $overdue_days = $created->diffInDays(Carbon::now());

                        foreach ($arrears as $arrear) {
                            $instal = Installment::find($arrear->installment_id);
                            if ($instal) {
                                $bal = $instal->principal_amount - $instal->amount_paid;
                                $principal_arrears += $bal;

                                if ($instal->amount_paid > $instal->principal_amount) {
                                    $Ipaid = $instal->amount_paid - $instal->principal_amount;
                                } else {
                                    $Ipaid = 0;
                                }
                                $Ibal = $instal->interest - $Ipaid;
                                $interest_arrears += $Ibal;
                            }
                        }
                    }

                    //loan offoicer
                    $field_agent = User::find($customer->field_agent_id);
                    //balance
                    $balance = $lns->total_amount - $lns->total_amount_paid;
                    //total arrears
                    //total Installments
                    $schedules = DB::table('installments')->where(['loan_id' => $lns->id])->count();

                    //elapsed schedule
                    $today = Carbon::now()->format('Y-m-d');

                    $skipped_installments = DB::table('installments')->where(['loan_id' => $lns->id, 'completed' => false])->where('due_date', '<', $today)->get();
                    //$arrears = DB::table('arrears')->where(['loan_id' => $lns->id])->where('amount', '>', 0)->get();
                    $elapsed_schedule = count($skipped_installments);

                    //loan type
                    $loan_type = "Weekly";
                    if ($lns->loan_type_id == 1){
                        $loan_type= "Daily";
                    }

                    array_push($lo, array(
                        'id' => $lns->id,
                        'loan_account' => $lns->loan_account,
                        'loan_amount' => $lns->loan_amount,
                        'loan_type' => $loan_type,
                        'last_payment_date' => $last_payment_date,
                        'product_id' => $lns->product_id,
                        'customer_id' => $lns->customer_id,
                        'date_created' => $lns->date_created,
                        'end_date' => $lns->end_date,
                        'approved' => $lns->approved,
                        'approved_date' => $lns->approved_date,
                        'disbursed' => $lns->disbursed,
                        'disbursement_date' => $lns->disbursement_date,
                        'created_at' => $lns->created_at,
                        'updated_at' => $lns->updated_at,
                        'purpose' => $lns->purpose,
                        'settled' => $lns->settled,
                        'rolled_over' => $lns->rolled_over,
                        'approved_by' => $lns->approved_by,
                        'disbursed_by' => $lns->disbursed_by,
                        'fname' => $customer->fname,
                        'lname' => $customer->lname,
                        'phone' => $customer->phone,
                        'product_name' => $product->product_name,
                        //'installments' => $product->installments,
                        'installments' => $schedules,
                        'interest' => $product->interest,
                        'owner' => $customer->fname . " " . $customer->lname,
                        'next_payment_date'=> $next_payment_date,
                        'principal_paid' => $principal_paid,
                        'principal_due' => $principal_due,
                        'interest_paid' => $interest_paid,
                        'interest_due' => $interest_due,
                        'overdue' => $overdue_days,
                        'field_agent' => $field_agent->name,
                        'balance' => $balance,
                        'branch' => Branch::find($customer->branch_id)->bname,
                        'total_arrears' => $total_arrears,
                        'principal_arrears' => $principal_arrears,
                        'interest_arrears'=>$interest_arrears,
                        'elapsed_schedule' => $elapsed_schedule,
                        'total_amount' => $lns->total_amount,
                        'total_amount_paid' => $lns->total_amount_paid
                    ));
                }
            }
        }

        return Datatables::of($lo)
            ->addColumn('action', function ($lo) {
                return '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-settings"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" x-placement="top-end" style="position: absolute; transform: translate3d(87px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                           <a class="dropdown-item" href="' . route('list_customer_thread', encrypt($lo['customer_id'])) . '"><i class="feather icon-clock"></i> View History</a>
                            ';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    //skipped payments
    public function loan_skipped_payments()
    {
        $this->data['title'] = "Loan Arrears Skipped Payments Report";
        $user = \auth()->user();
        if ($user->hasRole('admin') || $user->hasRole('accountant')) {
            $this->data['sub_title'] = "List of Loan Arrears with skipped payments";
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
        } elseif ($user->hasRole('field_agent')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $branch = Branch::find($user->branch_id);
            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => $user->id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        } elseif ($user->hasRole('collection_officer')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $branch = Branch::find($user->branch_id);
            $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => $user->field_agent_id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All Loan Arrears with skipped payments in " . $branch->bname;
            $this->data['branches'] = Branch::where('id', $branch->id)->get();
            $this->data['lfs'] = User::role('field_agent')->where('status', true)->where('branch_id', $branch->id)->get();
        }
        return view('pages.reports.loan_skipped_payments', $this->data);
    }

    public function loan_skipped_payments_data(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');
        $start = $request->start_date;
        $end = $request->end_date;
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');

        if ($request->lf != 'all' and $request->branch != 'all') {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', $request->lf);
                $builder->where('branch_id', '=', $request->branch);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif ($request->lf == 'all' and $request->branch != 'all') {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('branch_id', '=', $request->branch);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif ($request->lf != 'all' and $request->branch == 'all') {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', $request->lf);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request, $activeBranches) {
                $builder->whereIn('branch_id', $activeBranches);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif (Auth::user()->hasRole('field_agent')) {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', Auth::user()->id);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', Auth::user()->field_agent_id);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } else {
            $loans_w_arrears = Loan::whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('branch_id', '=', Auth::user()->branch_id);
            })->where(['disbursed' => true, 'settled' => false])->get();
        }

        $lo = array();

        foreach ($loans_w_arrears as $lns) {
            $last_payment_date = $lns->last_payment_date;
            $product = Product::find($lns->product_id);
            $skipped_installments = Installment::where(['loan_id' => $lns->id, 'completed' => false])->whereDate('due_date', '<', $today)->get();

            $skipped_installments_count = count($skipped_installments);

            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180)) {
                if ($skipped_installments_count > 0) {
                    $customer = Customer::find($lns->customer_id);
                    $instal = DB::table('installments')->where(['loan_id' => $lns->id, 'current' => true])->first();
                    $next_payment_date = null;
                    if ($instal) {
                        $next_payment_date = $instal->due_date;
                    }

                    $interest_paid = 0;

                    if ($lns->total_amount_paid > $lns->loan_amount){
                        $principal_paid = $lns->loan_amount;
                        $interest_paid = $lns->total_amount_paid - $lns->loan_amount;
                    } else {
                        $principal_paid = $lns->total_amount_paid;
                    }

                    $principal_due = $lns->loan_amount - $principal_paid;

                    $interest_due = ($lns->total_amount - $lns->loan_amount) - $interest_paid;

                    //overdue
                    $arrears = DB::table('arrears')->where(['loan_id' => $lns->id, ['amount', '>', 0]])->orderBy('id')->get();
                    $overdue_days = 0;
                    $total_arrears = $arrears->sum('amount');
                    $principal_arrears = 0;
                    $interest_arrears = 0;

                    if ($arrears->first()){
                        $inst = Installment::find($arrears->first()->installment_id);
                        $created = Carbon::parse($inst->due_date);
                        $overdue_days = $created->diffInDays(Carbon::now());

                        foreach ($arrears as $arrear) {
                            $instal = Installment::find($arrear->installment_id);
                            if ($instal) {
                                $bal = $instal->principal_amount - $instal->amount_paid;
                                $principal_arrears += $bal;
                                if ($instal->amount_paid > $instal->principal_amount) {
                                    $Ipaid = $instal->amount_paid - $instal->principal_amount;
                                } else {
                                    $Ipaid = 0;
                                }
                                $Ibal = $instal->interest - $Ipaid;
                                $interest_arrears += $Ibal;
                            }
                        }
                    }

                    //loan offoicer
                    $field_agent = User::find($customer->field_agent_id);
                    //balance
                    $balance = $lns->total_amount - $lns->total_amount_paid;
                    //total arrears
                    //total Installments
                    $schedules = DB::table('installments')->where(['loan_id' => $lns->id])->count();

                    //elapsed schedule
                    $elapsed_schedule = 0;
                    $inst = DB::table('installments')->where(['current' => true, 'loan_id' => $lns->id])->get();
                    if ($inst->first()) {
                        if ($lns->end_date < Carbon::now()){
                            $elapsed_schedule = $inst->first()->position;
                        } else {
                            $elapsed_schedule = $inst->first()->position - 1;
                        }
                    } else {
                        $elapsed_schedule = $schedules;
                    }

                    array_push($lo, array(
                        'id' => $lns->id,
                        'loan_account' => $lns->loan_account,
                        'loan_amount' => $lns->loan_amount,
                        'skipped_installments' => $skipped_installments_count,
                        'last_payment_date' => $last_payment_date,
                        'product_id' => $lns->product_id,
                        'customer_id' => $lns->customer_id,
                        'date_created' => $lns->date_created,
                        'end_date' => $lns->end_date,
                        'approved' => $lns->approved,
                        'approved_date' => $lns->approved_date,
                        'disbursed' => $lns->disbursed,
                        'disbursement_date' => $lns->disbursement_date,
                        'created_at' => $lns->created_at,
                        'updated_at' => $lns->updated_at,
                        'purpose' => $lns->purpose,
                        'settled' => $lns->settled,
                        'rolled_over' => $lns->rolled_over,
                        'approved_by' => $lns->approved_by,
                        'disbursed_by' => $lns->disbursed_by,
                        'fname' => $customer->fname,
                        'lname' => $customer->lname,
                        'phone' => $customer->phone,
                        'product_name' => $product->product_name,
                        //'installments' => $product->installments,
                        'installments' => $schedules,
                        'interest' => $product->interest,
                        'owner' => $customer->fname . " " . $customer->lname,
                        'next_payment_date'=>$next_payment_date,
                        'principal_paid' =>$principal_paid,
                        'principal_due' => $principal_due,
                        'interest_paid'=>$interest_paid,
                        'interest_due'=>$interest_due,
                        'overdue' => $overdue_days,
                        'field_agent' => $field_agent->name,
                        'balance'=>$balance,
                        'branch' => Branch::find($customer->branch_id)->bname,
                        'total_arrears' => $total_arrears,
                        'principal_arrears' => $principal_arrears,
                        'interest_arrears'=>$interest_arrears,
                        'elapsed_schedule' => $elapsed_schedule,
                        'total_amount' => $lns->total_amount,
                        'total_amount_paid'=>$lns->total_amount_paid
                    ));
                }
            }
        }

        return Datatables::of($lo)->toJson();
    }

    //non performing loans
    public function non_performing_loans()
    {
        $this->data['title'] = "Non Performing Loans Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Non Performing Loans";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All Non Performing Loans in " . $branch->bname;
        }
        return view('pages.reports.non_performing_loans', $this->data);

    }

    public function non_performing_loans_data()
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $unsettled_loans = Loan::with(['customer', 'product'])
                ->whereHas('customer', function (Builder $builder) use ($activeBranches) {
                    $builder->whereIn('branch_id', $activeBranches);
                })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif (Auth::user()->hasRole('field_agent')) {
            $user = Auth::user();
            $unsettled_loans = $user->loans()->whereHas('customer', function (Builder $builder) use ($activeBranches) {
                $builder->whereIn('branch_id', $activeBranches);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $user = User::where(['id' => Auth::user()->field_agent_id])->first();
            $unsettled_loans = $user->loans()->whereHas('customer', function (Builder $builder) use ($activeBranches) {
                $builder->whereIn('branch_id', $activeBranches);
            })->where(['disbursed' => true, 'settled' => false])->get();
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $unsettled_loans = $branch->loans()->whereHas('customer', function (Builder $builder) use ($activeBranches) {
                $builder->whereIn('branch_id', $activeBranches);
            })->where(['disbursed' => true, 'settled' => false])->get();
        }
        $lo = array();
        foreach ($unsettled_loans as $lns) {
            $last_payment_date = $lns->last_payment_date;
            $product = Product::find($lns->product_id);
            if ($last_payment_date != null) {
                $days_unpaid = Carbon::parse($last_payment_date)->diffInDays(Carbon::now());
            } else {
                $days_unpaid = '--';
            }
            if ($last_payment_date != null && $last_payment_date < Carbon::now()->subDays(180)) {
                array_push($lo, array(
                    'id' => $lns->id,
                    'loan_account' => $lns->loan_account,
                    'loan_amount' => $lns->loan_amount,
                    'product_id' => $lns->product_id,
                    'customer_id' => $lns->customer_id,
                    'date_created' => $lns->date_created,
                    'end_date' => $lns->end_date,
                    'approved' => $lns->approved,
                    'approved_date' => $lns->approved_date,
                    'disbursed' => $lns->disbursed,
                    'disbursement_date' => $lns->disbursement_date,
                    'created_at' => $lns->created_at,
                    'updated_at' => $lns->updated_at,
                    'purpose' => $lns->purpose,
                    'settled' => $lns->settled,
                    'rolled_over' => $lns->rolled_over,
                    'approved_by' => $lns->approved_by,
                    'disbursed_by' => $lns->disbursed_by,
                    'fname' => $lns->customer->fname,
                    'lname' => $lns->customer->lname,
                    'phone' => $lns->customer->phone,
                    'product_name' => $product->product_name,
                    'installments' => $product->installments,
                    'interest' => $product->interest,
                    'last_payment_date' => $last_payment_date,
                    'days_unpaid' => $days_unpaid,
                    'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                ));
            }
        }
        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {
                $Customer = Customer::find($lo['customer_id']);
                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('amount_paid', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo['id'], 'payment_type_id' => 1])->sum('amount');
                return $payments;
            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo['product_id']);
                if ($lo['rolled_over']) {
                    $rollover = Rollover::where('loan_id', $lo['id'])->first();
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100));
                }
                return $total;
            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo['id'], 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo['product_id']);
                if ($lo['rolled_over']) {
                    $rollover = Rollover::where('loan_id', $lo['id'])->first();
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100));
                }
                $balance = $total - $payments;
                return $balance;
            })
            ->addColumn('field_agent', function ($lo) {
                $Customer = Customer::find($lo['customer_id']);
                $user = User::find($Customer->field_agent_id);
                return $user->name;
            })
            ->addColumn('percentage_paid', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo['id'], 'payment_type_id' => 1])->sum('amount');
                $product = Product::find($lo['product_id']);
                if ($lo['rolled_over']) {
                    $rollover = Rollover::where('loan_id', $lo['id'])->first();
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100));
                }
                $percentage_paid1 = ($payments / $total) * 100;
                $percentage_paid = number_format((float)$percentage_paid1, 1, '.', '');
                return $percentage_paid;

            })
            ->make(true);
    }

    //PAR summary
    public function par_summary()
    {
        $this->data['title'] = "PAR Analysis Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "PAR Analysis Report in " . $branch->bname;
        }
        return view('pages.reports.par_summary', $this->data);
    }

    public function par_summary_data(Request $request)
    {
        $lo = DB::table('categories')->get();
        // dd($request->all());


        return Datatables::of($lo)
            ->addcolumn('bname', function () use ($request) {
                if ($request->branch) {
                    $branch = Branch::find($request->branch);

                } else {
                    $branch = Branch::find(Auth::user()->branch_id);

                }
                return $branch->bname;
            })
            ->addcolumn('loan_count', function ($lo) use ($request) {
                $total = 0;
                if ($request->branch) {
                    $loans = $this->getloans($request->branch);
                } else {
                    $loans = $this->getloans(Auth::user()->branch_id);
                }
                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {
                        $total += 1;
                    }
                }
                return $total;
            })
            ->addcolumn('loan_total', function ($lo) use ($request) {
                $total = 0;
                if ($request->branch) {
                    $loans = $this->getloans($request->branch);

                } else {
                    $loans = $this->getloans(Auth::user()->branch_id);

                }
                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {

                        $payments = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 1])->sum('amount');

                        $product = Product::find($loan->product_id);
                        if ($loan->rolled_over) {
                            $rollover = Rollover::where('loan_id', $loan->id)->first();
                            $total2 = $loan->loan_amount + ($loan->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                        } else {
                            $total2 = $loan->loan_amount + ($loan->loan_amount * ($product->interest / 100));


                        }
                        $balance = $total2 - $payments;
                        $total += $balance;
                    }
                }
                return $total;
            })
            ->addcolumn('total_arrears', function ($lo) use ($request) {
                $total = 0;

                if ($request->branch) {
                    $loans = $this->getloans($request->branch);

                } else {
                    $loans = $this->getloans(Auth::user()->branch_id);

                }
                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {
                        $total += Arrear::where('loan_id', $loan->id)->sum('amount');;
                    }
                }

                return $total;
            })
            ->addcolumn('par', function ($lo) use ($request) {
                $tarrears = 0;
                $tbalance = 0;

                if ($request->branch) {
                    $loans = $this->getloans($request->branch);

                } else {
                    $loans = $this->getloans(Auth::user()->branch_id);

                }

                foreach ($loans as $loan) {


                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {
                        $arrears = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->sum('amount');
                        $tarrears += $arrears;

                        $payments = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 1])->sum('amount');

                        $product = Product::find($loan->product_id);
                        if ($loan->rolled_over) {
                            $rollover = Rollover::where('loan_id', $loan->id)->first();
                            $total2 = $loan->loan_amount + ($loan->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                        } else {
                            $total2 = $loan->loan_amount + ($loan->loan_amount * ($product->interest / 100));


                        }
                        $balance = $total2 - $payments;
                        $tbalance += $balance;
                    }
                }
                if ($tbalance != 0) {
                    return number_format(($tarrears / $tbalance) * 100, 2) . '%';

                } else {
                    return 0;

                }

            })
            ->toJson();

    }

    //get loans function
    function getloans($id)
    {
        if (Auth::user()->hasRole('field_agent')) {
            $loans = DB::table('loans')
                ->where('disbursed', true)
                ->whereExists(function ($query) {
                    $query->select("arrears.loan_id")
                        ->from('arrears')
                        ->whereRaw('arrears.loan_id = loans.id')
                        ->whereRaw('arrears.amount != 0');

                })
                ->join('customers', function ($join) use ($id) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        ->where('customers.field_agent_id', '=', $id);
                })
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.id', 'loans.product_id', 'loans.rolled_over', 'loans.loan_amount')
                ->get();
        } else {


            $loans = DB::table('loans')
                ->where('disbursed', true)
                //->get();
                ->whereExists(function ($query) {
                    $query->select("arrears.loan_id")
                        ->from('arrears')
                        ->whereRaw('arrears.loan_id = loans.id')
                        ->whereRaw('arrears.amount != 0');

                })
                ->join('customers', function ($join) use ($id) {
                    $join->on('customers.id', '=', 'loans.customer_id')
                        ->where('customers.branch_id', '=', $id);
                })
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.id', 'loans.product_id', 'loans.rolled_over', 'loans.loan_amount')
                ->get();
            //->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'));


        }

        return $loans;

    }

    function getCredOfficerLoans($id)
    {
        $loans = DB::table('loans')
            ->where('disbursed', true)
            ->whereExists(function ($query) {
                $query->select("arrears.loan_id")
                    ->from('arrears')
                    ->whereRaw('arrears.loan_id = loans.id')
                    ->whereRaw('arrears.amount != 0');
            })
            ->join('customers', function ($join) use ($id) {
                $join->on('customers.id', '=', 'loans.customer_id')
                    ->where('customers.field_agent_id', '=', $id);
            })
            ->join('products', 'products.id', '=', 'loans.product_id')
            ->select('loans.id', 'loans.product_id', 'loans.rolled_over', 'loans.loan_amount')
            ->get();
        return $loans;
    }

    //loan collection per month
    public function loan_collections_per_month(Request $request)
    {
        $months = [
            [1, "JANUARY"], [2, "FEBRUARY"], [3, "MARCH"], [4, "APRIL"], [5, "MAY"], [6, "JUNE"], [7, "JULY"], [8, "AUGUST"], [9, "SEPTEMBER"], [10, "OCTOBER"], [11, "NOVEMBER"], [12, "DECEMBER"]
        ];
        $years = [];
        for ($i = 2019; $i <= now()->format('Y'); $i++) {
            $years[] = $i;
        }
        $years = array_reverse($years);
        $total = 0;
        $mtotal = 0;
        $this->data['dt'] = [];
        $this->data['title'] = "Loan Collection Per Month Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "Loan Collection Per Month Info";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "Loan Collection Per Month in " . $branch->bname;
        }
        foreach ($months as $month) {
            if ($request->year) {
                $this->data['title'] = "Loan Collection Per Month Report year " . $request->year;
                if ($request->branch == 'all') {
                    $current_branch = 'all';
                    $payments = Payment::whereYear('date_payed', $request->year)->whereMonth('date_payed', $month[0])->where('payment_type_id', 1)->sum('amount');
                } else {
                    if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
                        $branch = Branch::find($request->branch);
                    } else {
                        $branch = Branch::find(Auth::user()->branch_id);
                    }

                    //$lns = $branch->payments();
                    $payments = 0;
                    $branch_loan_ids = $branch->loans()->pluck('loans.id')->toArray();
                    $payments += Payment::query()->whereIn('loan_id', $branch_loan_ids)
                        ->whereYear('date_payed', $request->year)
                        ->whereMonth('date_payed', $month[0])
                        ->where('payment_type_id', 1)->sum('amount');
//                    if ($lns->count() != 0) {
//                        foreach ($lns as $ln) {
//                            $payments += $ln->payment()->whereYear('date_payed', $request->year)->whereMonth('date_payed', $month[0])->where('payment_type_id', 1)->sum('amount');
//                        }
//                    }
                }
            } else {
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
                    $payments = Payment::whereYear('date_payed', Carbon::now('Africa/Nairobi'))->whereMonth('date_payed', $month[0])->where('payment_type_id', 1)->sum('amount');
                } else {
                    $branch = Branch::find(Auth::user()->branch_id);
                    $current_year = Carbon::now()->format('Y');
                    //$lns = $branch->payments();
                    $payments = 0;
                    $branch_loan_ids = $branch->loans()->pluck('loans.id')->toArray();
                    $payments += Payment::query()->whereIn('loan_id', $branch_loan_ids)
                        ->whereYear('date_payed', now()->format('Y'))
                        ->whereMonth('date_payed', $month[0])
                        ->where('payment_type_id', 1)->sum('amount');
//                    if ($lns->count() != 0) {
//                        foreach ($lns as $ln) {
//                            $payments += $ln->payment()->whereYear('date_payed', Carbon::now('Africa/Nairobi'))->whereMonth('date_payed', $month[0])->where('payment_type_id', 1)->sum('amount');
//                        }
//                    }
                }
            }
            $total += $payments;
            $mtotal += $payments;
            $selc = [$month[1], number_format($total, 2)];
            $this->data['dt'][] = $selc;
            $total = 0;
        }
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['mtotal'] = $mtotal;
        $this->data['years'] = $years;
        $this->data['current_year'] = $request->year ?? now()->format('Y');
        $this->data['current_branch'] = $request->branch ?? 'all';
        return view('pages.reports.loan_collections_per_month', $this->data);
    }

    //disbursement summary per month
    public function loan_disbursement_permonth(Request $request)
    {
        $this->data['title'] = "Loan Disbursement Per Month Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "Loan Disbursement Per Month Info";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "Loan Disbursement Per Month in" . $branch->bname;
        }
        $years = [];
        for ($i = 2019; $i <= now()->format('Y'); $i++) {
            array_push($years, $i);
        }
        $years = array_reverse($years);
        $months = [
            [1, "JANUARY"], [2, "FEBRUARY"], [3, "MARCH"], [4, "APRIL"], [5, "MAY"], [6, "JUNE"], [7, "JULY"], [8, "AUGUST"], [9, "SEPTEMBER"], [10, "OCTOBER"], [11, "NOVEMBER"], [12, "DECEMBER"]
        ];
        $total = 0;
        $mtotal = 0;
        $this->data['dt'] = [];
        $tloans = 0;
        $loans = 0;
        foreach ($months as $month) {
            if ($request->year) {
                $this->data['title'] = "Loan Disbursement Per Month Report in " . $request->year;
                $current_year = $request->year;
                if ($request->branch == 'all') {
                    $current_branch = 'all';
                    $loans += Loan::where('disbursed', true)->whereYear('disbursement_date', $request->year)->whereMonth('disbursement_date', $month[0])->count();
                    $payments = Payment::whereYear('date_payed', $request->year)->whereMonth('date_payed', $month[0])->where('payment_type_id', 2)->sum('amount');
                } else {
                    if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
                        $branch = Branch::find($request->branch);
                    } else {
                        $branch = Branch::find(Auth::user()->branch_id);
                    }
                    $this->data['sub_title'] = "Loan Disbursement Per Month Info in " . $branch->bname;
                    $current_branch = $branch->id;
                    $loans += $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $request->year)->whereMonth('disbursement_date', $month[0])->count();
                    $lns = $branch->payments();
                    if ($lns->count() != 0) {
                        $payments = 0;
                        foreach ($lns as $ln) {
                            $payments += $ln->payment()->whereYear('date_payed', $request->year)->whereMonth('date_payed', $month[0])->where('payment_type_id', 2)->sum('amount');
                        }
                    } else {
                        $payments = 0;
                    }
                }
            } else {
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
                    $current_year = Carbon::now()->format('Y');
                    $current_branch = 'all';
                    $payments = Payment::whereYear('date_payed', Carbon::now('Africa/Nairobi'))->whereMonth('date_payed', $month[0])->where('payment_type_id', 2)->sum('amount');
                    $loans += Loan::where('disbursed', true)->whereYear('disbursement_date', Carbon::now('Africa/Nairobi'))->whereMonth('disbursement_date', $month[0])->count();
                } else {
                    $branch = Branch::find(Auth::user()->branch_id);
                    $current_year = Carbon::now()->format('Y');
                    $this->data['sub_title'] = "Loan Disbursement Per Month Info in " . $branch->bname;
                    $current_branch = $branch->id;
                    $loans += $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->whereMonth('disbursement_date', $month[0])->count();
                    $lns = $branch->payments();
                    if ($lns->count() != 0) {
                        $payments = 0;
                        foreach ($lns as $ln) {
                            $payments += $ln->payment()->whereYear('date_payed', $current_year)->whereMonth('date_payed', $month[0])->where('payment_type_id', 2)->sum('amount');
                        }
                    } else {
                        $payments = 0;
                    }
                }
            }
            $total += $payments;
            $mtotal += $payments;
            $tloans += $loans;
            $selc = [$month[1], number_format($total, 2), $loans];
            array_push($this->data['dt'], $selc);
            $total = 0;
            $loans = 0;
        }
        //dd($this->data['dt']);
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['mtotal'] = $mtotal;
        $this->data['total_loans'] = $tloans;
        $this->data['years'] = $years;
        $this->data['current_year'] = $current_year;
        $this->data['current_branch'] = $current_branch;
        return view('pages.reports.loan_disbursement_permonth', $this->data);
    }

    //loan officer performance - old
    public function field_agent_performance(Request $request)
    {
        $this->data['title'] = "Field Agent Performance Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "Field Agent Performance Report";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "Field Agent Performance Report in " . $branch->bname;
        }

        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['month'] = Carbon::now()->format('M');

        // dd(User::role('field_agent')->where('status', true)->count());

        return view('pages.reports.loan_officer_performance', $this->data);
    }

    public function field_agent_performance_data()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $users = User::role('field_agent')->where('status', true)->select('id')->get();
            $user_ids = array();
            foreach ($users as $user) {
                array_push($user_ids, $user->id);
            }
            $lo = DB::table('users as Lo')
                ->whereIn('Lo.id', $user_ids)
                // ->join('users as co', function ($join) {
                //     $join->on('Lo.id', '=', 'co.field_agent_id');
                // })
                ->select('Lo.*')
                ->get();
        } elseif (Auth::user()->hasRole('field_agent')) {
            $lo = DB::table('users')
                ->select('users.*')
                ->where('id', Auth::id())
                ->join('users as co', function ($join) {
                    $join->on('Lo.id', '=', 'co.field_agent_id')
                        ->where('co.field_agent_id', \auth()->id());

                })
                ->get();
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $lo = DB::table('users')
                ->select('users.*')
                ->where('id', \auth()->user()->field_agent_id)
                ->get();
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $users = User::role('field_agent')->where('status', true)->where('branch_id', $branch->id)->get();
            $user_ids = array();
            foreach ($users as $user) {
                array_push($user_ids, $user->id);
            }
            $lo = DB::table('users')
                ->select('users.*')
                ->whereIn('id', $user_ids)->get();
        }

        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {
                $branch = Branch::find($lo->branch_id);
                return $branch->bname;
            })
            ->addColumn('pair', function ($lo) {
                // $CO = User::where(['field_agent_id' => $lo->id])->first();
                // if ($CO){
                //     return $lo->name.'/'.$CO->name;
                // }
                // else{
                //     return $lo->name;
                // }
                return $lo->name;
            })
            //avg performance is computed as per the current month
            ->addColumn('avg_performance', function ($lo) {
                $user = User::find($lo->id);
                // $ro_collection_target_sum = RoTarget::where('user_id', $user->id)->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now())->sum('collection_target');
                // $ro_disbursement_target_sum = RoTarget::where('user_id', $user->id)->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now())->sum('collection_target');

                // $ddis = $user->loans()->whereYear('disbursement_date', Carbon::now())->whereMonth('disbursement_date', Carbon::now())->count();
                // if ($ro_disbursement_target_sum != 0) {
                //     $dper = (int)(($ddis / $ro_disbursement_target_sum) * 100);

                // } else {
                //     $dper = 0;

                // }
                // $payments = 0;

                // if ($user->hasRole(['field_agent'])) {
                //     $loans = $user->loans()->where('disbursed', true)->get();
                //     $arr = array();
                //     foreach ($loans as $loan) {
                //         array_push($arr, $loan->id);

                //     }
                //     $instalments = Installment::whereYear('due_date',  Carbon::now())->whereMonth('last_payment_date', Carbon::now())->whereIn('loan_id', $arr)->get();

                //     foreach ($instalments as $instalment) {
                //         $payments += $instalment->amount_paid;


                //     }
                // }
                // if ($ro_collection_target_sum != 0) {
                //     $cper = (int)(($payments / $ro_collection_target_sum) * 100);

                // } else {
                //     $cper = 0;

                // }
                // // dd($dper, $cper);

                // $av = ($dper + $cper) / 2;
                // return $av;
                return '--';

            })
            ->addColumn('action', function ($lo) {
                //  return '<a href="'.route('ro.performance',['id' => encrypt($lo->id)]).'" class="btn btn-xs btn-primary"><i class="feather icon-settings"></i> View</a>';
                return '<div class="btn-group text-center">
                            <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="feather icon-settings"></i> </button>
                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                <li><a href="' . route('credit_officer_performance', ['id' => encrypt($lo->id)]) . '" ><i class="feather icon-eye text-info"></i> View in Detail</a></li>
                            </ul>
                        </div>';
            })
            ->toJson();

    }

    //credit officer performance dashboard - update
    public function loco_performance($id)
    {
        $cred_officer = User::find(decrypt($id));
        // $CO = User::where('field_agent_id', $cred_officer->id)->first();
        // $this->data['title'] = $cred_officer->name ."/".$CO->name. " Performance Report";
        $this->data['title'] = $cred_officer->name ." Performance Report";
        $this->data['sub_title'] = "Detailed credit performance report.";
        $months = [
            /* [0, "All"],*/
            [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];
        $years = [];
        for ($i = 2018; $i <= now()->format('Y'); $i++) {
            $years[] = $i;
        }
        $user = $cred_officer;
        $this->data['months'] = $months;
        $this->data['years'] = $years;
        $this->data['cur_month'] = Carbon::now()->format('m');
        $this->data['user'] = $cred_officer;
        $this->data['id'] = $id;

        return view('pages.reports.LoCo_performance', $this->data);

        if (env('APP_ENV') == "local1") {
            return view('pages.reports.LoCo_performance', $this->data);

        } else {
            return view('pages.reports.credit_officer_performance', $this->data);
        }
    }

    public function credit_officer_performance($id)
    {
        $cred_officer = User::find(decrypt($id));
        $this->data['title'] = $cred_officer->name . " Performance Report";
        $this->data['sub_title'] = "Detailed credit performance report.";
        $months = [
            /* [0, "All"],*/
            [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];
        $user = $cred_officer;
        $due_today_amount = 0;
        $totalAmount = 0;
        $customers = Customer::where('field_agent_id', $user->id)->count();
        //  dd($customers);


        $loans = $user->loans()->where(['settled' => false, 'disbursed' => true])->count();
        $due_today_count = $user->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
            $q->where(['current' => true, 'completed' => false])->whereDate('due_date', Carbon::now());
        })->count();

        $due_today_amount = $user->today_inst_amount();
        $totalAmount = $user->getLoanBalanceAttribute();
        $amount_paid = $user->getMonthTotalPaidAttribute();
        $TotalLoanAmount = $user->getMonthTotalLoanAttribute();


        $pending_approval = $user->loans()->where(['approved' => false, 'disbursed' => false])->count();
        $pending_disbursements = $user->loans()->where(['approved' => true, 'disbursed' => false])->count();

        //arrears
        $loans_w_arrears = $user->loans()->where('settled', false)->whereHas('arrears')->get();
        $l = array();
        foreach ($loans_w_arrears as $lns) {
            $last_payment_date = $lns->last_payment_date;
            if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                array_push($l, $lns);
            }
        }
        $arrears_count = count($l);
        $arrears_total = 0;
        foreach ($l as $t) {
            $arrears_total += $t->total_arrears;
        }

        //rolled over loans - today
        $rolled_over_loans_today = $user->loans()->whereHas('rollover', function (Builder $query) {
            $query->whereDate('rollover_date', '=', Carbon::now());
        })->where(['settled' => false, 'disbursed' => true, 'rolled_over' => true])->get();
        $rolled_over_loans_today_count = $rolled_over_loans_today->count();
        $rolled_over_balance_today = 0;
        foreach ($rolled_over_loans_today as $lo) {
            $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
            $product = Product::find($lo->product_id);
            if ($lo->rolled_over) {
                $rollover = Rollover::where('loan_id', $lo->id)->first();
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            } else {
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
            }
            $rolled_over_balance_today += $total - $payments;
        }

        //rolled over loans - month
        $rolled_over_loans = $user->loans()->whereHas('rollover', function (Builder $query) {
            $query->whereMonth('rollover_date', '=', Carbon::now())
                ->whereYear('rollover_date', '=', Carbon::now());
        })
            ->where(['settled' => false, 'disbursed' => true, 'rolled_over' => true])->get();
        $rolled_over_loans_count = $rolled_over_loans->count();
        $rolled_over_balance = 0;
        foreach ($rolled_over_loans as $lo) {
            $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
            $product = Product::find($lo->product_id);
            if ($lo->rolled_over) {
                $rollover = Rollover::where('loan_id', $lo->id)->first();
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            } else {
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
            }
            $rolled_over_balance += $total - $payments;
        }

        //rolled over loans - year
        $rolled_over_loans_year = $user->loans()->whereHas('rollover', function (Builder $query) {
            $query->whereYear('rollover_date', '=', Carbon::now());
        })
            ->where(['settled' => false, 'disbursed' => true, 'rolled_over' => true])->get();
        $rolled_over_loans_year_count = $rolled_over_loans_year->count();
        $rolled_over_balance_year = 0;
        foreach ($rolled_over_loans_year as $lo) {
            $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
            $product = Product::find($lo->product_id);
            if ($lo->rolled_over) {
                $rollover = Rollover::where('loan_id', $lo->id)->first();
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            } else {
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
            }
            $rolled_over_balance_year += $total - $payments;
        }

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
        //repayment rate
        if ($due > 0) {
            $repayment_rate = ($paid / $due) * 100;
        } else {
            $repayment_rate = 0;
        }

        $av = '--';

        //disbursed loans
        $disbursed = $user->loans()->where('disbursed', true)->get();
        $disbTotalAmount = 0;
        $disbPaidAmount = 0;
        foreach ($disbursed as $disb) {
            $disbTotalAmount += $disb->getTotalAttribute();
            $disbPaidAmount += $disb->getAmountPaidAttribute();
        }
        if (count($disbursed) > 0) {
            $loanSize = $disbTotalAmount / count($disbursed);
        } else {
            $loanSize = 0;
        }

        $disbursedMonth = $user->loans()->where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get();
        $disbTotalAmountMonth = 0;
        $disbPaidAmountMonth = 0;
        foreach ($disbursedMonth as $disb) {
            $disbTotalAmountMonth += $disb->getTotalAttribute();
            $disbPaidAmountMonth += $disb->getAmountPaidAttribute();
        }
        if (count($disbursedMonth) > 0) {
            $loanSizeMonth = $disbTotalAmountMonth / count($disbursedMonth);
        } else {
            $loanSizeMonth = 0;
        }

        $interest_data = self::co_and_manager_interest_figures($cred_officer, Carbon::now()->format('m'));
        $this->data['total_interest'] = $interest_data['total_interest'];
        $this->data['interest_paid'] = $interest_data['total_paid_interest'];
        $this->data['loanSize'] = $loanSize;
        $this->data['disbCount'] = count($disbursed);
        $this->data['disbTotalAmount'] = $disbTotalAmount;
        $this->data['loanSizeMonth'] = $loanSizeMonth;
        $this->data['disbCountMonth'] = count($disbursedMonth);
        $this->data['disbTotalAmountMonth'] = $disbTotalAmountMonth;
        $this->data['rolled_over_loans_count_today'] = $rolled_over_loans_today_count;
        $this->data['rolled_over_loans_count'] = $rolled_over_loans_count;
        $this->data['rolled_over_loans_count_year'] = $rolled_over_loans_year_count;
        $this->data['rolled_over_balance'] = $rolled_over_balance;
        $this->data['rolled_over_balance_today'] = $rolled_over_balance_today;
        $this->data['rolled_over_balance_year'] = $rolled_over_balance_year;
        $this->data['non_performing_balance'] = $non_performing_balance;
        $this->data['non_performing_count'] = $non_performing_count;
        $this->data['amount_paid'] = $amount_paid;
        $this->data['TotalLoanAmount'] = $TotalLoanAmount;

        $this->data['due'] = $due;
        $this->data['paid'] = $paid;
        $this->data['repayment_rate'] = number_format($repayment_rate, 2);

        $this->data['customers'] = $customers;
        $this->data['av'] = $av;

        $this->data['loans'] = $loans;
        $this->data['totalAmount'] = $totalAmount;
        $this->data['due_today_count'] = $due_today_count;
        $this->data['due_today_amount'] = $due_today_amount;
        $this->data['pending_approval'] = $pending_approval;
        $this->data['pending_disbursements'] = $pending_disbursements;
        $this->data['arrears_count'] = $arrears_count;
        $this->data['arrears_total'] = $arrears_total;

        $years = [];
        for ($i = 2018; $i <= now()->format('Y'); $i++) {
            $years[] = $i;
        }

        $this->data['months'] = $months;
        $this->data['years'] = $years;
        $this->data['cur_month'] = Carbon::now()->format('m');
        $this->data['user'] = $cred_officer;

        return view('pages.reports.credit_officer_performance', $this->data);

    }

    public function credit_officer_monthly_collection_overview($id)
    {
        $user = User::query()->find(decrypt($id));
        if ($user) {
            $months = [
                [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
            ];
            $years = [];
            for ($i = 2018; $i <= now()->format('Y'); $i++) {
                $years[] = $i;
            }
            $this->data['title'] = "Credit Officer Monthly Collection Overview";
            $this->data['sub_title'] = "Customers under $user->name";
            $this->data['months'] = $months;
            $this->data['years'] = $years;
            $this->data['user'] = $user;
            $this->data['cur_month'] = Carbon::now()->format('m');
            return view('pages.reports.credit_officer_monthly_collection_overview', $this->data);
        } else {
            return Redirect::back()->with('error', 'User not Found');
        }
    }

    public function credit_officer_monthly_collection_overview_data($id, Request $request)
    {
        $user = User::find($id);
        $month = $request->get('collection_month');
        $year = $request->get('collection_year');
        if ($user->hasRole('field_agent')) {
            if ($month and $year) {
                $loans = $user->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', $year)->get();
            } else {
                $loans = $user->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->get();
            }
        } else {
            $branch = Branch::find($user->branch_id);
            if ($month and $year) {
                $loans = $branch->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', $year)->get();
            } else {
                $loans = $branch->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->get();
            }
        }

        $customerData = array();

        if ($loans) {
            foreach ($loans as $loan) {
                $customer = $loan->customer()->first();
                $balance = $loan->balance;
                $disbursed_amount = $loan->total;
                $interest = $disbursed_amount - $loan->loan_amount;
                $info = [
                    'id' => $loan->id,
                    'customer_name' => $customer->fname . ' ' . $customer->lname . ' (' . $customer->phone . ')',
                    'loan_balance' => 'Ksh. ' . number_format($balance),
                    'disbursed_amount' => 'Ksh. ' . number_format($disbursed_amount),
                    'loan_amount' => 'Ksh. ' . number_format($loan->loan_amount),
                    'interest' => 'Ksh. ' . number_format($interest),
                ];
                array_push($customerData, $info);
            }
        }
        return DataTables::of($customerData)->toJson();
    }

    public function field_agents_collection_report(Request $request)
    {
        $lf = $request->lf;
        $branch = $request->branch;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

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

        if (($start_date && $start_date != '') || ($end_date && $end_date != '')) {
            return Datatables::of($field_agents)
                ->addColumn('field_agent', function ($field_agent) {
                    return $field_agent->name.' ('.$field_agent->phone.')';
                })
                ->addColumn('collected', function ($field_agent) use ($start_date, $end_date) {
                    $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
                    $payments = Payment::where('payment_type_id', 1)->whereIn('loan_id', $loan_ids)
                                        ->when($start_date && $start_date != '', function ($query) use ($start_date) {
                                            $query->whereDate('created_at', '>=', Carbon::parse($start_date));
                                        })
                                        ->when($end_date && $end_date != '', function ($query) use ($end_date) {
                                            $query->whereDate('created_at', '<=', Carbon::parse($end_date));
                                        })
                                        ->sum('amount');

                    return $payments;
                })
                ->addColumn('target', function ($field_agent) use ($start_date, $end_date) {
                    $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
                    $target = Installment::whereIn('loan_id', $loan_ids)
                                            ->when($start_date && $start_date != '', function ($query) use ($start_date) {
                                                $query->whereDate('created_at', '>=', Carbon::parse($start_date));
                                            })
                                            ->when($end_date && $end_date != '', function ($query) use ($end_date) {
                                                $query->whereDate('created_at', '<=', Carbon::parse($end_date));
                                            })
                                            ->sum('total');

                    return $target;
                })
                ->addColumn('performance', function ($field_agent) use ($start_date, $end_date) {
                    $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
                    $payments = Payment::where('payment_type_id', 1)
                                        ->whereIn('loan_id', $loan_ids)
                                        ->when($start_date && $start_date != '', function ($query) use ($start_date) {
                                            $query->whereDate('created_at', '>=', Carbon::parse($start_date));
                                        })
                                        ->when($end_date && $end_date != '', function ($query) use ($end_date) {
                                            $query->whereDate('created_at', '<=', Carbon::parse($end_date));
                                        })
                                        ->sum('amount');

                    $target = Installment::whereIn('loan_id', $loan_ids)
                                        ->when($start_date && $start_date != '', function ($query) use ($start_date) {
                                            $query->whereDate('created_at', '>=', Carbon::parse($start_date));
                                        })
                                        ->when($end_date && $end_date != '', function ($query) use ($end_date) {
                                            $query->whereDate('created_at', '<=', Carbon::parse($end_date));
                                        })
                                        ->sum('total');

                    $performance = $target > 0 ? round(($payments / $target) * 100) : 0;

                    return $performance.'%';
                })
                ->toJson();
        }

        return Datatables::of($field_agents)
            ->addColumn('field_agent', function ($field_agent) {
                return $field_agent->name.' ('.$field_agent->phone.')';
            })
            ->addColumn('collected', function ($field_agent) {
                $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
                $payments = Payment::where('payment_type_id', 1)->whereIn('loan_id', $loan_ids)->whereDate('created_at', now())->sum('amount');

                return $payments;
            })
            ->addColumn('target', function ($field_agent) {
                $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
                $target = Installment::whereIn('loan_id', $loan_ids)->whereDate('due_date', now())->sum('total');

                return $target;
            })
            ->addColumn('performance', function ($field_agent) {
                $loan_ids = $field_agent->loans->where('disbursed', true)->where('settled', false)->pluck('id');
                $payments = Payment::where('payment_type_id', 1)->whereIn('loan_id', $loan_ids)->whereDate('created_at', now())->sum('amount');
                $target = Installment::whereIn('loan_id', $loan_ids)->whereDate('due_date', now())->sum('total');
                $performance = $target > 0 ? round(($payments / $target) * 100) : 0;

                return $performance.'%';
            })
            ->toJson();
    }

    public function co_and_manager_interest_figures($user, $month)
    {
        if ($user->hasRole('field_agent')) {
            if ($month) {
                $loans = $user->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', Carbon::now())->get();
            } else {
                $loans = $user->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->get();
            }
        } else {
            $branch = Branch::find($user->branch_id);
            if ($month) {
                $loans = $branch->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', Carbon::now())->get();
            } else {
                $loans = $branch->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->get();
            }
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
            'total_principle' => $total_principle,
            'total_loan_amount' => $total_loan_amount,
            'total_paid_interest' => $total_paid_interest,
            'total_interest' => $total_interest
        ];
    }

    public function par_summary_data_CO($id)
    {
        $lo = DB::table('categories')->get();
        $user = User::find(decrypt($id));
        return Datatables::of($lo)
            ->addcolumn('loan_count', function ($lo) use ($user) {
                $total = 0;
                $loans = $this->getCredOfficerLoans($user->id);

                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    //  dd($loan->id);
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {
                        $total += 1;
                    }
                }
                return $total;
            })
            ->addcolumn('loan_total', function ($lo) use ($user) {
                $total = 0;
                $loans = $this->getCredOfficerLoans($user->id);

                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {

                        $payments = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 1])->sum('amount');

                        $product = Product::find($loan->product_id);
                        if ($loan->rolled_over) {
                            $rollover = Rollover::where('loan_id', $loan->id)->first();
                            $total2 = $loan->loan_amount + ($loan->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                        } else {
                            $total2 = $loan->loan_amount + ($loan->loan_amount * ($product->interest / 100));


                        }
                        $balance = $total2 - $payments;
                        $total += $balance;
                    }
                }


                return $total;
            })
            ->addcolumn('total_arrears', function ($lo) use ($user) {
                $total = 0;
                $loans = $this->getCredOfficerLoans($user->id);
                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {
                        $total += Arrear::where('loan_id', $loan->id)->sum('amount');;
                    }
                }

                return $total;
            })
            ->addcolumn('par', function ($lo) use ($user) {
                $tarrears = 0;
                $tbalance = 0;
                $loans = $this->getCredOfficerLoans($user->id);

                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {
                        $arrears = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->sum('amount');
                        $tarrears += $arrears;

                        $payments = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 1])->sum('amount');

                        $product = Product::find($loan->product_id);
                        if ($loan->rolled_over) {
                            $rollover = Rollover::where('loan_id', $loan->id)->first();
                            $total2 = $loan->loan_amount + ($loan->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                        } else {
                            $total2 = $loan->loan_amount + ($loan->loan_amount * ($product->interest / 100));
                        }
                        $balance = $total2 - $payments;
                        $tbalance += $balance;
                    }
                }
                if ($tbalance != 0) {
                    return number_format(($tarrears / $tbalance) * 100, 2) . '%';

                } else {
                    return 0;

                }

            })
            ->toJson();

    }

    public function monthly_collection_performance_data($id, Request $request)
    {
//        Log::info($request->collection_month);
        $user = User::find($id);
        $month = $request->get('collection_month');
        $year = $request->get('collection_year');
        if ($user->hasRole('field_agent')) {
            if ($month and $year) {
                $loans = $user->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', $year)->get();
                $complete_loans = $user->loans()->where(['disbursed' => true, 'settled' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', $year)->count();
                $incomplete_loans = $user->loans()->where(['disbursed' => true, 'settled' => false])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', $year)->count();
            } else {
                $loans = $user->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->get();
                $complete_loans = $user->loans()->where(['disbursed' => true, 'settled' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->count();
                $incomplete_loans = $user->loans()->where(['disbursed' => true, 'settled' => false])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->count();
            }
        } else {
            $branch = Branch::find($user->branch_id);
            if ($month and $year) {
                $loans = $branch->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', $year)->get();
                $complete_loans = $branch->loans()->where(['disbursed' => true, 'settled' => true])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', $year)->count();
                $incomplete_loans = $branch->loans()->where(['disbursed' => true, 'settled' => false])->whereMonth('end_date', '=', $month)->whereYear('end_date', '=', $year)->count();
            } else {
                $loans = $branch->loans()->where(['disbursed' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->get();
                $complete_loans = $branch->loans()->where(['disbursed' => true, 'settled' => true])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->count();
                $incomplete_loans = $branch->loans()->where(['disbursed' => true, 'settled' => false])->whereMonth('end_date', '=', Carbon::now())->whereYear('end_date', '=', Carbon::now())->count();
            }
        }

        $customerData = array();
        $totalBalance = 0;
        $totalAmount = 0;
        if ($loans) {
            foreach ($loans as $loan) {
                $customer = $loan->customer()->first();
                $balance = $loan->balance;
                $amount = $loan->total;
                $totalBalance += $balance;
                $totalAmount += $amount;
                $info = [
                    'contact' => $customer->fname . ' ' . $customer->lname . ' (' . $customer->phone . ')',
                    'balance' => 'Ksh. ' . number_format($balance),
                    'amount' => 'Ksh. ' . number_format($amount)
                ];
                array_push($customerData, $info);
            }
        }
        $perc = 0;
        if ($complete_loans + $incomplete_loans > 0) {
            $perc = ($complete_loans / ($complete_loans + $incomplete_loans)) * 100;
        }
        $collection = collect([
            [
                'customer_data' => $customerData,
                'total_balance' => 'Ksh. ' . number_format($totalBalance),
                'total_amount' => 'Ksh. ' . number_format($totalAmount),
                'loans' => count($loans),
                'loans_due' => $incomplete_loans,
                'loans_complete' => $complete_loans,
                'percentage' => number_format($perc, 2)]
        ]);
        return DataTables::of($collection)->toJson();
    }

    public function ro_performance($id)
    {
        /* $loans = User::find(decrypt($id))->loans()->get();
         $ro_target = RoTarget::where('user_id', decrypt($id))->get();
         dd($loans);*/
        $months = [
            /* [0, "All"],*/
            [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];

        $user = User::find(decrypt($id));

        //dd($user);
        $this->data['title'] = "Credit Officer Performance  Report";
        $this->data['sub_title'] = $user->name;
        $this->data['user'] = $user;
        $this->data['months'] = $months;
        $this->data['cur_month'] = Carbon::now()->format('m');
        //  dd($this->data['cur_month']);


        return view('pages.reports.performance', $this->data);

    }

    public function ro_performance_data(Request $request, $id)
    {
        if ($request->month) {
            $ro = RoTarget::where('user_id', decrypt($id))->whereYear('date', Carbon::now())->whereMonth('date', $request->month);
        } else {
            $ro = RoTarget::where('user_id', decrypt($id))->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now());
        }
        //dd($id);
        return DataTables::of($ro)
            ->editColumn('id', function ($ro) {
                return '-';

            })
            ->editColumn('collection_target', function ($ro) {
                return $ro->collection_target;
            })
            ->editColumn('actual_disbursement', function ($ro) {
                $user = User::find($ro->user_id);
                $date = Carbon::parse($ro->date);
                return $user->loans()->where('disbursed', true)->whereDate('disbursement_date', $date)/*->whereYear('disbursement_date', $date)*/ ->count();
            })
            ->editColumn('actual_disbursement_amount', function ($ro) {
                $user = User::find($ro->user_id);
                $date = Carbon::parse($ro->date);
                return $user->loans()->where('disbursed', true)->whereDate('disbursement_date', $date)->whereYear('disbursement_date', $date)->sum('loan_amount');
            })
            ->editColumn('disbursement_achieved', function ($ro) {
                $date = Carbon::parse($ro->date);
                $user = User::find($ro->user_id);
                $dis = $user->loans()->where('disbursed', true)->whereDate('disbursement_date', $date)->whereYear('disbursement_date', $date)->sum('loan_amount');
                if ($ro->disbursement_target_amount != 0) {
                    $per = (int)(($dis / $ro->disbursement_target_amount) * 100);
                } else {
                    $per = 0;
                }
                return $per;
            })
            ->editColumn('actual_collection', function ($ro) {
                /* $user = User::find($ro->user_id);
                 $date = Carbon::parse($ro->date);
                 $dis = $user->getTotalCollectionsAttribute($date);
                 return $dis;*/
                $user = User::find($ro->user_id);
                if ($user->hasRole(['field_agent'])) {
                    $loans = $user->loans()->where('disbursed', true)->get();
                    $arr = array();
                    foreach ($loans as $loan) {
                        array_push($arr, $loan->id);
                    }
                    $instalments = Installment::whereDate('due_date', $ro->date)->whereDate('last_payment_date', $ro->date)->whereIn('loan_id', $arr)->get();
                    $payments = 0;
                    foreach ($instalments as $instalment) {
                        $payments += $instalment->amount_paid;
                    }
                    return $payments;
                } else {
                    return 0;
                }
            })
            ->editColumn('collection_achieved', function ($ro) {
                $user = User::find($ro->user_id);
                $payments = 0;
                if ($user->hasRole(['field_agent'])) {
                    $loans = $user->loans()->where('disbursed', true)->get();
                    $arr = array();
                    foreach ($loans as $loan) {
                        array_push($arr, $loan->id);
                    }
                    $instalments = Installment::whereDate('due_date', $ro->date)->whereDate('last_payment_date', $ro->date)->whereIn('loan_id', $arr)->get();
                    foreach ($instalments as $instalment) {
                        $payments += $instalment->amount_paid;
                    }
                }
                if ($ro->collection_target != 0) {
                    $per = (int)(($payments / $ro->collection_target) * 100);
                } else {
                    $per = 0;
                }
                return $per;
            })
            ->editColumn('average_performance', function ($ro) {
                $user = User::find($ro->user_id);
                $date = Carbon::parse($ro->date);
                $ddis = $user->loans()->whereDate('disbursement_date', $date)->sum('loan_amount');

                if ($ro->disbursement_target_amount != 0) {
                    $dper = (int)(($ddis / $ro->disbursement_target_amount) * 100);

                } else {
                    $dper = 0;

                }
                $payments = 0;

                if ($user->hasRole(['field_agent'])) {
                    $loans = $user->loans()->where('disbursed', true)->get();
                    $arr = array();
                    foreach ($loans as $loan) {
                        array_push($arr, $loan->id);

                    }
                    $instalments = Installment::whereDate('due_date', $ro->date)->whereDate('last_payment_date', $ro->date)->whereIn('loan_id', $arr)->get();

                    foreach ($instalments as $instalment) {
                        $payments += $instalment->amount_paid;


                    }
                }
                if ($ro->collection_target != 0) {
                    $cper = (int)(($payments / $ro->collection_target) * 100);

                } else {
                    $cper = 0;

                }
                // dd($dper, $cper);

                //customer onbording

                $customers = Customer::where(['field_agent_id' => $ro->user_id])->whereDate('created_at', $ro->date)->count();
                if ($ro->customer_target != 0) {
                    $Onper = (int)(($customers / $ro->customer_target) * 100);
                } else {
                    $Onper = 0;
                }

                $av = ($dper + $cper + $Onper) / 3;
               // dd($dper, $cper, $Onper);
                return number_format($av);


            })
            ->editColumn('disbursement_achieved', function ($ro) {
                $date = Carbon::parse($ro->date);
                $user = User::find($ro->user_id);
                $dis = $user->loans()->where('disbursed', true)->whereDate('disbursement_date', $date)->whereYear('disbursement_date', $date)->sum('loan_amount');
                if ($ro->disbursement_target_amount != 0) {
                    $per = (int)(($dis / $ro->disbursement_target_amount) * 100);
                } else {
                    $per = 0;
                }
                return $per;
            })
            ->addColumn('customer_enrolled', function ($ro) {
                $date = Carbon::parse($ro->date);

                $customers = Customer::where(['field_agent_id' => $ro->user_id])->whereDate('created_at', $date)->count();
                return $customers;
            })
            ->addColumn('customer_target_achieved', function ($ro) {
                $date = Carbon::parse($ro->date);

                $customers = Customer::where(['field_agent_id' => $ro->user_id])->whereDate('created_at', $date)->count();
                if ($ro->customer_target != 0) {
                    $per = (int)(($customers / $ro->customer_target) * 100);
                } else {
                    $per = 0;
                }
                return $per;

            })
            ->toJson();

    }

    public function co_income_data(Request $request, $id)
    {
//        Log::info($request->all());
        $cred_officer = User::find($id);
        $branch = Branch::find($cred_officer->branch_id);
        $loan_processing_fee = 0;
        $YTD_loan_processing_fee = 0;
        $loan_interest = 0;
        $YTD_loan_interest = 0;
        $total_loan_interest = 0;
        $YTD_total_loan_interest = 0;
        $rollover_interest = 0;
        $YTD_rollover_interest = 0;
        $total_rollover_interest = 0;
        $YTD_total_rollover_interest = 0;
        $YTD_joining_fee = 0;
        $joining_fee = 0;
        $data = [];
        $products = Product::all();
        $user = User::find($id);

        $month = $request->get('income_month');
        $year = Carbon::now()->format('Y');
        if (!$month) {
            $month = Carbon::now()->format('m');
        }
        foreach ($products as $product) {
            if ($user->hasRole('field_agent')) {
                $los = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                    $build->where('field_agent_id', $cred_officer->id);
                })->where('product_id', $product->id)->get();
            } else {
                $los = $branch->loans()->where('product_id', $product->id)->get();
            }

            //dd($los);
            foreach ($los as $lo) {
                $loan_interest += $lo->paidInterest($year, $month);
                $YTD_loan_interest += $lo->paidInterest($year, null);
                $total_loan_interest += $lo->paidInterest($year, $month);
                $YTD_total_loan_interest += $lo->paidInterest($year, null);
                $rollover_interest += $lo->paidRolloverInterest($year, $month);
                $YTD_rollover_interest += $lo->paidRolloverInterest($year, null);
                $total_rollover_interest += $lo->paidRolloverInterest($year, $month);
                $YTD_total_rollover_interest += $lo->paidRolloverInterest($year, null);
            }
            //  dd($loan_interest);
            $selc = ["id" => $product->id, "income_group" => $product->product_name, "month" => number_format($loan_interest, 2), "year" => number_format($YTD_loan_interest, 2)];
            // dd($selc); exit();
            array_push($data, $selc);
            $loan_interest = 0;
            // $rollover_interest = 0;
            $YTD_loan_interest = 0;
            //$total_rollover_interest = 0;
        }
        //add Total Loan Interest
        array_push($data, ["id" => count($products) + 1, "income_group" => "Total Loan Interest", "month" => number_format($total_loan_interest, 2), "year" => number_format($YTD_total_loan_interest, 2)]);

        //add loan processing fee
        //check number of disbursed loans in that month
        if ($user->hasRole('field_agent')) {
            $month_disbursed_loans = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                $build->where('field_agent_id', $cred_officer->id);
            })->where('disbursed', true)->whereYear('disbursement_date', $year)->whereMonth('disbursement_date', $month)->count();
        } else {
            $month_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $year)->whereMonth('disbursement_date', $month)->count();
        }
        //check number of disbursed loans so far in that year
        if ($user->hasRole('field_agent')) {
            $year_disbursed_loans = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                $build->where('field_agent_id', $cred_officer->id);
            })->where('disbursed', true)->whereYear('disbursement_date', $year)->count();
        } else {
            $year_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $year)->count();
        }

        if ($year == 2019) {
            $loan_processing_amount = 400;
            $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
            $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
        } else {
            $setting = Setting::first();
            $loan_processing_amount = $setting->loan_processing_fee;
            $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
            $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
        }
        array_push($data, ["id" => count($products) + 2, "income_group" => "Total Loan Processing Fee", "month" => $loan_processing_fee, "year" => $YTD_loan_processing_fee]);

        //add joining fees
        if ($user->hasRole('field_agent')) {
            $month_new_customers = Customer::where('branch_id', $branch->id)->where('field_agent_id', $cred_officer->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            $year_new_customers = Customer::where('branch_id', $branch->id)->where('field_agent_id', $cred_officer->id)->whereYear('created_at', $year)->get();
        } else {
            $month_new_customers = Customer::where('branch_id', $branch->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            $year_new_customers = Customer::where('branch_id', $branch->id)->whereYear('created_at', $year)->get();
        }
        $joining_fee = $month_new_customers->count() * 250;
        $YTD_joining_fee = $year_new_customers->count() * 250;
        array_push($data, ["id" => count($products) + 3, "income_group" => "Total Commission", "month" => $joining_fee, "year" => $YTD_joining_fee]);

        //rollover fees
        array_push($data, ["id" => count($products) + 4, "income_group" => "Total Rollover Fees", "month" => number_format($total_rollover_interest, 2), "year" => number_format($YTD_rollover_interest, 2)]);

        //total income
        $total_income = $joining_fee + $loan_processing_fee + $total_loan_interest + $total_rollover_interest;
        $YTD_Total_income = $YTD_joining_fee + $YTD_loan_processing_fee + $YTD_total_loan_interest + $YTD_total_rollover_interest;
        array_push($data, ["id" => count($products) + 5, "income_group" => "Total Income", "month" => number_format($total_income, 2), "year" => number_format($YTD_Total_income, 2)]);
        return DataTables::of(collect($data))->toJson();
    }

    public function co_income_data_ajax(Request $request, $id){
        $loan_processing_fee = 0;
        $YTD_loan_processing_fee = 0;


        $loan_interest = 0;
        $YTD_loan_interest = 0;

        $total_loan_interest = 0;
        $YTD_total_loan_interest = 0;

        $rollover_interest = 0;
        $YTD_rollover_interest = 0;

        $total_rollover_interest = 0;
        $YTD_total_rollover_interest = 0;

        $setting = Setting::query()->first();
        $products = Product::all();
        $data = [];





        if ( $request->month) {
            $year = Carbon::now()->format('Y');
            $month = $request->month;
            $current_month = Carbon::create()->month($month)->format('F');
            $current_year = $year;
        } else {
            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');
            $current_month = Carbon::now()->format('F');
            $current_year = Carbon::now()->format('Y');
        }



        $cred_officer = User::find(decrypt($id));
        $branch = Branch::query()->find($cred_officer->branch_id);
        $year_new_customers = Customer::where('branch_id', $branch->id)->whereYear('created_at', $year)->get();
        $YTD_joining_fee = $year_new_customers->count() * 150;
        $month_new_customers = Customer::where('branch_id', $branch->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
        $joining_fee = $month_new_customers->count() * 150;
        //check number of disbursed loans in that month
        $month_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->whereMonth('disbursement_date', $month)->count();
        //check number of disbursed loans so far in that year
        $year_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->count();
        if ($current_year == 2019) {
            $loan_processing_amount = 400;
        } else {
            $loan_processing_amount = $setting->loan_processing_fee;
        }
        $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
        $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;

        foreach ($products as $product) {
            $los = $branch->loans()->where('product_id', '=', $product->id)->get();

            $los_ids = $los->pluck('id')->toArray();

            $interest_month_year = Installment::query()
                // ->where('completed', '=', true)
                ->whereIn('loan_id', $los_ids)
                ->whereYear('interest_payment_date', $year)
                ->whereMonth('interest_payment_date', $month)
                ->whereColumn('amount_paid', '>=', 'interest')
                ->sum('interest');

            $interest_year = Installment::query()
                //->where('completed', '=', true)
                ->whereIn('loan_id', $los_ids)
                ->whereYear('interest_payment_date', $year)
                ->whereColumn('amount_paid', '>=', 'interest')
                ->sum('interest');

            $loan_interest += $interest_month_year;

            $YTD_loan_interest += $interest_year;

            $total_loan_interest += $interest_month_year;

            $YTD_total_loan_interest += $interest_year;

            $rollover_interest += 0;

            $YTD_rollover_interest += 0;

            $total_rollover_interest += 0;

            $YTD_total_rollover_interest += 0;

            //$selc = [$product->product_name, number_format($loan_interest, 2), number_format($YTD_loan_interest, 2)];
            $selc = ['id'=>$product->id, $product->id.'_monthly' => number_format($loan_interest), $product->id.'YTD' => number_format($YTD_loan_interest)];

            $this->data['product'][] = $selc;

            array_push($data, $selc);
            $loan_interest = 0;
            // $rollover_interest = 0;
            $YTD_loan_interest = 0;
            //$total_rollover_interest = 0;
        }
        //add Total Loan Interest
        array_push($data, ["id" => "total_loan_interest", "income_group" => "Total Loan Interest", "total_loan_interest_month" => number_format($total_loan_interest, 2), "total_loan_interest_year" => number_format($YTD_total_loan_interest, 2)]);




        //add loan processing fee
        //check number of disbursed loans in that month
        $month_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $year)->whereMonth('disbursement_date', $month)->count();

        //check number of disbursed loans so far in that year
        $year_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $year)->count();


        if ($year == 2019) {
            $loan_processing_amount = 400;
            $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
            $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
        } else {
            $setting = Setting::first();
            $loan_processing_amount = $setting->loan_processing_fee;
            $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
            $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
        }
        array_push($data, ["id" => "total_loan_processing_fee", "income_group" => "Total Loan Processing Fee", "total_loan_processing_fee_month" => $loan_processing_fee, "total_loan_processing_fee_year" => $YTD_loan_processing_fee]);

        //add joining fees
        $month_new_customers = Customer::where('branch_id', $branch->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
        $year_new_customers = Customer::where('branch_id', $branch->id)->whereYear('created_at', $year)->get();

        $joining_fee = $month_new_customers->count() * 150;
        $YTD_joining_fee = $year_new_customers->count() * 150;
        array_push($data, ["id" => "joining_fee", "income_group" => "Total Commission", "joining_fee_month" => $joining_fee, "joining_fee_year" => $YTD_joining_fee]);

        //rollover fees
        array_push($data, ["id" => "rollover_fee", "income_group" => "Total Rollover Fees", "rollover_fee_month" => number_format($total_rollover_interest, 2), "rollover_fee_year" => number_format($YTD_rollover_interest, 2)]);

        //total income
        $total_income = $joining_fee + $loan_processing_fee + $total_loan_interest + $total_rollover_interest;
        $YTD_Total_income = $YTD_joining_fee + $YTD_loan_processing_fee + $YTD_total_loan_interest + $YTD_total_rollover_interest;
        array_push($data, ["id" => "total_income", "income_group" => "Total Income", "total_income_month" => number_format($total_income, 2), "total_income_year" => number_format($YTD_Total_income, 2)]);

        $this->data['sub_title'] = "Income Statement in " . $branch->bname . " All Credit-Officers";
        $this->data['current_branch'] = $branch->id;
        $this->data['current_co'] = 'all';

        return ['data' =>$data, 'current_month' => date('M', mktime(0, 0, 0, $month, 10))];
       // dd($data);

    }


    //add ro target
    public function ro_target_add(Request $request, $id)
    {
        $user = User::find(decrypt($id));
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'disbursement_target_amount' => 'required',
                'customer_target' => 'required',
            ]);
             //dd($request->all());
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            //find if the target of this month is created
            $rs = RoTarget::where(['user_id' => decrypt($id)])->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now())->get();
            if (count($rs) > 0) {
                foreach ($rs as $r) {
                    $r->update([
                        'disbursement_target_amount' => $request->disbursement_target_amount,
                        'customer_target' => $request->customer_target,
                    ]);
                }
                return back()->with('success', 'Successfully updated targets for ' . $user->name);
            } else {
                $ro = RoTarget::create([
                    'disbursement_target_amount' => $request->disbursement_target_amount,
                    'collection_target' => 0,
                    'customer_target' => $request->customer_target,

                    'date' => Carbon::now(),
                    'user_id' => decrypt($id)
                ]);
                return back()->with('success', 'Successfully created targets for ' . $user->name);
            }
        }
        $user = User::find(decrypt($id));
        // $dis = $user->loans()->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->count();
        // $due = $user->installmets_due(Carbon::now());
        // dd($due);
        $ro = RoTarget::where('user_id', $user->id)->orderby('id', 'desc')->first();
        if ($ro) {
            $this->data['disbursement_target_amount'] = $ro->disbursement_target_amount;
            $this->data['customer_target'] = $ro->customer_target;

        }

        $this->data['title'] = "Add RO targets";
        $this->data['sub_title'] = $user->name;
        //$this->data['disbursed'] = $dis;
        // $this->data['due_collections'] = $due;
        $this->data['user'] = $user;


        return view('pages.reports.ro_target_add', $this->data);


    }

    //disbursement summary
    public function disbursed_loans_summary(Request $request)
    {
        $this->data['title'] = "Disbursement Summary Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "Disbursement Summary Info";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "Disbursement Summary in" . $branch->bname;
        }
        $brs = Branch::query()->where('status', '=', true)->get();
        if (Auth::user()->hasrole('investor')) {
            $brs = Branch::where('id', Auth::user()->branch_id)->get();
        }
        $total = 0;
        $mtotal = 0;
        $this->data['dt'] = [];

        foreach ($brs as $br) {
            // dd($br->payments()); exit();
            if ($request->start_date) {
                /*$payments = $br->payments()->where(['payment_type_id' => 2])->whereBetween('date_payed', [$request->start_date, $request->end_date])->get();
                foreach ($payments as $payment){
                    $total += $payment->amount;
                }*/
                //  $loans = $br->payments()/*->where('payment_type_id', 2)->get()*/;
                $loans = $br->loans()->where(['disbursed' => true])->whereBetween('disbursement_date', [$request->start_date, $request->end_date])->get();
                foreach ($loans as $loan) {
                    /*  $payments = $loan->payment()->where(['payment_type_id' => 2])->whereBetween('date_payed', [$request->start_date, $request->end_date])->get();
                      foreach ($payments as $payment) {
                          $total += $payment->amount;
                          $mtotal += $payment->amount;
                      }*/
                    $total += $loan->loan_amount;
                    $mtotal += $loan->loan_amount;
                }
            } else {
                // $loans = $br->payments()/*->where('payment_type_id', 2)->get()*/;
                $loans = $br->loans()->where(['disbursed' => true])->get();
                foreach ($loans as $loan) {
                    /*$payments = $loan->payment()->where('payment_type_id', 2)->get();
                    foreach ($payments as $payment) {
                        $total += $payment->amount;
                        $mtotal += $payment->amount;
                    }*/
                    $total += $loan->loan_amount;
                    $mtotal += $loan->loan_amount;
                }
            }
            //$total += $payments;
            $selc = [$br->bname, number_format($total, 2)];
            // dd($selc); exit();
            array_push($this->data['dt'], $selc);
            $total = 0;
        }
        $this->data['mtotal'] = $mtotal;
        $this->data['start_date'] = $request->start_date ?? '';
        $this->data['end_date'] = $request->end_date ?? '';
        return view('pages.reports.disbursed_loans_summary', $this->data);
    }

    //customer listings
    public function customer_listing()
    {
        $this->data['title'] = "Customer Listing Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Customers";
        } else {
            $this->data['sub_title'] = "List of All Customers in " .
                Auth::user()->branch;
        }
        return view('pages.reports.customer_listing', $this->data);
    }

    public function customer_listing_data()
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        $customers = \App\models\Customer::select("*");
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer')) {
            $customers = $customers->whereIn('branch_id', $activeBranches);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $customers = $customers->where('field_agent_id', Auth::id());
        } elseif (Auth::user()->hasRole('collection_officer')) {
            $customers = $customers->where('field_agent_id', \auth()->user()->field_agent_id);
        } else {
            $customers = $customers->where('branch_id', Auth::user()->branch_id);
        }
        return DT::eloquent($customers)
            ->addColumn('branchName', function (\App\models\Customer $customer) {
                return $customer->branch->bname;
            })
            ->addColumn('loanOfficer', function (\App\models\Customer $customer) {
                return $customer->Officer->name;
            })
            ->addColumn('customerName', function (\App\models\Customer $customer) {
                return $customer->fullName;
            })
            ->addColumn('mobileNumber', function (\App\models\Customer $customer) {
                return $customer->phone;
            })
            ->addColumn('idNo', function (\App\models\Customer $customer) {
                return $customer->id_no;
            })
            ->addColumn('gender', function (\App\models\Customer $customer) {
                return $customer->gender;
            })
            ->addColumn('dateOfBirth', function (\App\models\Customer $customer) {
                $dateTime = \Carbon\Carbon::parse($customer->dob);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('age', function (\App\models\Customer $customer) {

                $dob = \Carbon\Carbon::parse($customer->dob);
                $age = \Carbon\Carbon::now()->diffInYears($dob);
                return $age;
            })
            ->addColumn('maritalStatus', function (\App\models\Customer $customer) {
                return $customer->marital_status;
            })
            ->addColumn('residenceType', function (\App\models\Customer $customer) {
                return $customer->location->residence_type;
            })
            ->addColumn('businessDescription', function (\App\models\Customer $customer) {
                return $customer->is_employed ? "Employed" : "Self Employed";
            })
            ->addColumn('dealingIn', function (\App\models\Customer $customer) {
                return $customer->is_employed ? $customer->employer : $customer->businessType->bname;
            })
            ->addColumn('createdDate', function (\App\models\Customer $customer) {

                $dateTime = \Carbon\Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->toJson();
    }

    //customer account statements
    public function customer_account_statement()
    {
        $this->data['title'] = "Customer Account Statement Report";
        $user = \auth()->user();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Customer Account Statements";
        } elseif ($user->hasRole('field_agent')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $this->data['credit_officers'] = User::role('field_agent')->where(['status' => true, 'id' => $user->id])->get();
        } else {
            $this->data['sub_title'] = "List of All Customer Account Statements in " . $user->branch;
        }
        return view('pages.reports.customer_account_statement', $this->data);
    }

    public function customer_account_statement_data()
    {
        $customers = \App\models\Customer::select("*");
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager')) {
            $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
            $customers = $customers->whereIn('branch_id', $activeBranches);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $customers = $customers->where('field_agent_id', Auth::id());
        } else {
            $customers = $customers->where('branch_id', Auth::user()->branch_id);
        }
        return DT::eloquent($customers)
            ->addColumn('branchName', function (\App\models\Customer $customer) {
                return $customer->branch->bname;
            })
            ->addColumn('loanOfficer', function (\App\models\Customer $customer) {
                return $customer->Officer ? $customer->Officer->name : '';
            })
            ->addColumn('customerName', function (\App\models\Customer $customer) {
                return $customer->fullName;
            })
            ->addColumn('mobileNumber', function (\App\models\Customer $customer) {
                return $customer->phone;
            })
            ->addColumn('idNo', function (\App\models\Customer $customer) {
                return $customer->id_no;
            })
            ->addColumn('createdDate', function (\App\models\Customer $customer) {

                $dateTime = \Carbon\Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('location', function (\App\models\Customer $customer) {
                $location = Customer_location::where('customer_id', $customer->id)->first();

                return Str::title($location->ward).', '.Str::title($location->constituency).', '.Str::title($location->county->cname);
            })
            ->addColumn('referee', function (\App\models\Customer $customer) {
                $customer_referee = DB::table('customer_referee')->where('customer_id', $customer->id)->first();

                if ($customer_referee) {
                    $referee = Referee::find($customer_referee->referee_id);

                    return Str::headline($referee->full_name).' ('.$referee->phone_number.')';
                }
                return '--';
            })
            ->addColumn('action', function (\App\models\Customer $customer) {

                return sprintf(
                    '<a href="%s" type="button" class="sel-btn btn btn-xs btn-primary" ><i class="feather icon-eye text-danger" ></i>
                View</a>',
                    route('customer_account_statement_single', encrypt($customer->id))
                );
            })
            ->rawColumns(['action'])
            ->toJson();

    }

    public function customer_account_statement_single($id, Request $request)
    {
        $customer = Customer::find(decrypt($id));
        $data['custID'] = $id;
        $data['title'] = sprintf("%s - Customer Account Statement Report", $customer->fullNameUpper);
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager')) {
            $data['sub_title'] = sprintf("Customer Account Statement for %s", $customer->fullNameUpper);
        } else {
            $data['sub_title'] = sprintf("Customer Account Statement for %s", $customer->fullNameUpper);
        }
        $cust_loans = $customer->loans()->where('disbursed', '=', true)->get();
        $totalAmount = 0;
        $balance = 0;
        $paidAmount = 0;
        $principalAmount = 0;
        $interestAmount = 0;
        foreach ($cust_loans as $loan) {
            $totalAmount += $loan->getTotalAttribute();
            $paidAmount += $loan->getAmountPaidAttribute();
            $balance += $loan->getBalanceAttribute();
            $principalAmount += $loan->loan_amount;
            if ($loan->rolled_over) {
                $totalInterest = $loan->loan_amount * ($loan->product()->first()->interest / 100) + $loan->rollover()->first()->rollover_interest;
            } else {
                $totalInterest = $loan->loan_amount * ($loan->product()->first()->interest / 100);
            }
            $interestAmount += $totalInterest;
        }

        $registrationFees = $customer->regpayments()->sum('amount');

        $data['customer'] = $customer;
        $data['totalAmount'] = $totalAmount;
        $data['balance'] = $balance;
        $data['paidAmount'] = $paidAmount;
        $data['principalAmount'] = $principalAmount;
        $data['interestAmount'] = $interestAmount;
        $data['registrationFees'] = $registrationFees;

        $data['options']['startDate'] = $request->start_date ? $request->start_date : '2019-04-01';
        $data['options']['endDate'] = $request->end_date ? $request->end_date : Carbon::now()->format('Y-m-d');

        return view('pages.reports.customer_account_statement_single', $data);
    }

    //not in use
    public function customer_account_statement_single_data($id, Request $request)
    {
        $customer = \App\models\Customer::find(decrypt($id));

        $data['title'] = sprintf("%s - Customer Account Statement Report", $customer->fullNameUpper);

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $data['sub_title'] = sprintf("Customer Account Statement for %s", $customer->fullNameUpper);
        } else {
            $data['sub_title'] = sprintf("Customer Account Statement for %s", $customer->fullNameUpper);
        }

        $data['customer'] = $customer;
        $data['currentBalance'] = 0;
        $curr_bal = $data['currentBalance'];
        $data['options']['startDate'] = $request->start_date ? $request->start_date : '2019-04-01'/*Carbon::now()->subDays(28)->format('Y-m-d')*/
        ;
        $data['options']['endDate'] = $request->end_date ? $request->end_date : Carbon::now()->format('Y-m-d');

        //get regpayments.
        $regpayments = $customer->regpayments()
            ->whereDate('created_at', '>=', $data['options']['startDate'])
            ->whereDate('created_at', '<=', $data['options']['endDate'])
            ->get();

        $regpayments->each(function ($regpayment, $key) use (&$data) {

            // registration fees
            $data['statement'][] = collect([
                'date' => $regpayment->date_payed,
                'primary_description' => "Registration Fees",
                'debit' => 150,
                'credit' => 0
            ]);

            // registration deposit
            $data['statement'][] = collect([
                'date' => $regpayment->date_payed,
                'primary_description' => "Registration Deposit",
                'debit' => 0,
                'credit' => $regpayment->amount
            ]);

        });

        // get disbursed loans
        $loans = $customer->loans()
            ->whereDate('disbursement_date', '>=', $data['options']['startDate'])
            ->whereDate('disbursement_date', '<=', $data['options']['endDate'])
            ->get();

        $loans->each(function ($loan, $key) use (&$data) {

            // disbursement to loans loans account
            $data['statement'][] = collect([
                'date' => $loan->disbursement_date,
                'primary_description' => "Loan disbursement",
                'debit' => 0,
                'credit' => $loan->loan_amount
            ]);

        });

        // get loan processing fees
        $fees = Payment::whereIn('loan_id', $loans->pluck('id')->toArray())
            ->where('payment_type_id', 3)
            ->get();

        $fees->each(function ($fee, $key) use (&$data) {

            $data['statement'][] = collect([
                'date' => $fee->date_payed,
                'primary_description' => "Loan Processing Fee",
                'debit' => $fee->amount,
                'credit' => 0
            ]);
            $paid_by = Raw_payment::where('mpesaReceiptNumber', '=', $fee->transaction_id)->first();
            if ($paid_by) {
                $paid_by = $paid_by->phoneNumber;
            } else {
                $loan = Loan::find($fee->loan_id);
                $cust = $loan->customer()->first()->phone;
                $paid_by = $cust;
            }
            // fees for loans
            $data['statement'][] = collect([
                'date' => $fee->date_payed,
                'primary_description' => "M-PESA Deposit -" . $fee->transaction_id . ' paid through ' . $paid_by,
                'debit' => 0,
                'credit' => $fee->amount
            ]);
        });

        // get loan settlements
        $settlements = Payment::whereIn('loan_id', $loans->pluck('id')->toArray())
            ->where('payment_type_id', 1)
            ->get();

        $settlements->each(function ($settlement, $key) use (&$data) {

            $data['statement'][] = collect([
                'date' => $settlement->date_payed,
                'primary_description' => "Loan Repayment",
                'debit' => $settlement->amount,
                'credit' => 0
            ]);
            $paid_by = Raw_payment::where('mpesaReceiptNumber', '=', $settlement->transaction_id)->first();
            if ($paid_by) {
                $paid_by = $paid_by->phoneNumber;
            } else {
                $loan = Loan::find($settlement->loan_id);
                $cust = $loan->customer()->first()->phone;
                $paid_by = $cust;
            }
            $data['statement'][] = collect([
                'date' => $settlement->date_payed,
                'primary_description' => "M-PESA Deposit -" . $settlement->transaction_id . ' paid through ' . $paid_by,
                'debit' => 0,
                'credit' => $settlement->amount
            ]);
        });

        // if statement exists within this timeframe
        if (isset($data['statement'])) {

            $data['statement'] = collect($data['statement'])->sortBy('date');

            $data['currentBalance'] = $data['statement']->last()['credit'] ? $data['statement']->last()['credit'] :
                0 - $data['statement']->last()['credit'];

            $debits = 0;
            $credits = 0;
            $data['statement']->each(function (&$statement, $key) use (&$data, &$debits, &$credits) {
                if ($statement['credit']) {
                    $statement['balance'] = $statement['credit'] + $data['currentBalance'];
                    // update current balance
                    $data['currentBalance'] = $statement['balance'];
                } else {
                    $statement['balance'] = $data['currentBalance'] - $statement['debit'];
                    // update current balance
                    $data['currentBalance'] = $statement['balance'];
                }

            });

            $data['statement'] = collect($data['statement'])->sortByDesc('date');
        }
        return DataTables::of($data['statement'])->toJson();
    }

    public function customer_account_statement_loans_data($id)
    {
        $customer = \App\models\Customer::find(decrypt($id));
        $lo = DB::table('loans')
            ->join('customers', 'customers.id', '=', 'loans.customer_id')
            ->join('products', 'products.id', '=', 'loans.product_id')
            ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
            ->where('customers.id', '=', $customer->id);
        return \Yajra\DataTables\Facades\DataTables::of($lo)
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return "YES";
                }
                return "NO";
            })
            ->editColumn('disbursed', function ($lo) {
                if ($lo->disbursed) {
                    return "YES";
                }
                return "NO";
            })
            ->editColumn('settled', function ($lo) {
                if ($lo->settled) {
                    return "YES";
                }
                return "NO";
            })
            ->addColumn('amount_paid', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                return number_format($payments, 1);
            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    // $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                    $total = $lo->total_amount + $rollover->rollover_interest;
                } else {
                    // $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
                    $total = $lo->total_amount;
                }
                return number_format($total, 2);
            })
            ->addColumn('balance', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    // $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                    $total = $lo->total_amount + $rollover->rollover_interest;
                } else {
                    // $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
                    $total = $lo->total_amount;
                }
                $balance = $total - $payments;
                return number_format($balance, 1);
            })
            ->addColumn('payments', function ($lo) {
                return Payment::where('loan_id', $lo->id)->whereIn('payment_type_id', [1, 3])->orderBy('date_payed', 'ASC')->get()->toArray();
            })
            ->rawColumns(['owner'])
            ->toJson();
    }

    public function customer_account_statement_single_post($id, Request $request)
    {
        $customer = \App\models\Customer::find(decrypt($id));

        $data['title'] = sprintf("%s - Customer Account Statement Report", $customer->fullNameUpper);

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $data['sub_title'] = sprintf("Customer Account Statement for %s", $customer->fullNameUpper);
        } else {
            $data['sub_title'] = "List of All Customer Account Statements in " . Auth::user()->branch;
        }

        $data['customer'] = $customer;
        $data['currentBalance'] = 0;
        $data['options']['startDate'] = $request->start_date ? $request->start_date : '2019-04-01'/*Carbon::now()->subDays(28)->format('Y-m-d')*/
        ;
        $data['options']['endDate'] = $request->end_date ? $request->end_date : Carbon::now()->format('Y-m-d');

        //get regpayments.
        $regpayments = $customer->regpayments()
            ->whereDate('created_at', '>=', $data['options']['startDate'])
            ->whereDate('created_at', '<=', $data['options']['endDate'])
            ->get();

        $regpayments->each(function ($regpayment, $key) use (&$data) {
            // registration fees
            $data['statement'][] = collect([
                'date' => $regpayment->date_payed,
                'primary_description' => "Registration Fees",
                'debit' => 150,
                'credit' => 0
            ]);

            // registration deposit
            $data['statement'][] = collect([
                'date' => $regpayment->date_payed,
                'primary_description' => "Registration Deposit",
                'debit' => 0,
                'credit' => $regpayment->amount
            ]);
        });

        // get disbursed loans
        $loans = $customer->loans()
            ->whereDate('disbursement_date', '>=', $data['options']['startDate'])
            ->whereDate('disbursement_date', '<=', $data['options']['endDate'])
            ->get();

        $loans->each(function ($loan, $key) use (&$data) {

            // disbursement to loans loans account
            $data['statement'][] = collect([
                'date' => $loan->disbursement_date,
                'primary_description' => "Loan disbursement",
                'debit' => 0,
                'credit' => $loan->loan_amount
            ]);

        });

        // get loan processing fees
        $fees = Payment::whereIn('loan_id', $loans->pluck('id')->toArray())
            ->where('payment_type_id', 3)
            ->get();

        $fees->each(function ($fee, $key) use (&$data) {

            $data['statement'][] = collect([
                'date' => $fee->date_payed,
                'primary_description' => "Loan Processing Fee",
                'debit' => $fee->amount,
                'credit' => 0
            ]);

            // fees for loans
            $data['statement'][] = collect([
                'date' => $fee->date_payed,
                'primary_description' => sprintf("M-PESA Deposit %s", $fee->transaction_id),
                'debit' => 0,
                'credit' => $fee->amount
            ]);
        });

        // get loan settlements
        $settlements = Payment::whereIn('loan_id', $loans->pluck('id')->toArray())
            ->where('payment_type_id', 1)
            ->get();

        $settlements->each(function ($settlement, $key) use (&$data) {

            $data['statement'][] = collect([
                'date' => $settlement->date_payed,
                'primary_description' => "Loan Repayment",
                'debit' => $settlement->amount,
                'credit' => 0
            ]);

            $data['statement'][] = collect([
                'date' => $settlement->date_payed,
                'primary_description' => sprintf("M-PESA Deposit %s", $settlement->transaction_id),
                'debit' => 0,
                'credit' => $settlement->amount
            ]);
        });

        // if statement exists within this timeframe
        if (isset($data['statement'])) {

            $data['statement'] = collect($data['statement'])->sortBy('date');

            $data['currentBalance'] = $data['statement']->last()['credit'] ? $data['statement']->last()['credit'] :
                0 - $data['statement']->last()['credit'];


            $data['statement']->each(function (&$statement, $key) use (&$data) {

                if ($statement['credit']) {
                    $statement['balance'] = $statement['credit'] + $data['currentBalance'];
                    // update current balance
                    $data['currentBalance'] = $statement['balance'];
                } else {
                    $statement['balance'] = $data['currentBalance'] - $statement['debit'];
                    // update current balance
                    $data['currentBalance'] = $statement['balance'];
                }

            });

            $data['statement'] = collect($data['statement'])->sortByDesc('date');

            // dd( $data['statement']);
        }

        return view('pages.reports.customer_account_statement_single', $data);
    }

    //income statement
    public function income_statement(Request $request)
    {
        $this->data['title'] = "Income Statement Report";
        $this->data['product'] = [];
        //years
        $yr_start = 2019;
        $yr_end = Carbon::now()->format('Y');
        $yrs = array();
        for ($i = $yr_start; $i <= $yr_end; $i++) {
            array_push($yrs, $i);
        }
        $this->data['yrs'] = $yrs;
        $loan_processing_fee = 0;
        $YTD_loan_processing_fee = 0;
        $loan_interest = 0;
        $YTD_loan_interest = 0;
        $total_loan_interest = 0;
        $YTD_total_loan_interest = 0;

        $rollover_interest = 0;
        $YTD_rollover_interest = 0;

        $total_rollover_interest = 0;
        $YTD_total_rollover_interest = 0;
        $YTD_joining_fee = 0;
        $joining_fee = 0;

//         dd($request->all());

        if ($request->year) {
            $year = $request->year;
            $month = $request->month;
            $branch = Branch::find($request->branch);
            $current_month = date("F", mktime(0, 0, 0, $request->month, 1));
            $current_year = $year;
            $cred_officer = User::find($request->co_id);

        } else {

            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');
            $current_month = Carbon::now()->format('F');
            $current_year = $year;
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
                $branch = Branch::first();
                $cred_officer = User::role('field_agent')->where('branch_id', '=', $branch->id)->first();

            } else {
                $branch = Branch::find(Auth::user()->branch_id);
                $cred_officer = Auth::user();
            }

            $mpesa_balance = new DisbursementController();
            $mbalance = $mpesa_balance->mpesa_balance();
            // dd($mbalance);


        }
        //dd($year, $month, $branch);
        //$branch = Branch::find(Auth::user()->branch_id);

        /************************ADDED BY MUKHAMI************************/
        if ($request->co_id == 'all' || $request->co_id == null) {
            $year_new_customers = Customer::where('branch_id', $branch->id)->whereYear('created_at', $year)->get();
            $YTD_joining_fee = $year_new_customers->count() * 150;
        } else {
            $year_new_customers = Customer::where('branch_id', $branch->id)->where('field_agent_id', $cred_officer->id)->whereYear('created_at', $year)->get();
            $YTD_joining_fee = $year_new_customers->count() * 150;
        }
        /************************END************************/
        // $YTD_joining_fee = $branch->Regpayments()->whereYear('date_payed', $year)->sum('amount');

//        $YTD_joining_fees = $branch->Regpayments()->whereYear('date_payed', $year)->get();
//        foreach ($YTD_joining_fees as $ytds){
//            if ($ytds->amount > 150){
//                $YTD_joining_fee += 150;
//
//            }
//            else{
//                $YTD_joining_fee += $ytds->amount;
//
//            }
//        }


        //   dd($month);
        /************************ADDED BY MUKHAMI************************/
        if ($request->co_id == 'all' || $request->co_id == null) {
            $month_new_customers = Customer::where('branch_id', $branch->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            $joining_fee = $month_new_customers->count() * 150;
        } else {
            $month_new_customers = Customer::where('branch_id', $branch->id)->where('field_agent_id', $cred_officer->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            $joining_fee = $month_new_customers->count() * 150;

        }
        /************************END************************/
        //$joining_fee = $branch->Regpayments()->whereYear('date_payed', $year)->whereMonth('date_payed', $month)->sum('amount');

//        $joining_fees = $branch->Regpayments()->whereYear('date_payed', $year)->whereMonth('date_payed', $month)->get();
//        foreach ($joining_fees as $ytds){
//            if ($ytds->amount > 150){
//                $joining_fee += 150;
//
//            }
//            else{
//                $joining_fee += $ytds->amount;
//
//            }
//        }
        /***************ADDED BY MUKHAMI**********************/
        if ($request->co_id == 'all' || $request->co_id == null) {
            //check number of disbursed loans in that month
            $month_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->whereMonth('disbursement_date', $month)->count();
            //check number of disbursed loans so far in that year
            $year_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->count();
            if ($current_year == 2019) {
                $loan_processing_amount = 400;
                $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
                $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
            } else {
                $setting = Setting::first();
                $loan_processing_amount = $setting->loan_processing_fee;
                $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
                $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
            }
        } else {
            //check number of disbursed loans in that month
            $month_disbursed_loans = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                $build->where('field_agent_id', $cred_officer->id);
            })->where('disbursed', true)->whereYear('disbursement_date', $current_year)->whereMonth('disbursement_date', $month)->count();
            //check number of disbursed loans so far in that year
            $year_disbursed_loans = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                $build->where('field_agent_id', $cred_officer->id);
            })->where('disbursed', true)->whereYear('disbursement_date', $current_year)->count();
            if ($current_year == 2019) {
                $loan_processing_amount = 400;
                $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
                $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
            } else {
                $setting = Setting::first();
                $loan_processing_amount = $setting->loan_processing_fee;
                $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
                $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
            }
        }
        /************END**************/
//        $loans_payments = $branch->payments();
//        foreach ($loans_payments as $loans_payment) {
//            $pay = $loans_payment->payment()->where(['payment_type_id' => 3])->whereYear('date_payed', $year)->whereMonth('date_payed', $month)->sum('amount');
//            $YTD_pay = $loans_payment->payment()->where(['payment_type_id' => 3])->whereYear('date_payed', $year)->sum('amount');
//            $loan_processing_fee += $pay;
//            $YTD_loan_processing_fee += $YTD_pay;
//        }

        $products = Product::all();
        //dd()
        if ($request->co_id == 'all' || $request->co_id == null) {
            foreach ($products as $product) {
                $los = $branch->loans()->where('product_id', $product->id)->get();
                //dd($los);
                foreach ($los as $lo) {
                    $loan_interest += $lo->paidInterest($year, $month);
                    $YTD_loan_interest += $lo->paidInterest($year, null);
                    $total_loan_interest += $lo->paidInterest($year, $month);
                    $YTD_total_loan_interest += $lo->paidInterest($year, null);
                    $rollover_interest += $lo->paidRolloverInterest($year, $month);
                    $YTD_rollover_interest += $lo->paidRolloverInterest($year, null);
                    $total_rollover_interest += $lo->paidRolloverInterest($year, $month);
                    $YTD_total_rollover_interest += $lo->paidRolloverInterest($year, null);


                }
                //  dd($loan_interest);
                $selc = [$product->product_name, number_format($loan_interest, 2), number_format($YTD_loan_interest, 2)];
                // dd($selc); exit();
                array_push($this->data['product'], $selc);

                $loan_interest = 0;
                // $rollover_interest = 0;
                $YTD_loan_interest = 0;
                //$total_rollover_interest = 0;
            }
            $this->data['sub_title'] = "Income Statement in " . $branch->bname . " All Credit-Officers";
        } else {
            foreach ($products as $product) {
                $los = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                    $build->where('field_agent_id', $cred_officer->id);
                })->where('product_id', $product->id)->get();
                //dd($los);
                foreach ($los as $lo) {
                    $loan_interest += $lo->paidInterest($year, $month);
                    $YTD_loan_interest += $lo->paidInterest($year, null);
                    $total_loan_interest += $lo->paidInterest($year, $month);
                    $YTD_total_loan_interest += $lo->paidInterest($year, null);
                    $rollover_interest += $lo->paidRolloverInterest($year, $month);
                    $YTD_rollover_interest += $lo->paidRolloverInterest($year, null);
                    $total_rollover_interest += $lo->paidRolloverInterest($year, $month);
                    $YTD_total_rollover_interest += $lo->paidRolloverInterest($year, null);


                }
                //  dd($loan_interest);
                $selc = [$product->product_name, number_format($loan_interest, 2), number_format($YTD_loan_interest, 2)];
                // dd($selc); exit();
                array_push($this->data['product'], $selc);

                $loan_interest = 0;
                // $rollover_interest = 0;
                $YTD_loan_interest = 0;
                //$total_rollover_interest = 0;
            }
            $this->data['sub_title'] = "Income Statement in for Credit Officer - " . $cred_officer->name . " . Branch - " . $branch->bname;
        }

        $total_income = $joining_fee + $loan_processing_fee + $total_loan_interest + $total_rollover_interest;
        $YTD_Total_income = $YTD_joining_fee + $YTD_loan_processing_fee + $YTD_total_loan_interest + $YTD_total_rollover_interest;
        $months = [
            [0, "All"], [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];

        $msetting = Msetting::first();
        $exp = explode('|', $msetting->Utility_balance);
        $this->data['utility_balance'] = 'KSH ' . number_format($exp[2], 2) . ' as per ' . $msetting->last_updated;

        // dd($exp);
        $this->data['joining_fee'] = $joining_fee;
        $this->data['processing_fee'] = $loan_processing_fee;
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['credit_officers'] = User::role('field_agent')->where('status', true)->get();

        if ($request->co_id == 'all' || $request->co_id == null) {
            $this->data['current_co'] = 'all';
        } else {
            $this->data['current_co'] = $cred_officer->id;
        }

        if (Auth::user()->hasrole('investor')) {
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();
        }
        $this->data['total_loan_interest'] = $total_loan_interest;
        $this->data['total_rollover_fee'] = $total_rollover_interest;
        $this->data['total_income'] = $total_income;
        $this->data['months'] = $months;
        $this->data['cur_month'] = $month;
        $this->data['current_branch'] = $branch->id;
        $this->data['current_month'] = $current_month;
        $this->data['current_year'] = $current_year;
        $this->data['YTD_joining_fee'] = $YTD_joining_fee;
        $this->data['YTD_loan_processing_fee'] = $YTD_loan_processing_fee;
        $this->data['YTD_total_loan_interest'] = $YTD_total_loan_interest;
        $this->data['YTD_total_rollover_interest'] = $YTD_total_rollover_interest;
        $this->data['YTD_Total_income'] = $YTD_Total_income;

        return view('pages.reports.income_statement', $this->data);
    }

    public function income_statement_v2(Request $request)
    {
        $this->data['title'] = "Income Statement Report - Revised";
        $this->data['product'] = [];

        $loan_processing_fee = 0;
        $YTD_loan_processing_fee = 0;

        $loan_interest = 0;
        $YTD_loan_interest = 0;

        $total_loan_interest = 0;
        $YTD_total_loan_interest = 0;

        $rollover_interest = 0;
        $YTD_rollover_interest = 0;

        $total_rollover_interest = 0;
        $YTD_total_rollover_interest = 0;

        $setting = Setting::query()->first();
        $products = Product::all();

        $mpesa_balance = new DisbursementController();

        try {
            $mpesa_balance->mpesa_balance();
        } catch (Exception $e) {

        }

        if ($request->year and $request->month) {
            $year = $request->year;
            $month = $request->month;
            $current_month = Carbon::create()->month($month)->format('F');
            $current_year = $year;
        } else {
            $year = Carbon::now()->format('Y');
            $month = Carbon::now()->format('m');
            $current_month = Carbon::now()->format('F');
            $current_year = Carbon::now()->format('Y');
        }

        //branch specified
        if ($request->co_id == 'all' and $request->branch != 'all') {
            $branch = Branch::query()->find($request->branch);
            $year_new_customers = Customer::where('branch_id', $branch->id)->whereYear('created_at', $year)->get();
            $YTD_joining_fee = $year_new_customers->count() * 150;
            $month_new_customers = Customer::where('branch_id', $branch->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            $joining_fee = $month_new_customers->count() * 150;
            //check number of disbursed loans in that month
            $month_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->whereMonth('disbursement_date', $month)->count();
            //check number of disbursed loans so far in that year
            $year_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->count();
            if ($current_year == 2019) {
                $loan_processing_amount = 400;
            } else {
                $loan_processing_amount = $setting->loan_processing_fee;
            }
            $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
            $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;

            foreach ($products as $product) {
                $los = $branch->loans()->where('product_id', '=', $product->id)->get();

                $los_ids = $los->pluck('id')->toArray();

                $interest_month_year = Installment::query()
                    // ->where('completed', '=', true)
                    ->whereIn('loan_id', $los_ids)
                    ->whereYear('interest_payment_date', $year)
                    ->whereMonth('interest_payment_date', $month)
                    ->whereColumn('amount_paid', '>=', 'interest')
                    ->sum('interest');

                $interest_year = Installment::query()
                    //->where('completed', '=', true)
                    ->whereIn('loan_id', $los_ids)
                    ->whereYear('interest_payment_date', $year)
                    ->whereColumn('amount_paid', '>=', 'interest')
                    ->sum('interest');

                $loan_interest += $interest_month_year;

                $YTD_loan_interest += $interest_year;

                $total_loan_interest += $interest_month_year;

                $YTD_total_loan_interest += $interest_year;

                $rollover_interest += 0;

                $YTD_rollover_interest += 0;

                $total_rollover_interest += 0;

                $YTD_total_rollover_interest += 0;
                // foreach ($los as $lo) {
                //     $loan_interest += $lo->paidInterest($year, $month);
                //     $YTD_loan_interest += $lo->paidInterest($year, null);
                //     $total_loan_interest += $lo->paidInterest($year, $month);
                //     $YTD_total_loan_interest += $lo->paidInterest($year, null);
                //     $rollover_interest += $lo->paidRolloverInterest($year, $month);
                //     $YTD_rollover_interest += $lo->paidRolloverInterest($year, null);
                //     $total_rollover_interest += $lo->paidRolloverInterest($year, $month);
                //     $YTD_total_rollover_interest += $lo->paidRolloverInterest($year, null);
                // }
                $selc = [$product->product_name, number_format($loan_interest, 2), number_format($YTD_loan_interest, 2)];
                $this->data['product'][] = $selc;
                $loan_interest = 0;
                $YTD_loan_interest = 0;
            }
            $this->data['sub_title'] = "Income Statement in " . $branch->bname . " All Credit-Officers";
            $this->data['current_branch'] = $branch->id;
            $this->data['current_co'] = 'all';

        } //credit officer specified
        elseif ($request->co_id and $request->co_id != 'all') {
            $cred_officer = User::role('field_agent')->where('id', '=', $request->co_id)->first();
            $branch = Branch::query()->where('id', '=', $cred_officer->branch_id)->first();
            $year_new_customers = Customer::where('field_agent_id', $cred_officer->id)->whereYear('created_at', $year)->get();
            $YTD_joining_fee = $year_new_customers->count() * 150;
            $month_new_customers = Customer::where('field_agent_id', $cred_officer->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            $joining_fee = $month_new_customers->count() * 150;
            //check number of disbursed loans in that month
            $month_disbursed_loans = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                $build->where('field_agent_id', $cred_officer->id);
            })->where('disbursed', true)->whereYear('disbursement_date', $current_year)->whereMonth('disbursement_date', $month)->count();
            //check number of disbursed loans so far in that year
            $year_disbursed_loans = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                $build->where('field_agent_id', $cred_officer->id);
            })->where('disbursed', true)->whereYear('disbursement_date', $current_year)->count();
            if ($current_year == 2019) {
                $loan_processing_amount = 400;
            } else {
                $loan_processing_amount = $setting->loan_processing_fee;
            }
            $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
            $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;
            foreach ($products as $product) {
                $los = $branch->loans()->whereHas('customer', function (Builder $build) use ($cred_officer) {
                    $build->where('field_agent_id', $cred_officer->id);
                })->where('product_id', $product->id)->get();

                $los_ids = $los->pluck('id')->toArray();

                $interest_month_year = Installment::query()
                    // ->where('completed', '=', true)
                    ->whereIn('loan_id', $los_ids)
                    ->whereYear('interest_payment_date', $year)
                    ->whereMonth('interest_payment_date', $month)
                    ->whereColumn('amount_paid', '>=', 'interest')
                    ->sum('interest');

                $interest_year = Installment::query()
                    // ->where('completed', '=', true)
                    ->whereIn('loan_id', $los_ids)
                    ->whereYear('interest_payment_date', $year)
                    ->whereColumn('amount_paid', '>=', 'interest')
                    ->sum('interest');

                $loan_interest += $interest_month_year;

                $YTD_loan_interest += $interest_year;

                $total_loan_interest += $interest_month_year;

                $YTD_total_loan_interest += $interest_year;

                $rollover_interest += 0;

                $YTD_rollover_interest += 0;

                $total_rollover_interest += 0;

                $YTD_total_rollover_interest += 0;

                // foreach ($los as $lo) {
                //     $loan_interest += $lo->paidInterest($year, $month);
                //     $YTD_loan_interest += $lo->paidInterest($year, null);
                //     $total_loan_interest += $lo->paidInterest($year, $month);
                //     $YTD_total_loan_interest += $lo->paidInterest($year, null);
                //     $rollover_interest += $lo->paidRolloverInterest($year, $month);
                //     $YTD_rollover_interest += $lo->paidRolloverInterest($year, null);
                //     $total_rollover_interest += $lo->paidRolloverInterest($year, $month);
                //     $YTD_total_rollover_interest += $lo->paidRolloverInterest($year, null);
                // }

                $selc = [$product->product_name, number_format($loan_interest, 2), number_format($YTD_loan_interest, 2)];
                $this->data['product'][] = $selc;
                $loan_interest = 0;
                $YTD_loan_interest = 0;
            }
            $this->data['sub_title'] = "Income Statement in for Credit Officer - " . $cred_officer->name . " . Branch - " . $branch->bname;
            $this->data['current_branch'] = $branch->id;
            $this->data['current_co'] = $cred_officer->id;
        } // all branches
        elseif (\auth()->user()->hasRole('investor')){
            $user = \auth()->user();
            $branch = Branch::query()->find($user->branch_id);
            $year_new_customers = Customer::where('branch_id', $branch->id)->whereYear('created_at', $year)->get();
            $YTD_joining_fee = $year_new_customers->count() * 150;
            $month_new_customers = Customer::where('branch_id', $branch->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            $joining_fee = $month_new_customers->count() * 150;
            //check number of disbursed loans in that month
            $month_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->whereMonth('disbursement_date', $month)->count();
            //check number of disbursed loans so far in that year
            $year_disbursed_loans = $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->count();
            if ($current_year == 2019) {
                $loan_processing_amount = 400;
            } else {
                $loan_processing_amount = $setting->loan_processing_fee;
            }
            $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
            $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;

            foreach ($products as $product) {
                $los = $branch->loans()->where('product_id', '=', $product->id)->get();

                $los_ids = $los->pluck('id')->toArray();

                $interest_month_year = Installment::query()
                    // ->where('completed', '=', true)
                    ->whereIn('loan_id', $los_ids)
                    ->whereYear('interest_payment_date', $year)
                    ->whereMonth('interest_payment_date', $month)
                    ->whereColumn('amount_paid', '>=', 'interest')
                    ->sum('interest');

                $interest_year = Installment::query()
                    //->where('completed', '=', true)
                    ->whereIn('loan_id', $los_ids)
                    ->whereYear('interest_payment_date', $year)
                    ->whereColumn('amount_paid', '>=', 'interest')
                    ->sum('interest');

                $loan_interest += $interest_month_year;

                $YTD_loan_interest += $interest_year;

                $total_loan_interest += $interest_month_year;

                $YTD_total_loan_interest += $interest_year;

                $rollover_interest += 0;

                $YTD_rollover_interest += 0;

                $total_rollover_interest += 0;

                $YTD_total_rollover_interest += 0;

                $selc = [$product->product_name, number_format($loan_interest, 2), number_format($YTD_loan_interest, 2)];
                $this->data['product'][] = $selc;
                $loan_interest = 0;
                $YTD_loan_interest = 0;
            }
            $this->data['sub_title'] = "Income Statement in " . $branch->bname . " All Credit-Officers";
            $this->data['current_branch'] = $branch->id;
            $this->data['current_co'] = 'all';
        } else {
            $year_new_customers = Customer::whereYear('created_at', $year)->get();
            $YTD_joining_fee = $year_new_customers->count() * 150;
            $month_new_customers = Customer::whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            $branches = Branch::query()->where('status', '=', true)->get();
            $joining_fee = $month_new_customers->count() * 150;

            $month_disbursed_loans = 0;
            $year_disbursed_loans = 0;
            foreach ($branches as $branch) {
                $year_disbursed_loans += $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->count();
                $month_disbursed_loans += $branch->loans()->where('disbursed', true)->whereYear('disbursement_date', $current_year)->whereMonth('disbursement_date', $month)->count();
            }
            if ($current_year == 2019) {
                $loan_processing_amount = 400;
            } else {
                $loan_processing_amount = $setting->loan_processing_fee;
            }
            $loan_processing_fee = $loan_processing_amount * $month_disbursed_loans;
            $YTD_loan_processing_fee = $loan_processing_amount * $year_disbursed_loans;

            foreach ($products as $product) {
                $branch_loan_interest = [];
                $branch_ytd_loan_interest = [];
                foreach ($branches as $branch) {
                    $los = $branch->loans()->where('product_id', $product->id)->get();
                    $branch_loan_interest = [];
                    $branch_ytd_loan_interest = [];
                    $los_ids = $los->pluck('id')->toArray();

                    $interest_month_year = Installment::query()
                        // ->where('completed', '=', true)
                        ->whereIn('loan_id', $los_ids)
                        ->whereYear('interest_payment_date', $year)
                        ->whereMonth('interest_payment_date', $month)
                        ->whereColumn('amount_paid', '>=', 'interest')
                        ->sum('interest');

                    $interest_year = Installment::query()
                        //->where('completed', '=', true)
                        ->whereIn('loan_id', $los_ids)
                        ->whereYear('interest_payment_date', $year)
                        ->whereColumn('amount_paid', '>=', 'interest')
                        ->sum('interest');

                    $loan_interest += $interest_month_year;

                    $YTD_loan_interest += $interest_year;

                    $total_loan_interest += $interest_month_year;

                    $YTD_total_loan_interest += $interest_year;

                    $rollover_interest += 0;

                    $YTD_rollover_interest += 0;

                    $total_rollover_interest += 0;

                    $YTD_total_rollover_interest += 0;

                    // foreach ($los as $lo) {
                    //     $loan_interest += $lo->paidInterest($year, $month);
                    //     $YTD_loan_interest += $lo->paidInterest($year, null);
                    //     $total_loan_interest += $lo->paidInterest($year, $month);
                    //     $YTD_total_loan_interest += $lo->paidInterest($year, null);
                    //     $rollover_interest += $lo->paidRolloverInterest($year, $month);
                    //     $YTD_rollover_interest += $lo->paidRolloverInterest($year, null);
                    //     $total_rollover_interest += $lo->paidRolloverInterest($year, $month);
                    //     $YTD_total_rollover_interest += $lo->paidRolloverInterest($year, null);
                    // }

                    $branch_loan_interest[] = $loan_interest;
                    $branch_ytd_loan_interest[] = $YTD_loan_interest;
                }
                $selc = [$product->product_name, number_format(array_sum($branch_loan_interest), 2), number_format(array_sum($branch_ytd_loan_interest), 2)];
                $this->data['product'][] = $selc;
                $loan_interest = 0;
                $YTD_loan_interest = 0;
            }
            $this->data['sub_title'] = "Income Statement in All Branches, All Credit-Officers";
            $this->data['current_branch'] = "all";
            $this->data['current_co'] = "all";
        }

        $total_income = $joining_fee + $loan_processing_fee + $total_loan_interest + $total_rollover_interest;
        $YTD_Total_income = $YTD_joining_fee + $YTD_loan_processing_fee + $YTD_total_loan_interest + $YTD_total_rollover_interest;
        $months = [
            [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];

        $msetting = Msetting::first();
        $exp = explode('|', $msetting->Utility_balance);
        $this->data['utility_balance'] = 'KSH ' . number_format($exp[2], 2) . ' as per ' . $msetting->last_updated;

        $this->data['joining_fee'] = $joining_fee;
        $this->data['processing_fee'] = $loan_processing_fee;

        if (\auth()->user()->hasRole('investor')){
            $this->data['branches'] = Branch::query()->where('id', '=', \auth()->user()->branch_id)->get();
            $this->data['credit_officers'] = User::role('field_agent')->where(['status' => true, 'branch_id' => \auth()->user()->branch_id])->get();
        } else {
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['credit_officers'] = User::role('field_agent')->where('status', '=', true)->get();
        }
        //years
        $yr_start = 2019;
        $yr_end = Carbon::now()->format('Y');
        $yrs = array();
        for ($i = $yr_start; $i <= $yr_end; $i++) {
            $yrs[] = $i;
        }
        $this->data['yrs'] = $yrs;
        $this->data['total_loan_interest'] = $total_loan_interest;
        $this->data['total_rollover_fee'] = $total_rollover_interest;
        $this->data['total_income'] = $total_income;
        $this->data['months'] = $months;
        $this->data['cur_month'] = $month;
        $this->data['current_month'] = $current_month;
        $this->data['current_year'] = $current_year;
        $this->data['YTD_joining_fee'] = $YTD_joining_fee;
        $this->data['YTD_loan_processing_fee'] = $YTD_loan_processing_fee;
        $this->data['YTD_total_loan_interest'] = $YTD_total_loan_interest;
        $this->data['YTD_total_rollover_interest'] = $YTD_total_rollover_interest;
        $this->data['YTD_Total_income'] = $YTD_Total_income;

        return view('pages.reports.income_statement_v2', $this->data);
    }

    /*public function income_statement_summary(Request $request)
    {
        $this->data['title'] = "Income Statement Report";
        $this->data['product'] = [];
        $loan_processing_fee = 0;
        $loan_interest = 0;
        $total_loan_interest = 0;
        $rollover_interest = 0;
        $total_rollover_interest = 0;
        // dd($request->all());


        if ($request->branch == "all" && $request->month != "0") {
            // dd($request->all());
            $this->data['sub_title'] = "income_statement Info";
            $joining_fee = Regpayment::whereYear('date_payed', $request->year)->whereMonth('date_payed', $request->month)->sum('amount');
            $loan_processing_fee = Payment::whereYear('date_payed', $request->year)->whereMonth('date_payed', $request->month)->where('payment_type_id', 3)->sum('amount');
            $loans = Loan::all();
            $products = Product::all();

            foreach ($products as $product) {
                $loans = Loan::where('product_id', $product->id)->get();
                foreach ($loans as $loan) {
                    if ($loan->profit > 0) {
                        $loan_interest += $loan->paidInterest();
                        $total_loan_interest += $loan->paidInterest();
                    }
                    $rollover_interest += $loan->paidRolloverInterest();
                    $total_rollover_interest += $loan->paidRolloverInterest();
                }
                $selc = [$product->product_name, number_format($loan_interest, 2)];
                // dd($selc); exit();
                array_push($this->data['product'], $selc);

                $loan_interest = 0;
                $rollover_interest = 0;
            }

        }


        $total_income = $joining_fee + $loan_processing_fee + $total_loan_interest + $total_rollover_interest;
        $months = [
            [0, "All"], [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];
        $this->data['joining_fee'] = $joining_fee;
        $this->data['processing_fee'] = $loan_processing_fee;
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['total_loan_interest'] = $total_loan_interest;
        $this->data['total_rollover_fee'] = $total_rollover_interest;
        $this->data['total_income'] = $total_income;
        $this->data['months'] = $months;


        return view('pages.reports.income_statement', $this->data);


    }*/

    //cashflow statement
    public function cash_flow_statement(Request $request)
    {
        //dd($request->month);

        if ($request->branch != null) {
            $branch = Branch::find($request->branch);
            $this->data['title'] = "Cashflow Statement " . $branch->bname;

            $total_loan_collections = $branch->total_loan_collections($request->year, $request->month);
            $total_expenses = $branch->total_expenses($request->year, $request->month);
            $getTotalLoanDisbursement = $branch->getTotalLoanDisbursement($request->year, $request->month);
            $current_month = date("F", mktime(0, 0, 0, $request->month, 1));
            $cur_month = date("m", mktime(0, 0, 0, $request->month, 1));


            $current_year = $request->year;
            $total_processing_fee = $branch->total_processing_fee($request->year, $request->month);
            $total_registration_fee = $branch->total_registration_fee($request->year, $request->month);
            $balance_bd = $branch->balance_bd($request->year, $request->month);
            $investments = $branch->investments()->whereYear('date_payed', $request->year)->whereMonth('date_payed', $request->month)->sum('amount');


        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $branch = Branch::first();
            $this->data['title'] = "Cashflow Statement " . $branch->bname;

            $total_loan_collections = $branch->total_loan_collections(date('Y'), date('m'));
            $total_expenses = $branch->total_expenses(date('Y'), date('m'));;
            $getTotalLoanDisbursement = $branch->getTotalLoanDisbursement(date('Y'), date('m'));
            $current_month = Carbon::now()->format('F');
            $cur_month = Carbon::now()->format('m');
            $current_year = Carbon::now()->format('Y');
            $total_processing_fee = $branch->total_processing_fee(date('Y'), date('m'));
            $total_registration_fee = $branch->total_registration_fee(date('Y'), date('m'));
            $balance_bd = $branch->balance_bd(date('Y'), date('m'));
            $investments = $branch->investments()->whereYear('date_payed', Carbon::now())->whereMonth('date_payed', Carbon::now())->sum('amount');
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['title'] = "Cashflow Statement " . $branch->bname;
            $total_loan_collections = $branch->total_loan_collections(date('Y'), date('m'));
            $total_expenses = $branch->total_expenses(date('Y'), date('m'));
            $getTotalLoanDisbursement = $branch->getTotalLoanDisbursement(date('Y'), date('m'));
            $current_month = Carbon::now()->format('F');
            $cur_month = Carbon::now()->format('m');
            $current_year = Carbon::now()->format('Y');
            $total_processing_fee = $branch->total_processing_fee(date('Y'), date('m'));
            $total_registration_fee = $branch->total_registration_fee(date('Y'), date('m'));
            $balance_bd = $branch->balance_bd(date('Y'), date('m'));
            $investments = $branch->investments()->whereYear('date_payed', Carbon::now())->whereMonth('date_payed', Carbon::now())->sum('amount');
        }
        $months = [
            [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];
        $this->data['months'] = $months;

        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        if (Auth::user()->hasrole('investor')) {
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();

        }
        $this->data['current_branch'] = $branch->id;
        $this->data['cur_month'] = (int)$cur_month;
        // dd($this->data['current_month']);


        $this->data['total_loan_collections'] = $total_loan_collections;
        $this->data['total_expenses'] = $total_expenses;
        $this->data['getTotalLoanDisbursement'] = $getTotalLoanDisbursement;
        $this->data['total_cash_outflows'] = $total_expenses + $getTotalLoanDisbursement;
        $this->data['current_month'] = $current_month;
        $this->data['current_year'] = $current_year;
        $this->data['total_processing_fee'] = $total_processing_fee;
        $this->data['total_registration_fee'] = $total_registration_fee;
        $this->data['balance_bd'] = $balance_bd;
        $this->data['investments'] = $investments;
        $this->data['total_cash_inflows'] = $total_loan_collections + $total_processing_fee + $total_registration_fee + $balance_bd + $investments;
        $this->data['net_cash_inflows'] = $this->data['total_cash_inflows'] - $this->data['total_cash_outflows'];


        return view('pages.reports.cash_flow_statement', $this->data);

    }

    //sms summary
    public function sms_summary()
    {
        $this->data['title'] = "SMS Summary Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "SMS Summary Info";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "SMS Summary Info in " . $branch->bname;
        }

        return view('pages.reports.sms_summary', $this->data);
    }

    public function sms_summary_data(Request $request)
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $branch = Branch::select('*');
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id);
        }
        return DataTables::of($branch)
            ->addColumn('sms', function ($branch) use ($request) {
                $start = $request->start_date;
                $end = $request->end_date;
                if ($start != $end) {
                    $user_sms = UserSms::where('branch_id', '=', $branch->id)->whereBetween('created_at', [$start, $end])->count();
                    $customer_sms = CustomerSms::where('branch_id', '=', $branch->id)->whereBetween('created_at', [$start, $end])->count();
                    $total = $user_sms + $customer_sms;
                } else {
                    $user_sms = UserSms::where('branch_id', '=', $branch->id)->count();
                    $customer_sms = CustomerSms::where('branch_id', '=', $branch->id)->count();
                    $total = $user_sms + $customer_sms;
                }
                return $total;
            })
            ->toJson();
    }


    /******************************branch expenses**********************/
    public function branch_expenses()
    {
        $this->data['title'] = "Expenses Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "All Expenses Info";
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['check_role'] = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant');
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "All Expenses Info in " . $branch->bname;
            $this->data['branches'] = Branch::where('id', '=', Auth::user()->branch_id)->get();
            $this->data['check_role'] = true;
        }

        return view('pages.reports.expenses', $this->data);
    }

    /**************************manager performance*********************/
    public function manager_officer_performance_list()
    {

        $this->data['title'] = "Managers List";

        $this->data['managers'] = User::role(['manager'])->where('status', true)->get();
        if (Auth::user()->hasrole('investor')) {
            $this->data['managers'] = User::role(['manager'])->where('branch_id', Auth::user()->branch_id)->get();

        }
        return view('pages.reports.managers_list', $this->data);
    }
    public function manager_performance_revamped($id){
        $user = User::find(decrypt($id));
        $branch = Branch::find($user->branch_id);
        $products = Product::all();
        $current_year = Carbon::now();

        $this->data['title'] = $user->name . ": Manager Performance Report - " . $branch->bname;
        $this->data['sub_title'] = "Detailed manager performance report.";
        $months = [[1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]];
        $this->data['months'] = $months;
        $this->data['cur_month'] = Carbon::now()->format('m');
        $this->data['user'] = $user;
        $this->data['id'] = $id;
        $this->data['products'] = $products;
        $years = [];
        for ($i = 2018; $i <= now()->format('Y'); $i++) {
            $years[] = $i;
        }
        $this->data['current_year'] = $years;

        return view('pages.reports.manager_performance_revamped', $this->data);

    }

    public function manager_performance($id)
    {
        $user = User::find(decrypt($id));
        $branch = Branch::find($user->branch_id);
        $this->data['title'] = $user->name . ": Manager Performance Report - " . $branch->bname;
        $this->data['sub_title'] = "Detailed manager performance report.";
        $months = [[1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]];
        $due_today_amount = 0;
        $totalAmount = 0;
        $customers = Customer::where('branch_id', $branch->id)->count();
        $loans = $branch->loans()->where(['settled' => false, 'disbursed' => true])->count();
        $due_today_count = $branch->loans()->where(['settled' => false])->whereHas('Installments', function ($q) {
            $q->where(['current' => true, 'completed' => false])->whereDate('due_date', Carbon::now());
        })->count();

        $due_today_amount = $branch->today_inst_amount();
        $totalAmount = $branch->getLoanBalanceAttribute();
        $amount_paid = $branch->getMonthTotalPaidAttribute();
        $TotalLoanAmount = $branch->getMonthTotalLoanAttribute();
        $pending_approval = $branch->loans()->where(['approved' => false, 'disbursed' => false])->count();
        $pending_disbursements = $branch->loans()->where(['approved' => true, 'disbursed' => false])->count();

        //arrears
        $loans_w_arrears = $branch->loans()->where('settled', false)->whereHas('arrears')->get();
        $l = array();
        foreach ($loans_w_arrears as $lns) {
            $last_payment_date = $lns->last_payment_date;
            if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                array_push($l, $lns);
            }
        }
        $arrears_count = count($l);
        $arrears_total = 0;
        foreach ($l as $t) {
            $arrears_total += $t->total_arrears;
        }

        //rolled over loans - today
        $rolled_over_loans_today = $branch->loans()->whereHas('rollover', function (Builder $query) {
            $query->whereDate('rollover_date', '=', Carbon::now());
        })->where(['settled' => false, 'disbursed' => true, 'rolled_over' => true])->get();
        $rolled_over_loans_today_count = $rolled_over_loans_today->count();
        $rolled_over_balance_today = 0;
        foreach ($rolled_over_loans_today as $lo) {
            $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
            $product = Product::find($lo->product_id);
            if ($lo->rolled_over) {
                $rollover = Rollover::where('loan_id', $lo->id)->first();
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            } else {
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
            }
            $rolled_over_balance_today += $total - $payments;
        }

        //rolled over loans - month
        $rolled_over_loans = $branch->loans()->whereHas('rollover', function (Builder $query) {
            $query->whereMonth('rollover_date', '=', Carbon::now())
                ->whereYear('rollover_date', '=', Carbon::now());
        })
            ->where(['settled' => false, 'disbursed' => true, 'rolled_over' => true])->get();
        $rolled_over_loans_count = $rolled_over_loans->count();
        $rolled_over_balance = 0;
        foreach ($rolled_over_loans as $lo) {
            $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
            $product = Product::find($lo->product_id);
            if ($lo->rolled_over) {
                $rollover = Rollover::where('loan_id', $lo->id)->first();
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            } else {
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
            }
            $rolled_over_balance += $total - $payments;
        }

        //rolled over loans - year
        $rolled_over_loans_year = $branch->loans()->whereHas('rollover', function (Builder $query) {
            $query->whereYear('rollover_date', '=', Carbon::now());
        })
            ->where(['settled' => false, 'disbursed' => true, 'rolled_over' => true])->get();
        $rolled_over_loans_year_count = $rolled_over_loans_year->count();
        $rolled_over_balance_year = 0;
        foreach ($rolled_over_loans_year as $lo) {
            $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
            $product = Product::find($lo->product_id);
            if ($lo->rolled_over) {
                $rollover = Rollover::where('loan_id', $lo->id)->first();
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
            } else {
                $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
            }
            $rolled_over_balance_year += $total - $payments;
        }

        //non performing loans
        $non_performing_loans = array();
        $unsettled_loans = $branch->loans()->where(['settled' => false, 'disbursed' => true])->get();
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
        //repayment rate
        if ($due > 0) {
            $repayment_rate = ($paid / $due) * 100;

        } else {
            $repayment_rate = 0;
        }

        $av = '--';

        //disbursed loans
        $disbursed = $branch->loans()->where('disbursed', true)->get();
        $disbTotalAmount = 0;
        $disbPaidAmount = 0;
        foreach ($disbursed as $disb) {
            $disbTotalAmount += $disb->getTotalAttribute();
            $disbPaidAmount += $disb->getAmountPaidAttribute();
        }
        if (count($disbursed) > 0) {
            $loanSize = $disbTotalAmount / count($disbursed);
        } else {
            $loanSize = 0;
        }

        $disbursedMonth = $branch->loans()->where('disbursed', true)->whereMonth('disbursement_date', Carbon::now())->whereYear('disbursement_date', Carbon::now())->get();
        $disbTotalAmountMonth = 0;
        $disbPaidAmountMonth = 0;
        foreach ($disbursedMonth as $disb) {
            $disbTotalAmountMonth += $disb->getTotalAttribute();
            $disbPaidAmountMonth += $disb->getAmountPaidAttribute();
        }
        if (count($disbursedMonth) > 0) {
            $loanSizeMonth = $disbTotalAmountMonth / count($disbursedMonth);
        } else {
            $loanSizeMonth = 0;
        }

        $interest_data = self::co_and_manager_interest_figures($user, Carbon::now()->format('m'));
        $this->data['total_interest'] = $interest_data['total_interest'];
        $this->data['interest_paid'] = $interest_data['total_paid_interest'];
        $this->data['cred_officers'] = User::role('field_agent')->where(['status' => true, 'branch_id' => $branch->id])->count();
        $this->data['loanSize'] = $loanSize;
        $this->data['disbCount'] = count($disbursed);
        $this->data['disbTotalAmount'] = $disbTotalAmount;
        $this->data['loanSizeMonth'] = $loanSizeMonth;
        $this->data['disbCountMonth'] = count($disbursedMonth);
        $this->data['disbTotalAmountMonth'] = $disbTotalAmountMonth;
        $this->data['rolled_over_loans_count_today'] = $rolled_over_loans_today_count;
        $this->data['rolled_over_loans_count'] = $rolled_over_loans_count;
        $this->data['rolled_over_loans_count_year'] = $rolled_over_loans_year_count;
        $this->data['rolled_over_balance'] = $rolled_over_balance;
        $this->data['rolled_over_balance_today'] = $rolled_over_balance_today;
        $this->data['rolled_over_balance_year'] = $rolled_over_balance_year;
        $this->data['non_performing_balance'] = $non_performing_balance;
        $this->data['non_performing_count'] = $non_performing_count;
        $this->data['amount_paid'] = $amount_paid;
        $this->data['TotalLoanAmount'] = $TotalLoanAmount;

        $this->data['due'] = $due;
        $this->data['paid'] = $paid;
        $this->data['repayment_rate'] = number_format($repayment_rate, 2);

        $this->data['customers'] = $customers;
        $this->data['av'] = $av;

        $this->data['loans'] = $loans;
        $this->data['totalAmount'] = $totalAmount;
        $this->data['due_today_count'] = $due_today_count;
        $this->data['due_today_amount'] = $due_today_amount;
        $this->data['pending_approval'] = $pending_approval;
        $this->data['pending_disbursements'] = $pending_disbursements;
        $this->data['arrears_count'] = $arrears_count;
        $this->data['arrears_total'] = $arrears_total;
        $this->data['months'] = $months;
        $this->data['cur_month'] = Carbon::now()->format('m');
        $this->data['user'] = $user;

        return view('pages.reports.manager_performance', $this->data);


    }

    public function manager_performance_data(Request $request, $id)
    {
//Log::info($request->all());
        if ($request->month) {
            $month = $request->month;
            // $ro = RoTarget::where('user_id', decrypt($id))->whereYear('date', Carbon::now())->whereMonth('date', $request->month);
            $user = User::find(decrypt($id));
            $ros = User::role('field_agent')->where('branch_id', $user->branch_id)->whereHas('RoTarget', function ($q) use ($request) {
                $q->whereYear('date', Carbon::now())->whereMonth('date', $request->month);
            });

        } else {
            $month = Carbon::now();
            $user = User::find(decrypt($id));
            $ros = User::role('field_agent')->where('branch_id', $user->branch_id)->whereHas('RoTarget', function ($q) use ($request) {
                $q->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now());
            });

        }
        //dd($ros->get());

        $lo = array();
        foreach ($ros->get() as $ro) {
            //collection Target
            $cts = DB::table('ro_targets')->where('user_id', $ro->id)->whereYear('date', Carbon::now())->whereMonth('date', $month)->get();
            $collection_target = $cts->sum('collection_target');
            //disbursement_target_amount
            $disbusement_target = 0;
            $customer_target = 0;
            if ($cts->first()){
                $disbusement_target = $cts->first()->disbursement_target_amount;
                //customer_target
                $customer_target = $cts->first()->customer_target;

            }
            //actual_disbursement_amount
            $loans = DB::table('loans')
                ->where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', Carbon::now())
                ->join('customers', function ($join) use($ro){
                    $join->on('loans.customer_id', '=', 'customers.id')
                        ->where('field_agent_id', $ro->id);
                })->select('loans.loan_amount', 'loans.id');
            $actual_disbursement_amount = $loans->sum('loan_amount');
            //disbursement_achieved
            $disbursement_achieved = $disbusement_target!=0 ? ($actual_disbursement_amount/$disbusement_target)*100 : $disbusement_target;
            //actual_collection
            $actual_collection = Payment::where(['payment_type_id' => 1])->whereIn('loan_id', $loans->pluck('id'))->whereYear('date_payed', Carbon::now())->whereMonth('date_payed', $month)/*->whereDay('date_payed', $date)*/->sum('amount');
           // dd($loans->pluck('id'), $actual_collection);

            //collection_achieved
            $collection_achieved = $collection_target!=0 ? ($actual_collection/$collection_target) * 100 : $collection_target;
            //customer_enrolled
            $customers = Customer::where(['field_agent_id' => $ro->id])->whereMonth('created_at', $month)->whereYear('created_at', Carbon::now())->get();
            $customer_enrolled = $customers->count();
            $customer_target_achieved =$customer_target != 0 ? ($customer_enrolled/$customer_target)*100 : $customer_target;
            //average_performance
            $average_performance = ($customer_target_achieved+$collection_achieved+$disbursement_achieved)/3;







            array_push($lo, array(
                'id'=>$ro->id,
                'name'=>$ro->name,
                'collection_target'=>$collection_target,
                'disbursement_target_amount' =>$disbusement_target,
                'actual_disbursement_amount' => $actual_disbursement_amount,
                'actual_collection'=>$actual_collection,
                'collection_achieved'=>$collection_achieved,
                'disbursement_achieved'=>number_format($disbursement_achieved),
                'customer_target'=>$customer_target,
                'customer_enrolled'=>$customer_enrolled,
                'customer_target_achieved'=>number_format($customer_target_achieved),
                'average_performance' => number_format($average_performance)

            ));


        }
       // dd($lo);
       // dd($ro->get());
       //  $ro = RoTarget::where('user_id', decrypt($id))->whereYear('date', Carbon::now())->whereMonth('date', Carbon::now());


       // dd($ro->get()[0]->target($month));
       // dd($ro->get());
        return DataTables::of($lo)
//            ->editColumn('collection_target', function ($ro) use ($month) {
//                $cts = DB::table('ro_targets')->where('user_id', $ro->id)->whereYear('date', Carbon::now())->whereMonth('date', $month)->sum('collection_target');
//
//                return $cts;
//            })
//            ->editColumn('disbursement_target_amount', function ($ro) use ($month) {
//
//
//                $cts = DB::table('ro_targets')->where('user_id', $ro->id)->whereYear('date', Carbon::now())->whereMonth('date', $month)->first();
//                if ($cts){
//                    return $cts->disbursement_target_amount;
//                }
//                return 0;
//
//
//            })
//            ->editColumn('actual_disbursement', function ($ro) use($month){
//
//                $loans = DB::table('loans')
//                    ->where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', Carbon::now())
//                    ->join('customers', function ($join) use($ro){
//                        $join->on('loans.customer_id', '=', 'customers.id')
//                            ->where('field_agent_id', $ro->id);
//                    })->count();
//
//                return $loans;
//            })
//            ->editColumn('actual_disbursement_amount', function ($ro) use ($month){
//                $loans = DB::table('loans')
//                    ->where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', Carbon::now())
//                    ->join('customers', function ($join) use($ro){
//                        $join->on('loans.customer_id', '=', 'customers.id')
//                            ->where('field_agent_id', $ro->id);
//                    })->sum('loan_amount');
//
//                return $loans;
//            })
//
//            ->editColumn('actual_collection', function ($ro) use ($month) {
//
//                $loans = $ro->loans()->where('disbursed', true)->get();
//                $arr = array();
//                $payments = 0;
//                foreach ($loans as $loan) {
//                    array_push($arr, $loan->id);
//
//                }
//                $cts = RoTarget::where('user_id', $ro->id)->whereYear('date', Carbon::now())->whereMonth('date', $month)->get();
//                foreach ($cts as $ct) {
//
//                    $instalments = Installment::whereDate('due_date', $ct->date)->whereDate('last_payment_date', $ct->date)->whereIn('loan_id', $arr)->get();
//
//                    foreach ($instalments as $instalment) {
//                        $payments += $instalment->amount_paid;
//
//
//                    }
//                }
//                return $payments;
//
//
//
//            })
//            ->editColumn('collection_achieved', function ($ro) use ($month) {
//                /***********************colletected*/
//                $loans = $ro->loans()->where('disbursed', true)->get();
//                $arr = array();
//                $payments = 0;
//                foreach ($loans as $loan) {
//                    array_push($arr, $loan->id);
//
//                }
//                $cts = RoTarget::where('user_id', $ro->id)->whereYear('date', Carbon::now())->whereMonth('date', $month)->get();
//                foreach ($cts as $ct) {
//
//                    $instalments = Installment::whereDate('due_date', $ct->date)->whereDate('last_payment_date', $ct->date)->whereIn('loan_id', $arr)->get();
//
//                    foreach ($instalments as $instalment) {
//                        $payments += $instalment->amount_paid;
//
//
//                    }
//                }
//
//
//
//                /**************************target**************************/
//                $cts = DB::table('ro_targets')->where('user_id', $ro->id)->whereYear('date', Carbon::now())->whereMonth('date', $month)->get();
//                $collection_target = 0;
//                foreach ($cts as $ct) {
//                    $collection_target += $ct->collection_target;
//                }
//
//                if ($collection_target != 0) {
//                    $per = (int)(($payments / $collection_target) * 100);
//
//                } else {
//                    $per = 0;
//
//                }
//                return $per;
//            })
//            ->editColumn('average_performance', function ($ro) use ($month) {
//
//                /*****************************disbursed********************/
//                $dis = $ro->loans()->where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
//
//                if ($ro->target($month)->disbursement_target_amount != 0) {
//                    $cper = (int)(($dis / $ro->target($month)->disbursement_target_amount) * 100);
//
//                } else {
//                    $cper = 0;
//                }
//
//                /***********************colletected**************************/
//                $loans = $ro->loans()->where('disbursed', true)->get();
//                $arr = array();
//                $payments = 0;
//                foreach ($loans as $loan) {
//                    array_push($arr, $loan->id);
//
//                }
//                $cts = RoTarget::where('user_id', $ro->id)->whereYear('date', Carbon::now())->whereMonth('date', $month)->get();
//                foreach ($cts as $ct) {
//
//                    $instalments = Installment::whereDate('due_date', $ct->date)->whereDate('last_payment_date', $ct->date)->whereIn('loan_id', $arr)->get();
//
//                    foreach ($instalments as $instalment) {
//                        $payments += $instalment->amount_paid;
//
//
//                    }
//                }
//                $cts = RoTarget::where('user_id', $ro->id)->whereYear('date', Carbon::now())->whereMonth('date', $month)->get();
//                $collection_target = 0;
//                foreach ($cts as $ct) {
//                    $collection_target += $ct->collection_target;
//                }
//
//                if ($collection_target != 0) {
//                    $per = (int)(($payments / $collection_target) * 100);
//
//                } else {
//                    $per = 0;
//
//                }
//                $customers = Customer::where(['field_agent_id' => $ro->id])->whereMonth('created_at', $month)->count();
//                if ($ro->target($month)->customer_target != 0) {
//                    $Onper = (int)(($customers / $ro->target($month)->customer_target) * 100);
//                } else {
//                    $Onper = 0;
//                }
//
//                $av = ($per + $cper + $Onper) / 3;
//                return number_format($av);
//
//
//            })
//
//            ->editColumn('disbursement_achieved', function ($ro) use($month){
//
//                $dis = $ro->loans()->where('disbursed', true)->whereMonth('disbursement_date', $month)->whereYear('disbursement_date', Carbon::now())->sum('loan_amount');
//                if ($ro->target($month)->disbursement_target_amount != 0) {
//                    $per = (int)(($dis / $ro->target($month)->disbursement_target_amount) * 100);
//                } else {
//                    $per = 0;
//                }
//                return $per;
//            })
//            ->addColumn('customer_target', function ($ro) use ($month) {
//                return $ro->target($month)->customer_target;
//
//            })
//            ->addColumn('customer_enrolled', function ($ro) use ($month){
//
//                $customers = Customer::where(['field_agent_id' => $ro->id])->whereMonth('created_at', $month)->whereYear('created_at', Carbon::now())->count();
//                return $customers;
//            })
//            ->addColumn('customer_target_achieved', function ($ro) use($month){
//
//                $customers = Customer::where(['field_agent_id' => $ro->id])->whereMonth('created_at', $month)->whereYear('created_at', Carbon::now())->count();
//                if ($ro->target($month)->customer_target != 0) {
//                    $per = (int)(($customers / $ro->target($month)->customer_target) * 100);
//                } else {
//                    $per = 0;
//                }
//                return $per;
//
//            })
            ->toJson();


    }

    // par analysis
    public function par_analysis()
    {
        $this->data['title'] = "PAR ANALYSIS";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "PAR analysis";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "PAR analysis in " . $branch->bname;
        }

        return view('pages.reports.par_analysis', $this->data);


    }

    //collection rates
    public function collection_rate(Request $request)
    {
        $this->data['title'] = "Collection Rate Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "Collection Rate Report " . $branch->bname;
        }

        $categories = DB::table('categories')->get();
        $per = [];
        $calc = 0;
        $tbalance = 0;
        $tarrears = 0;
        if ($request->branch) {
            $branch = Branch::find($request->branch);
            foreach ($categories as $lo) {
                $loans = $branch->loans()->where(['disbursed' => true])->wherehas('arrears', function ($q) {
                    $q->where('amount', '>', 0);
                })->get();

                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {
                        $arer = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->sum('amount');

                        $tbalance += $loan->balance;
                        $tarrears += $arer;
                    }
                }
                if ($tbalance > 0) {
                    $calc = ($tarrears / $tbalance) * 100;
                }
                $per[] = array("category" => $lo->name, "percentage" => $calc);

                $tbalance = 0;
                $tarrears = 0;
                $calc = 0;


            }


        } else {
            foreach ($categories as $lo) {
                //  $loans = $branch->loans()->where(['product_id' => $lo->id, 'disbursed' => true])->count();
                $branch = Branch::find(Auth::user()->branch_id);
                $loans = $branch->loans()->where(['disbursed' => true])->wherehas('arrears', function ($q) {
                    $q->where('amount', '>', 0);
                })->get();

                foreach ($loans as $loan) {
                    $arrear = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->first();
                    $inst = Installment::find($arrear->installment_id);
                    $created = Carbon::parse($inst->due_date);
                    $overdue_days = $created->diffInDays(Carbon::now());
                    if ($overdue_days <= $lo->days && $overdue_days > $lo->days - 30) {
                        $arer = Arrear::where(['loan_id' => $loan->id, ['amount', '>', 0]])->sum('amount');

                        $tbalance += $loan->balance;
                        $tarrears += $arer;
                    }
                }
                if ($tbalance > 0) {
                    $calc = ($tarrears / $tbalance) * 100;
                }


                //array_push($per, $calc);
                $per[] = array("category" => $lo->name, "percentage" => $calc);

                $tbalance = 0;
                $tarrears = 0;
                $calc = 0;

            }
        }

        $this->data['data'] = $per;
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();

        //  dd($per);


        return view('pages.reports.collection_rate', $this->data);


    }

    //customer scoring
    public function customer_scoring()
    {
        $this->data['title'] = "Customer Scoring Report";

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "Customer Scoring Report in" . $branch->bname;
        }
        return view('pages.reports.customer_scoring', $this->data);

    }

    public function customer_scoring_data()
    {

        /*$customers = \App\models\Customer::whereHas('loans', function ($query) {
            $query->where('settled', 0);
        })->select("*");*/

        /* $customers = Customer::whereNotIn('id', Customer::whereHas('loans', function ($query) {
             $query->where('disbursed', true);
         })->pluck('id'));*/
        /* $customers =Customer::whereHas('loans', function ($query) {
             $query->where('disbursed', true);
         })->pluck('id');*/
        $customers = \App\models\Customer::whereHas('loans', function ($query) {
            $query->where('disbursed', true);
        })->select("*");


        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {

            $customers = $customers;

        } elseif (Auth::user()->hasRole('field_agent')) {

            $customers = $customers->where('field_agent_id', Auth::id());
        } elseif (Auth::user()->hasRole('collection_officer')) {

            $customers = $customers->where('field_agent_id', Auth::user()->field_agent_id);
        } else {

            $customers = $customers->where('branch_id', Auth::user()->branch_id);
        }

        return DT::eloquent($customers)
            ->addColumn('fullName', function (\App\models\Customer $customer) {
                return sprintf("%s %s", $customer->title, $customer->fullName);
            })
            ->addColumn('branchName', function (\App\models\Customer $customer) {
                return $customer->branch->bname;
            })
            ->addColumn('loanOfficer', function (\App\models\Customer $customer) {
                return $customer->Officer->name;
            })
            ->addColumn('phoneNumber', function (\App\models\Customer $customer) {
                return $customer->phone;
            })
            ->addColumn('profession', function (\App\models\Customer $customer) {
                return $customer->industry->iname;
            })
            ->addColumn('dealingIn', function (\App\models\Customer $customer) {
                return $customer->businessType->bname;
            })
            ->addColumn('identificationNumber', function (\App\models\Customer $customer) {
                return $customer->id_no;
            })
            ->addColumn('prequalifiedAmount', function (\App\models\Customer $customer) {
                // $amount = \DB::table('prequalified_loans')->whereId($customer->prequalified_amount)->first();
                return $customer->prequalified_amount;
            })
            ->addColumn('customerCreatedDate', function (\App\models\Customer $customer) {

                $dateTime = \Carbon\Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('lastDisbursement', function (\App\models\Customer $customer) {

                $rawDate = $customer->disbursements()->orderByDesc('disbursement_date')->first()->disbursement_date ?? null;

                $dateTime = \Carbon\Carbon::parse($rawDate);
                return sprintf("%s/%s/%s %s:%s:%s %s",
                    $dateTime->day, $dateTime->month, $dateTime->year, $dateTime->hour, $dateTime->minute, $dateTime->second, $dateTime->format('A'));
            })
            ->addColumn('inactiveDays', function (\App\models\Customer $customer) {

                $lastCompletePayment = $customer->lastCompletePayment()->orderByDesc('date_payed')->first()->date_payed ?? null;
                $lastCompletePayment = \Carbon\Carbon::parse($lastCompletePayment);

                return \Carbon\Carbon::now()->diffInDays($lastCompletePayment);
            })
            ->addColumn('totalNumberOfLoans', function (\App\models\Customer $customer) {
                return $customer->loans->count();
            })
            ->addColumn('paid_loans', function (\App\models\Customer $customer) {
                return $customer->loans->where('settled', true)->count();
            })
            ->addColumn('loansArrear', function (\App\models\Customer $customer) {
                return $customer->loans()->whereHas('arrears', function ($q) {
                    $q->where('amount', '>', 0);
                })->count();
            })
            ->addColumn('loansWithoutArrear', function (\App\models\Customer $customer) {
                $paidwitharrears = $customer->loans()->where('settled', true)->whereHas('installments', function ($q) {
                    $q->where('in_arrear', true);
                })->count();
                $paid = $customer->loans()->where('settled', true)->count();
                $withoutarrears = $paid - $paidwitharrears;
                return $withoutarrears;
            })
            ->addColumn('perOfLoanwithoutArrears', function (\App\models\Customer $customer) {
                $paidwitharrears = $customer->loans()->whereHas('installments', function ($q) {
                    $q->where('in_arrear', true);
                })->count();
                $all = $customer->loans()->where('disbursed', true)->count();
                $withoutarrears = $all - $paidwitharrears;


                return ($withoutarrears / $all) * 100;
            })
            ->addColumn('lastPaidAmount', function (\App\models\Customer $customer) {
                $payments = Payment::where('payment_type_id', 1)->whereIn('loan_id', $customer->loans()->where('disbursed', true)->pluck('id'))->latest()->first();
                if ($payments) {
                    return $payments->amount;

                } else {
                    return 0;
                }
            })
            ->addColumn('skippedDuePayments', function (\App\models\Customer $customer) {
                $instal = Installment::where('in_arrear', true)->whereIn('loan_id', $customer->loans()->pluck('id'))->count();
                return $instal;
            })
            ->toJson();
    }

    /**************************************- GROUP REPORTS -********************************************/
    public function group_index()
    {

        $this->data['title'] = "System Group Reports";

        $this->data['sub_title'] = "List of Group Lending System Reports";

        return view('pages.reports.group-reports.group_index', $this->data);
    }

    public function group_data()
    {
        $lo = Report::select('*')->where('for_group', true);

        return Datatables::of($lo)
            ->addColumn('route', function ($lo) {
                $data = $lo->route;
                return '<a href="' . url($data) . '"    class="sel-btn btn btn-xs btn-primary" ><i class="feather icon-eye text-danger" ></i> View</a>';
            })
            ->rawColumns(['route', 'checkbox'])
            ->make(true);
    }

    public function group_loan_arrears()
    {
        $this->data['title'] = "Group Lending: Loan Arrears";
        $this->data['groups'] = Group::all();

        //$this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        //$this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of Group Loans in Arrears";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of All Group Loan Arrears in " . $branch->bname;
        }
        return view('pages.reports.group-reports.loan_arrears', $this->data);
    }

    public function group_loan_arrears_data(Request $request)
    {
        if ($request->group != 'all') {
            $loans_w_arrears = Loan::where('group_id', '=', $request->group)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->where(['disbursed' => true, 'settled' => false])->get();
            $lo = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                $product = Product::find($lns->product_id);
                if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lo, array(
                        'id' => $lns->id,
                        'loan_account' => $lns->loan_account,
                        'loan_amount' => $lns->loan_amount,
                        'product_id' => $lns->product_id,
                        'customer_id' => $lns->customer_id,
                        'group_id' => $lns->group_id,
                        'date_created' => $lns->date_created,
                        'end_date' => $lns->end_date,
                        'approved' => $lns->approved,
                        'approved_date' => $lns->approved_date,
                        'disbursed' => $lns->disbursed,
                        'disbursement_date' => $lns->disbursement_date,
                        'created_at' => $lns->created_at,
                        'updated_at' => $lns->updated_at,
                        'purpose' => $lns->purpose,
                        'settled' => $lns->settled,
                        'rolled_over' => $lns->rolled_over,
                        'approved_by' => $lns->approved_by,
                        'disbursed_by' => $lns->disbursed_by,
                        'fname' => $lns->customer->fname,
                        'lname' => $lns->customer->lname,
                        'phone' => $lns->customer->phone,
                        'product_name' => $product->product_name,
                        'installments' => $product->installments,
                        'interest' => $product->interest,
                        'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                    ));
                }
            }
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->where(['disbursed' => true, 'settled' => false])->get();
            $lo = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                $product = Product::find($lns->product_id);
                if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lo, array(
                        'id' => $lns->id,
                        'loan_account' => $lns->loan_account,
                        'loan_amount' => $lns->loan_amount,
                        'product_id' => $lns->product_id,
                        'customer_id' => $lns->customer_id,
                        'group_id' => $lns->group_id,
                        'date_created' => $lns->date_created,
                        'end_date' => $lns->end_date,
                        'approved' => $lns->approved,
                        'approved_date' => $lns->approved_date,
                        'disbursed' => $lns->disbursed,
                        'disbursement_date' => $lns->disbursement_date,
                        'created_at' => $lns->created_at,
                        'updated_at' => $lns->updated_at,
                        'purpose' => $lns->purpose,
                        'settled' => $lns->settled,
                        'rolled_over' => $lns->rolled_over,
                        'approved_by' => $lns->approved_by,
                        'disbursed_by' => $lns->disbursed_by,
                        'fname' => $lns->customer->fname,
                        'lname' => $lns->customer->lname,
                        'phone' => $lns->customer->phone,
                        'product_name' => $product->product_name,
                        'installments' => $product->installments,
                        'interest' => $product->interest,
                        'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                    ));
                }
            }
        } elseif (Auth::user()->hasRole('field_agent')) {
            $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('group', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', Auth::user()->id);
            })->where(['disbursed' => true, 'settled' => false])->get();
            $lo = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                $product = Product::find($lns->product_id);
                if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lo, array(
                        'id' => $lns->id,
                        'loan_account' => $lns->loan_account,
                        'loan_amount' => $lns->loan_amount,
                        'product_id' => $lns->product_id,
                        'customer_id' => $lns->customer_id,
                        'group_id' => $lns->group_id,
                        'date_created' => $lns->date_created,
                        'end_date' => $lns->end_date,
                        'approved' => $lns->approved,
                        'approved_date' => $lns->approved_date,
                        'disbursed' => $lns->disbursed,
                        'disbursement_date' => $lns->disbursement_date,
                        'created_at' => $lns->created_at,
                        'updated_at' => $lns->updated_at,
                        'purpose' => $lns->purpose,
                        'settled' => $lns->settled,
                        'rolled_over' => $lns->rolled_over,
                        'approved_by' => $lns->approved_by,
                        'disbursed_by' => $lns->disbursed_by,
                        'fname' => $lns->customer->fname,
                        'lname' => $lns->customer->lname,
                        'phone' => $lns->customer->phone,
                        'product_name' => $product->product_name,
                        'installments' => $product->installments,
                        'interest' => $product->interest,
                        'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                    ));
                }
            }
        } else {
            $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                $builder->where('amount', '!=', 0);
            })->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('branch_id', '=', Auth::user()->branch_id);
            })->where(['disbursed' => true, 'settled' => false])->get();
            $lo = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                $product = Product::find($lns->product_id);
                if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                    array_push($lo, array(
                        'id' => $lns->id,
                        'loan_account' => $lns->loan_account,
                        'loan_amount' => $lns->loan_amount,
                        'product_id' => $lns->product_id,
                        'customer_id' => $lns->customer_id,
                        'group_id' => $lns->group_id,
                        'date_created' => $lns->date_created,
                        'end_date' => $lns->end_date,
                        'approved' => $lns->approved,
                        'approved_date' => $lns->approved_date,
                        'disbursed' => $lns->disbursed,
                        'disbursement_date' => $lns->disbursement_date,
                        'created_at' => $lns->created_at,
                        'updated_at' => $lns->updated_at,
                        'purpose' => $lns->purpose,
                        'settled' => $lns->settled,
                        'rolled_over' => $lns->rolled_over,
                        'approved_by' => $lns->approved_by,
                        'disbursed_by' => $lns->disbursed_by,
                        'fname' => $lns->customer->fname,
                        'lname' => $lns->customer->lname,
                        'phone' => $lns->customer->phone,
                        'product_name' => $product->product_name,
                        'installments' => $product->installments,
                        'interest' => $product->interest,
                        'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                    ));
                }
            }
        }

        return Datatables::of($lo)
            ->editColumn('owner', function ($lo) {
                $customer = Customer::find($lo['customer_id']);
                $group = Group::find($lo['group_id']);
                if ($group) {
                    $group = $customer->group()->first();
                    $group_name = $group->name;

                    return $customer->fname . ' ' . $customer->lname . '<br>' . '<span class="badge badge-primary" style="font-size: small">' . $group_name . '</span>';
                } else {
                    return $customer->fname . ' ' . $customer->lname;
                }

            })
            ->addColumn('next_payment_date', function ($lo) {
                $instal = Installment::where(['loan_id' => $lo['id'], 'current' => true])->first();
                return $instal->due_date;
            })
            ->addColumn('principal_paid', function ($lo) {
                $instals = Installment::where(['loan_id' => $lo['id']])->get();
                $ppaid = 0;
                foreach ($instals as $instal) {
                    if ($instal->amount_paid >= $instal->principal_amount) {
                        $ppaid += $instal->principal_amount;
                    } else {
                        $ppaid += $instal->amount_paid;
                    }

                }
                return $ppaid;

            })
            ->addColumn('principal_due', function ($lo) {
                $instals = Installment::where(['loan_id' => $lo['id']])->get();
                $ppaid = 0;
                foreach ($instals as $instal) {
                    if ($instal->amount_paid >= $instal->principal_amount) {
                        $ppaid += $instal->principal_amount;
                    } else {
                        $ppaid += $instal->amount_paid;
                    }

                }
                $pdue = $lo['loan_amount'] - $ppaid;
                return $pdue;

            })
            ->addColumn('interest_paid', function ($lo) {
                $instals = Installment::where(['loan_id' => $lo['id']])->get();
                $Ipaid = 0;
                foreach ($instals as $instal) {
                    if ($instal->amount_paid > $instal->principal_amount) {
                        $iP = $instal->amount_paid - $instal->principal_amount;
                        $Ipaid += $iP;
                    }

                }
                return $Ipaid;

            })
            ->addColumn('interest_due', function ($lo) {
                $instals = Installment::where(['loan_id' => $lo['id']])->get();
                $Ipaid = 0;
                foreach ($instals as $instal) {
                    if ($instal->amount_paid > $instal->principal_amount) {
                        $Ipaid += $instal->amount_paid - $instal->principal_amount;
                    }

                }

                $product = Product::find($lo['product_id']);
                $loan_total_interest = $lo['loan_amount'] * ($product->interest / 100);

                return $loan_total_interest - $Ipaid;

            })
            ->addColumn('overdue', function ($lo) {
                $instal = Arrear::where(['loan_id' => $lo['id'], ['amount', '>', 0]])->first();
                $inst = Installment::find($instal->installment_id);

                $created = Carbon::parse($inst->due_date);
                $overdue_days = $created->diffInDays(Carbon::now());
                return $overdue_days;

            })
            ->addColumn('field_agent', function ($lo) {
                $Customer = Customer::find($lo['customer_id']);
                $user = User::find($Customer->field_agent_id);

                return $user->name;

            })
            ->addColumn('amount_paid', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo['id'], 'payment_type_id' => 1])->sum('amount');


                return $payments;


            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo['product_id']);
                if ($lo['rolled_over']) {
                    $rollover = Rollover::where('loan_id', $lo['id'])->first();
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100));

                }

                return $total;


            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo['id'], 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo['product_id']);
                if ($lo['rolled_over']) {
                    $rollover = Rollover::where('loan_id', $lo['id'])->first();
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100));


                }
                $balance = $total - $payments;


                return $balance;


            })
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo['customer_id']);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('total_arrears', function ($lo) {
                $amount = 0;
                $arrears = Arrear::where('loan_id', $lo['id'])->get();


                if ($arrears->first()) {

                    foreach ($arrears as $arrear) {
                        $amount += $arrear->amount;

                    }
                }

                return $amount;
            })
            ->addColumn('principal_arrears', function ($lo) {
                $amount = 0;
                $arrears = Arrear::where('loan_id', $lo['id'])->get();


                if ($arrears->first()) {

                    foreach ($arrears as $arrear) {
                        $instal = Installment::find($arrear->installment_id);
                        if ($instal) {
                            $bal = $instal->principal_amount - $instal->amount_paid;
                            $amount += $bal;


                        }

                    }
                }

                return $amount;
            })
            ->addColumn('interest_arrears', function ($lo) {
                $amount = 0;
                $arrears = Arrear::where('loan_id', $lo['id'])->get();


                if ($arrears->first()) {

                    foreach ($arrears as $arrear) {
                        $instal = Installment::find($arrear->installment_id);
                        if ($instal) {

                            if ($instal->amount_paid > $instal->principal_amount) {
                                $Ipaid = $instal->amount_paid - $instal->principal_amount;
                            } else {
                                $Ipaid = 0;
                            }
                            $bal = $instal->interest - $Ipaid;
                            $amount += $bal;


                        }

                    }
                }

                return $amount;
            })
            ->addColumn('elapsed_schedule', function ($lo) {
                if ($lo['disbursed']) {
                    $inst = Installment::where(['current' => true, 'loan_id' => $lo['id']])->get();
                    if ($inst->first()) {

                        return $inst->first()->position - 1;
                    } else {
                        return $inst->count();

                    }


                }
            })
            ->rawColumns(['owner'])
            ->toJson();

    }

    public function group_loan_skipped_payments()
    {
        $this->data['title'] = "Group Lending: Loan Arrears Skipped Payments Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of Loan Arrears with skipped payments";
            $this->data['groups'] = Group::all();
            //$this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "List of Group Lending Loan Arrears with skipped payments in " . $branch->bname;
            $this->data['groups'] = Group::where('branch_id', $branch->id)->get();
            //$this->data['lfs'] = User::role('field_agent')->where('status', true)->where('branch_id', $branch->id)->get();
        }
        return view('pages.reports.group-reports.loan_skipped_payments', $this->data);
    }

    public function group_loan_skipped_payments_data(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');
        $start = $request->start_date;
        $end = $request->end_date;

        if ($request->group != 'all') {
            if ($start != $end) {
                $loans_w_arrears = Loan::where('group_id', '=', $request->group)->whereHas('arrears', function (Builder $builder) {
                    $builder->where('amount', '!=', 0);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over' => false])->whereBetween('disbursement_date', [$start, $end])->get();
            } else {
                $loans_w_arrears = Loan::where('group_id', '=', $request->group)->whereHas('arrears', function (Builder $builder) {
                    $builder->where('amount', '!=', 0);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over' => false])->get();
            }

            $lo = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                $product = Product::find($lns->product_id);
                $skipped_installments = Installment::where(['loan_id' => $lns->id, 'completed' => false])->where('due_date', '<', $today)->get();
                $skipped_installments_count = count($skipped_installments);
                if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                    if ($skipped_installments_count > 0) {
                        array_push($lo, array(
                            'id' => $lns->id,
                            'loan_account' => $lns->loan_account,
                            'loan_amount' => $lns->loan_amount,
                            'product_id' => $lns->product_id,
                            'customer_id' => $lns->customer_id,
                            'group_id' => $lns->group_id,
                            'date_created' => $lns->date_created,
                            'end_date' => $lns->end_date,
                            'approved' => $lns->approved,
                            'approved_date' => $lns->approved_date,
                            'disbursed' => $lns->disbursed,
                            'disbursement_date' => $lns->disbursement_date,
                            'created_at' => $lns->created_at,
                            'updated_at' => $lns->updated_at,
                            'last_payment_date' => $last_payment_date,
                            'purpose' => $lns->purpose,
                            'settled' => $lns->settled,
                            'rolled_over' => $lns->rolled_over,
                            'approved_by' => $lns->approved_by,
                            'disbursed_by' => $lns->disbursed_by,
                            'phone' => $lns->customer->phone,
                            'product_name' => $product->product_name,
                            'installments' => $product->installments,
                            'interest' => $product->interest,
                            'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                            'skipped_installments' => $skipped_installments_count,
                            'skipped_installments_obj' => $skipped_installments
                        ));
                    }
                }
            }
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            if ($start != $end) {
                $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                    $builder->where('amount', '!=', 0);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over' => false])->whereBetween('disbursement_date', [$start, $end])->get();
            } else {
                $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                    $builder->where('amount', '!=', 0);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over' => false])->get();
            }
            $lo = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                $product = Product::find($lns->product_id);
                $skipped_installments = Installment::where(['loan_id' => $lns->id, 'completed' => false])->where('due_date', '<', $today)->get();
                $skipped_installments_count = count($skipped_installments);
                if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                    if ($skipped_installments_count > 0) {
                        array_push($lo, array(
                            'id' => $lns->id,
                            'loan_account' => $lns->loan_account,
                            'loan_amount' => $lns->loan_amount,
                            'product_id' => $lns->product_id,
                            'customer_id' => $lns->customer_id,
                            'group_id' => $lns->group_id,
                            'date_created' => $lns->date_created,
                            'end_date' => $lns->end_date,
                            'approved' => $lns->approved,
                            'approved_date' => $lns->approved_date,
                            'disbursed' => $lns->disbursed,
                            'disbursement_date' => $lns->disbursement_date,
                            'created_at' => $lns->created_at,
                            'updated_at' => $lns->updated_at,
                            'last_payment_date' => $last_payment_date,
                            'purpose' => $lns->purpose,
                            'settled' => $lns->settled,
                            'rolled_over' => $lns->rolled_over,
                            'approved_by' => $lns->approved_by,
                            'disbursed_by' => $lns->disbursed_by,
                            'phone' => $lns->customer->phone,
                            'product_name' => $product->product_name,
                            'installments' => $product->installments,
                            'interest' => $product->interest,
                            'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                            'skipped_installments' => $skipped_installments_count,
                            'skipped_installments_obj' => $skipped_installments
                        ));
                    }
                }
            }
        } elseif (Auth::user()->hasRole('field_agent')) {
            if ($start != $end) {
                $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                    $builder->where('amount', '!=', 0);
                })->whereHas('customer', function (Builder $builder) use ($request) {
                    $builder->where('field_agent_id', '=', Auth::user()->id);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over' => false])
                    ->whereBetween('disbursement_date', [$start, $end])->get();
            } else {
                $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                    $builder->where('amount', '!=', 0);
                })->whereHas('customer', function (Builder $builder) use ($request) {
                    $builder->where('field_agent_id', '=', Auth::user()->id);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over' => false])->get();
            }

            $lo = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                $product = Product::find($lns->product_id);
                $skipped_installments = Installment::where(['loan_id' => $lns->id, 'completed' => false])->where('due_date', '<', $today)->get();
                $skipped_installments_count = count($skipped_installments);
                if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                    if ($skipped_installments_count > 0) {
                        array_push($lo, array(
                            'id' => $lns->id,
                            'loan_account' => $lns->loan_account,
                            'loan_amount' => $lns->loan_amount,
                            'product_id' => $lns->product_id,
                            'customer_id' => $lns->customer_id,
                            'group_id' => $lns->group_id,
                            'date_created' => $lns->date_created,
                            'end_date' => $lns->end_date,
                            'approved' => $lns->approved,
                            'approved_date' => $lns->approved_date,
                            'disbursed' => $lns->disbursed,
                            'disbursement_date' => $lns->disbursement_date,
                            'created_at' => $lns->created_at,
                            'updated_at' => $lns->updated_at,
                            'last_payment_date' => $last_payment_date,
                            'purpose' => $lns->purpose,
                            'settled' => $lns->settled,
                            'rolled_over' => $lns->rolled_over,
                            'approved_by' => $lns->approved_by,
                            'disbursed_by' => $lns->disbursed_by,
                            'phone' => $lns->customer->phone,
                            'product_name' => $product->product_name,
                            'installments' => $product->installments,
                            'interest' => $product->interest,
                            'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                            'skipped_installments' => $skipped_installments_count,
                            'skipped_installments_obj' => $skipped_installments
                        ));
                    }
                }
            }
        } else {
            if ($start != $end) {
                $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                    $builder->where('amount', '!=', 0);
                })->whereHas('customer', function (Builder $builder) use ($request) {
                    $builder->where('branch_id', '=', Auth::user()->branch_id);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over' => false])
                    ->whereBetween('disbursement_date', [$start, $end])->get();
            } else {
                $loans_w_arrears = Loan::where('group_id', '!=', null)->whereHas('arrears', function (Builder $builder) {
                    $builder->where('amount', '!=', 0);
                })->whereHas('customer', function (Builder $builder) use ($request) {
                    $builder->where('branch_id', '=', Auth::user()->branch_id);
                })->where(['disbursed' => true, 'settled' => false, 'rolled_over' => false])->get();
            }

            $lo = array();
            foreach ($loans_w_arrears as $lns) {
                $last_payment_date = $lns->last_payment_date;
                $product = Product::find($lns->product_id);
                $skipped_installments = Installment::where(['loan_id' => $lns->id, 'completed' => false])->where('due_date', '<', $today)->get();
                $skipped_installments_count = count($skipped_installments);
                if (/*$last_payment_date == null ||*/ $last_payment_date > Carbon::now()->subDays(180)) {
                    if ($skipped_installments_count > 0) {
                        array_push($lo, array(
                            'id' => $lns->id,
                            'loan_account' => $lns->loan_account,
                            'loan_amount' => $lns->loan_amount,
                            'product_id' => $lns->product_id,
                            'customer_id' => $lns->customer_id,
                            'group_id' => $lns->group_id,
                            'date_created' => $lns->date_created,
                            'end_date' => $lns->end_date,
                            'approved' => $lns->approved,
                            'approved_date' => $lns->approved_date,
                            'disbursed' => $lns->disbursed,
                            'disbursement_date' => $lns->disbursement_date,
                            'created_at' => $lns->created_at,
                            'updated_at' => $lns->updated_at,
                            'last_payment_date' => $last_payment_date,
                            'purpose' => $lns->purpose,
                            'settled' => $lns->settled,
                            'rolled_over' => $lns->rolled_over,
                            'approved_by' => $lns->approved_by,
                            'disbursed_by' => $lns->disbursed_by,
                            'phone' => $lns->customer->phone,
                            'product_name' => $product->product_name,
                            'installments' => $product->installments,
                            'interest' => $product->interest,
                            'owner' => $lns->customer->fname . " " . $lns->customer->lname,
                            'skipped_installments' => $skipped_installments_count,
                            'skipped_installments_obj' => $skipped_installments
                        ));
                    }
                }
            }
        }

        return Datatables::of($lo)
            ->editColumn('owner', function ($lo) {
                $customer = Customer::find($lo['customer_id']);
                $group = Group::find($lo['group_id']);
                if ($group) {
                    $group = $customer->group()->first();
                    $group_name = $group->name;

                    return $customer->fname . ' ' . $customer->lname . '<br>' . '<span class="badge badge-primary" style="font-size: small">' . $group_name . '</span>';
                } else {
                    return $customer->fname . ' ' . $customer->lname;
                }
            })
            ->addColumn('next_payment_date', function ($lo) {
                $instal = Installment::where(['loan_id' => $lo['id'], 'current' => true])->first();
                return $instal->due_date;
            })
            ->addColumn('principal_paid', function ($lo) {
                $instals = Installment::where(['loan_id' => $lo['id']])->get();
                $ppaid = 0;
                foreach ($instals as $instal) {
                    if ($instal->amount_paid >= $instal->principal_amount) {
                        $ppaid += $instal->principal_amount;
                    } else {
                        $ppaid += $instal->amount_paid;
                    }

                }
                return $ppaid;

            })
            ->addColumn('principal_due', function ($lo) {
                $instals = Installment::where(['loan_id' => $lo['id']])->get();
                $ppaid = 0;
                foreach ($instals as $instal) {
                    if ($instal->amount_paid >= $instal->principal_amount) {
                        $ppaid += $instal->principal_amount;
                    } else {
                        $ppaid += $instal->amount_paid;
                    }

                }
                $pdue = $lo['loan_amount'] - $ppaid;
                return $pdue;

            })
            ->addColumn('interest_paid', function ($lo) {
                $instals = Installment::where(['loan_id' => $lo['id']])->get();
                $Ipaid = 0;
                foreach ($instals as $instal) {
                    if ($instal->amount_paid > $instal->principal_amount) {
                        $iP = $instal->amount_paid - $instal->principal_amount;
                        $Ipaid += $iP;
                    }

                }
                return $Ipaid;

            })
            ->addColumn('interest_due', function ($lo) {
                $instals = Installment::where(['loan_id' => $lo['id']])->get();
                $Ipaid = 0;
                foreach ($instals as $instal) {
                    if ($instal->amount_paid > $instal->principal_amount) {
                        $Ipaid += $instal->amount_paid - $instal->principal_amount;
                    }
                }
                $product = Product::find($lo['product_id']);
                $loan_total_interest = $lo['loan_amount'] * ($product->interest / 100);
                return $loan_total_interest - $Ipaid;
            })
            ->addColumn('overdue', function ($lo) {
                $instal = Arrear::where(['loan_id' => $lo['id'], ['amount', '>', 0]])->first();
                $inst = Installment::find($instal->installment_id);
                $created = Carbon::parse($inst->due_date);
                $overdue_days = $created->diffInDays(Carbon::now());
                return $overdue_days;
            })
            ->addColumn('field_agent', function ($lo) {
                $Customer = Customer::find($lo['customer_id']);
                $user = User::find($Customer->field_agent_id);
                return $user->name;
            })
            ->addColumn('amount_paid', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo['id'], 'payment_type_id' => 1])->sum('amount');
                return $payments;
            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo['product_id']);
                if ($lo['rolled_over']) {
                    $rollover = Rollover::where('loan_id', $lo['id'])->first();
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100));

                }

                return $total;


            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo['id'], 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo['product_id']);
                if ($lo['rolled_over']) {
                    $rollover = Rollover::where('loan_id', $lo['id'])->first();
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo['loan_amount'] + ($lo['loan_amount'] * ($product->interest / 100));


                }
                $balance = $total - $payments;


                return $balance;


            })
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo['customer_id']);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('total_arrears', function ($lo) {
                $amount = 0;
                $arrears = Arrear::where('loan_id', $lo['id'])->get();


                if ($arrears->first()) {

                    foreach ($arrears as $arrear) {
                        $amount += $arrear->amount;

                    }
                }

                return $amount;
            })
            ->addColumn('principal_arrears', function ($lo) {
                $amount = 0;
                $arrears = Arrear::where('loan_id', $lo['id'])->get();


                if ($arrears->first()) {

                    foreach ($arrears as $arrear) {
                        $instal = Installment::find($arrear->installment_id);
                        if ($instal) {
                            $bal = $instal->principal_amount - $instal->amount_paid;
                            $amount += $bal;


                        }

                    }
                }

                return $amount;
            })
            ->addColumn('interest_arrears', function ($lo) {
                $amount = 0;
                $arrears = Arrear::where('loan_id', $lo['id'])->get();


                if ($arrears->first()) {

                    foreach ($arrears as $arrear) {
                        $instal = Installment::find($arrear->installment_id);
                        if ($instal) {

                            if ($instal->amount_paid > $instal->principal_amount) {
                                $Ipaid = $instal->amount_paid - $instal->principal_amount;
                            } else {
                                $Ipaid = 0;
                            }
                            $bal = $instal->interest - $Ipaid;
                            $amount += $bal;


                        }

                    }
                }

                return $amount;
            })
            ->rawColumns(['owner'])
            ->toJson();
    }

    public function group_disbursed_loans()
    {
        $this->data['title'] = "Group Lending: Disbursed Loans";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['credit_officers'] = User::role('field_agent')->where('status', 1)->get();
        $user = \auth()->user();
        $branch = Branch::find($user->branch_id);


        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of All Disbursed Group Loans";
        } elseif ($user->hasRole('field_agent')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $this->data['credit_officers'] = User::role('field_agent')->where(['status' => true, 'id' => $user->id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        } elseif ($user->hasRole('collection_officer')) {
            $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $user->branch_id])->get();

            $this->data['credit_officers'] = User::role('field_agent')->where(['status' => true, 'id' => $user->field_agent_id])->get();
            $this->data['sub_title'] = "List of All Loan Arrears in " . $branch->bname;
        } else {
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();
            $this->data['sub_title'] = "List of All Disbursed Loans in " . $branch->bname;
        }
        return view('pages.reports.group-reports.disbursed_loans', $this->data);
    }

    public function group_disbursed_loans_data(Request $request)
    {
        if ($request->start_date) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.approved', true],
                    ['loans.disbursed', true],
                    ['loans.group_id', '!=', null],
                    ['customers.branch_id', '=', $request->branch]
                ])
                ->whereBetween('disbursement_date', [$request->start_date, $request->end_date]);
//            }
//            elseif($request->co_id != null){
//                $lo = DB::table('loans')
//                    ->join('customers', 'customers.id', '=', 'loans.customer_id')
//                    ->join('products', 'products.id', '=', 'loans.product_id')
//                    ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
//                    ->where([
//                        ['loans.approved', true],
//                        ['loans.disbursed', true],
//                        ['customers.field_agent_id', '=', $request->co_id]
//                    ])
//                    ->whereBetween('disbursement_date', [$request->start_date, $request->end_date]);
//            }
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            // $lo = Loan::where(['approved' => true, 'disbursed' => true])->get();
            //  $los = Loan::select('*')->where(['approved' => true, 'disbursed' => true]);
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.approved', true],
                    ['loans.disbursed', true],
                    ['loans.group_id', '!=', null],
                ]);


        } elseif (Auth::user()->hasRole('field_agent')) {
            /*$branch = Auth::user();
            $los = $branch->loans()->where(['approved' => true, 'disbursed' => true])->get();*/
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.approved', true],
                    ['loans.disbursed', true],
                    ['loans.group_id', '!=', null],
                    ['customers.field_agent_id', '=', Auth::user()->id]

                ]);
        } elseif (Auth::user()->hasRole('collection_officer')) {
            /*$branch = Auth::user();
            $los = $branch->loans()->where(['approved' => true, 'disbursed' => true])->get();*/
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.approved', true],
                    ['loans.disbursed', true],
                    ['loans.group_id', '!=', null],
                    ['customers.field_agent_id', '=', Auth::user()->field_agent_id]

                ]);
        } else {
            /*$branch = Branch::where('id', Auth::user()->branch_id)->first();
            $lo = $branch->loans()->where(['approved' => true, 'disbursed' => true])->get();*/
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.approved', true],
                    ['loans.disbursed', true],
                    ['loans.group_id', '!=', null],
                    ['customers.branch_id', '=', Auth::user()->branch_id]

                ]);


        }


        //return Datatables::of($lo)

        // return DT::eloquent($los)
        return Datatables::of($lo)
            ->editColumn('owner', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                $group = Group::find($lo->group_id);
                if ($group) {
                    $group = $customer->group()->first();
                    $group_name = $group->name;

                    return $customer->fname . ' ' . $customer->lname . '<br>' . '<span class="badge badge-primary" style="font-size: small">' . $group_name . '</span>';
                } else {
                    return $customer->fname . ' ' . $customer->lname;
                }
            })
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'Yes';
                } else {
                    return 'NO';
                }


            })
            ->addColumn('credit_officer', function ($lo) {
                $cust = Customer::find($lo->customer_id);
                $field_agent = User::find($cust->field_agent_id);
                return $field_agent->name;
            })
            ->addColumn('amount_paid', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');


                return $payments;


            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));

                }

                return $total;


            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));


                }
                $balance = $total - $payments;


                return $balance;


            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);


                return '<a href="' . route('loans.post_disburse', ['id' => $data]) . '"    class="sel-btn btn btn-xs btn-primary"><i class="feather icon-eye text-info"></i> Disburse</a>';
                /* return '<div class="btn-group text-center">
                                                 <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                         <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                             <li><a href="' . route('loans.post_disburse', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Disburse</a></li>
                                                            <li><a href="' . route('loans.destroy', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>


                                                         </ul>
                                         </div>';*/
            })
            ->addColumn('checkbox', function ($lo) {
                return '<input type="checkbox" name="id" value="' . $lo->id . '" id="' . $lo->id . '">';


            })
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->rawColumns(['owner', 'action', 'checkbox'])
            ->toJson();

    }

    public function group_scoring()
    {
        $this->data['title'] = "Group Scoring Report";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "Group Lending Scoring Overview";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['sub_title'] = "Group Scoring Report in" . $branch->bname;
        }
        return view('pages.reports.group-reports.group_scoring', $this->data);

    }

    public function group_scoring_data()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $groups = Group::where('approved', true)->select("*");

        } elseif (Auth::user()->hasRole('field_agent')) {
            $groups = Group::where('approved', true)->where('field_agent_id', Auth::id())->select("*");
        } else {
            $groups = Group::where('approved', true)->where('branch_id', Auth::user()->branch_id)->select("*");
        }

        return DT::eloquent($groups)
            ->addColumn('members', function (Group $group) {
                return $group->customers()->count();
            })
            ->addColumn('branchName', function (Group $group) {
                return $group->branch()->first()->bname;
            })
            ->addColumn('loanOfficer', function (Group $group) {
                return $group->field_agent()->first()->name;
            })
            ->addColumn('group_leader', function (Group $group) {
                $name = $group->leader()->first()->fname . ' ' . $group->leader()->first()->lname;
                $phone = $group->leader()->first()->phone;
                return $name . '<br>' . '- ' . $phone;
            })
            ->addColumn('groupCreatedDate', function (Group $group) {
                $dateTime = Carbon::parse($group->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('totalNumberOfLoans', function (Group $group) {
                return $group->loans()->count();
            })
            ->addColumn('paid_loans', function (Group $group) {
                return $group->loans()->where('settled', true)->count();
            })
            ->addColumn('loansArrear', function (Group $group) {
                return $group->loans()->whereHas('arrears', function ($q) {
                    $q->where('amount', '>', 0);
                })->count();
            })
            ->addColumn('loansWithoutArrear', function (Group $group) {
                $paidwitharrears = $group->loans()->where('settled', true)->whereHas('installments', function ($q) {
                    $q->where('in_arrear', true);
                })->count();
                $paid = $group->loans()->where('settled', true)->count();
                $withoutarrears = $paid - $paidwitharrears;
                return $withoutarrears;
            })
            ->addColumn('perOfLoanwithoutArrears', function (Group $group) {
                $paidwitharrears = $group->loans()->whereHas('installments', function ($q) {
                    $q->where('in_arrear', true);
                })->count();
                $all = $group->loans()->where('disbursed', '=', true)->count();
                $withoutarrears = $all - $paidwitharrears;

                if ($all > 0) {
                    $perc = round(($withoutarrears / $all) * 100, 2);
                } else {
                    $perc = 0;
                }

                return $perc;
            })
            ->addColumn('skippedDuePayments', function (Group $group) {
                $instal = Installment::where('in_arrear', true)->whereIn('loan_id', $group->loans()->pluck('id'))->count();
                return $instal;
            })
            ->rawColumns(['group_leader'])
            ->toJson();
    }

    public function group_loans_balance()
    {
        $this->data['title'] = "Group Lending: Loan Balances Report";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of Group Lending Loan Balances";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();
            $this->data['lfs'] = User::role('field_agent')->where('branch_id', Auth::user()->branch_id)->get();

            $this->data['sub_title'] = "List of Group Loan Balances in " . $branch->bname;
        }
        return view('pages.reports.group-reports.loan_balances', $this->data);
    }

    public function group_loans_balance_data(Request $request)
    {
        if ($request->lf) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['loans.group_id', '!=', null],
                    ['customers.field_agent_id', '=', $request->lf],

                    ['customers.branch_id', '=', $request->branch]
                ]);
        } elseif ($request->branch) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['loans.group_id', '!=', null],
                    ['customers.branch_id', '=', $request->branch]
                ]);
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            //$los = Loan::select('*')->where(['disbursed' => true, 'settled' => false])/*->get()*/;
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['loans.group_id', '!=', null],
                ]);

        } elseif (Auth::user()->hasRole('field_agent')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['loans.group_id', '!=', null],
                    ['customers.field_agent_id', '=', Auth::user()->id]
                ]);
        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([
                    ['loans.disbursed', '=', true],
                    ['loans.settled', '=', false],
                    ['loans.group_id', '!=', null],
                    ['customers.branch_id', '=', Auth::user()->branch_id]
                ]);
        }

        return Datatables::of($lo)
            ->editColumn('owner', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                $group = Group::find($lo->group_id);
                if ($group) {
                    $group = $customer->group()->first();
                    $group_name = $group->name;

                    return $customer->fname . ' ' . $customer->lname . '<br>' . '<span class="badge badge-primary" style="font-size: small">' . $group_name . '</span>';
                } else {
                    return $customer->fname . ' ' . $customer->lname;
                }
            })
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('amount_paid', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                return $payments;
            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
                }
                return $total;
            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));


                }
                $balance = $total - $payments;


                return $balance;


            })
            ->addColumn('field_agent', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                $user = User::find($Customer->field_agent_id);
                return $user->name;
            })
            ->addColumn('percentage_paid', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
                }
                $percentage_paid = ($payments / $total) * 100;
                return number_format($percentage_paid, 2);
            })
            ->rawColumns(['owner'])
            ->toJson();
    }

    /**
     * Default analysis report
     * Installments that were supposed to be paid in a certain month but was not paid
     *
     * */


    public function default_analysis_report()
    {
        $this->data['title'] = "Default Analysis Report";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
        $months = [
            /* [0, "All"],*/
            [1, "JAN"], [2, "FEB"], [3, "MAR"], [4, "APR"], [5, "MAY"], [6, "JUN"], [7, "JUL"], [8, "AUG"], [9, "SEP"], [10, "OCT"], [11, "NOV"], [12, "DEC"]
        ];
        $years = [];
        for ($i = 2018; $i <= now()->format('Y'); $i++) {
            $years[] = $i;
        }
        $this->data['cur_month'] = Carbon::now()->format('m');
        $this->data['months'] = $months;
        $this->data['years'] = $years;


        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {
            $this->data['sub_title'] = "List of Defaulted Loans";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['branches'] = Branch::where('id', Auth::user()->branch_id)->get();
            $this->data['lfs'] = User::role('field_agent')->where('branch_id', Auth::user()->branch_id)->get();

            $this->data['sub_title'] = "List of defaulted loans in " . $branch->bname;
        }
        return view('pages.reports.default_analysis', $this->data);
    }

    public function default_analysis_report_data(Request $request)
    {

        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');


        if ($request->lf != 'all' ){
            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true, 'settled' => false])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)
                ->whereDate('end_date', '<', Carbon::now())

                //                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('arrears')
//                        ->whereColumn('arrears.loan_id', 'loans.id') // Proper correlation
//                        ->where('arrears.amount', '!=', 0);
//                })
//                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('installments')
//                        ->whereColumn('installments.loan_id', 'loans.id') // Proper correlation
//                        ->whereMonth('installments.due_date', $request->month)
//                        ->whereYear('installments.due_date', $request->year)
//                        ->whereColumn('installments.total', '>', 'installments.amount_paid');
//                })


            ->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))
                    ->from('customers')
                    ->whereColumn('loans.customer_id', 'customers.id') // Proper correlation
                    ->where(['customers.field_agent_id' => $request->lf]);

            })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'loans.customer_id');

                })
                ->select('loans.*', 'customers.phone');
        }


        elseif ($request->lf == 'all' and $request->branch != 'all') {
                $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true, 'settled' => false])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)
                    ->whereDate('end_date', '<', Carbon::now())

                    //                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('arrears')
//                        ->whereColumn('arrears.loan_id', 'loans.id') // Proper correlation
//                        ->where('arrears.amount', '!=', 0);
//                })
//                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('installments')
//                        ->whereColumn('installments.loan_id', 'loans.id') // Proper correlation
//                        ->whereMonth('installments.due_date', $request->month)
//                        ->whereYear('installments.due_date', $request->year)
//                        ->whereColumn('installments.total', '>', 'installments.amount_paid');
//                })
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.id', 'loans.customer_id') // Proper correlation
                        ->where(['customers.branch_id' => $request->branch]);

                })
                    ->join('customers', function ($join) {
                        $join->on('customers.id', '=', 'loans.customer_id');

                    })
                    ->select('loans.*', 'customers.phone');
        }
        /*elseif ($request->lf != 'all' and $request->branch == 'all') {

            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true, 'settled' => false])
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('arrears')
                        ->whereColumn('arrears.loan_id', 'loans.id') // Proper correlation
                        ->where('arrears.amount', '!=', 0);
                })
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('installments')
                        ->whereColumn('installments.loan_id', 'loans.id') // Proper correlation
                        ->whereMonth('installments.due_date', $request->month)
                        ->whereYear('installments.due_date', $request->year)
                        ->whereColumn('installments.total', '>', 'installments.amount_paid');
                })
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.id', 'loans.customer_id') // Proper correlation
                        ->where(['customers.field_agent_id' => $request->lf]);

                });
        }
        */
        elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {

            /*$loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true, 'settled' => false])
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('arrears')
                        ->whereColumn('arrears.loan_id', 'loans.id') // Proper correlation
                        ->where('arrears.amount', '!=', 0);
                })
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('installments')
                        ->whereColumn('installments.loan_id', 'loans.id') // Proper correlation
                        ->whereMonth('installments.due_date', $request->month)
                        ->whereYear('installments.due_date', $request->year)
                        ->whereColumn('installments.total', '>', 'installments.amount_paid');
                });*/

            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true, 'settled' => false])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)
                ->whereDate('end_date', '<', Carbon::now())
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'loans.customer_id');

                })
            ->select('loans.*', 'customers.phone');
           // dd($loans_w_arrears->get());




        } elseif (Auth::user()->hasRole('field_agent')) {


            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true, 'settled' => false])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)
                ->whereDate('end_date', '<', Carbon::now())

//                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('arrears')
//                        ->whereColumn('arrears.loan_id', 'loans.id') // Proper correlation
//                        ->where('arrears.amount', '!=', 0);
//                })
//                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('installments')
//                        ->whereColumn('installments.loan_id', 'loans.id') // Proper correlation
//                        ->whereMonth('installments.due_date', $request->month)
//                        ->whereYear('installments.due_date', $request->year)
//                        ->whereColumn('installments.total', '>', 'installments.amount_paid');
//                })
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.customer_id', 'loans.id') // Proper correlation
                        ->where(['customers.field_agent_id' => Auth::user()->id]);

                })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'loans.customer_id');

                })
                ->select('loans.*', 'customers.phone');
        } elseif (Auth::user()->hasRole('collection_officer')) {

            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true, 'settled' => false])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)
                ->whereDate('end_date', '<', Carbon::now())

//                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('arrears')
//                        ->whereColumn('arrears.loan_id', 'loans.id') // Proper correlation
//                        ->where('arrears.amount', '!=', 0);
//                })
//                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('installments')
//                        ->whereColumn('installments.loan_id', 'loans.id') // Proper correlation
//                        ->whereMonth('installments.due_date', $request->month)
//                        ->whereYear('installments.due_date', $request->year)
//                        ->whereColumn('installments.total', '>', 'installments.amount_paid');
//                })
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.id', 'loans.customer_id') // Proper correlation
                        ->where(['customers.field_agent_id' => Auth::user()->field_agent_id]);

                })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'loans.customer_id');

                })
                ->select('loans.*', 'customers.phone');

        } else {

            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true, 'settled' => false])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)
                ->whereDate('end_date', '<', Carbon::now())
//                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('arrears')
//                        ->whereColumn('arrears.loan_id', 'loans.id') // Proper correlation
//                        ->where('arrears.amount', '!=', 0);
//                })
//                ->whereExists(function ($query) use ($request) {
//                    $query->select(DB::raw(1))
//                        ->from('installments')
//                        ->whereColumn('installments.loan_id', 'loans.id') // Proper correlation
//                        ->whereMonth('installments.due_date', $request->month)
//                        ->whereYear('installments.due_date', $request->year)
//                        ->whereColumn('installments.total', '>', 'installments.amount_paid');
//                })
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.id', 'loans.customer_id') // Proper correlation
                        ->where(['customers.branch_id' => Auth::user()->branch_id]);

                })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'loans.customer_id');

                })
                ->select('loans.*', 'customers.phone');
        }
        if ($request->ajax) {
            $total_paid = 0;
            $disbursed_amount = 0;
            $total_amount = 0;

            foreach ($loans_w_arrears->get() as $lo) {
                //dd($lo);
                $installment = DB::table('installments')->where(['loan_id' => $lo->id])->whereMonth('due_date', $request->month)->whereYear('due_date', $request->year)->whereDate('due_date', '<', Carbon::now())->get();
                //$installment = DB::table('installments')->where(['loan_id' => $lo->id])->get();
                $disbursed_amount += $lo->loan_amount;
                $due_amount = $installment->sum('total');

                $paid_amount = $installment->sum('amount_paid');



                //$month_def_amount = $due_amount - $paid_amount;

                $total_paid += $paid_amount;
                $total_amount += $due_amount;

            }
            $monthly_defaulted_amount = $total_amount - $total_paid;



            return [
                'monthly_due_amount' => number_format($total_amount),
                'monthly_paid_amount'=>number_format($total_paid),
                'monthly_defaulted_amount'=>number_format($monthly_defaulted_amount),

            ];

        }


        return Datatables::of($loans_w_arrears)

            ->addColumn('product_name', function ($lo) {
                $product = DB::table('products')->where(['id' => $lo->product_id])->first();

                return $product->product_name;

            })
            ->addColumn('phone', function ($lo) {
                $Customer = Customer::find($lo->customer_id);

                return $Customer->phone;

            })
//            ->filterColumn('phone', function ($query, $keyword){
//                $sql = "phone like ?";
//                $query->whereRaw($sql, ["%{$keyword}%"]);
//            })

            ->addColumn('owner', function ($lo) {
                $Customer = Customer::find($lo->customer_id);

                return $Customer->fullName;

            })
            ->addColumn('field_agent', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                $user = User::find($Customer->field_agent_id);

                return $user->name;

            })
            ->addColumn('amount_paid', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');


                return $payments;


            })

            ->addColumn('balance', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

               // $product = Product::find($lo->product_id);
//                if ($lo->rolled_over) {
//                    $rollover = Rollover::where('loan_id', $lo->id)->first();
//                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
//                } else {
//                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));
//
//
//                }
                $balance = $lo->total_amount - $payments;


                return $balance;


            })
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
//            ->addColumn('month_due_amount', function ($lo) use ($request) {
//
//                $installment = DB::table('installments')->where(['loan_id' => $lo->id])->whereMonth('due_date', $request->month)->whereYear('due_date', $request->year)->whereDate('due_date', '<', Carbon::now())->get();
//                $due_amount = $installment->sum('total');
//
//
//                return $due_amount;
//            })
//            ->addColumn('amount_paid', function ($lo) use ($request) {
//
//                $installment = DB::table('installments')->where(['loan_id' => $lo->id])->whereMonth('due_date', $request->month)->whereYear('due_date', $request->year)->whereDate('due_date', '<', Carbon::now())->get();
//                return $installment->sum('amount_paid');
//            })
            /*->addColumn('defaulted_amount', function ($lo) use ($request) {

                //$installment = DB::table('installments')->where(['loan_id' => $lo->id])->whereMonth('due_date', $request->month)->whereYear('due_date', $request->year)->whereDate('due_date', '<', Carbon::now())->get();
                $installment = DB::table('installments')->where(['loan_id' => $lo->id])->get();

                $loan_total = $lo->total_amount;
                $paid_amount = $installment->sum('amount_paid');


                return $loan_total - $paid_amount;
            })*/
            ->addColumn('action', function ($lo) {

                return '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-settings"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" x-placement="top-end" style="position: absolute; transform: translate3d(87px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                           <a class="dropdown-item" href="' . route('customer-interactions.list_customer_interactions', $lo->customer_id) . '"><i class="feather icon-clock"></i> View History</a>
                            ';
            })
            ->rawColumns(['action'])
            ->toJson();


    }

    public function default_ajax(Request $request)
    {
        if ($request->lf != 'all' ){
            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('loans.customer_id', 'customers.id') // Proper correlation
                        ->where(['customers.field_agent_id' => $request->lf]);

                });
        }


        elseif ($request->lf == 'all' and $request->branch != 'all') {
            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)


                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.id', 'loans.customer_id') // Proper correlation
                        ->where(['customers.branch_id' => $request->branch]);

                });
        }
        elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager')) {


            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year);




        } elseif (Auth::user()->hasRole('field_agent')) {


            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)

                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.customer_id', 'loans.id') // Proper correlation
                        ->where(['customers.field_agent_id' => Auth::user()->id]);

                });
        } elseif (Auth::user()->hasRole('collection_officer')) {

            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)

                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.id', 'loans.customer_id') // Proper correlation
                        ->where(['customers.field_agent_id' => Auth::user()->field_agent_id]);

                });
        } else {

            $loans_w_arrears = DB::table('loans')
                ->where(['disbursed' => true])
                ->whereMonth('disbursement_date', $request->month)
                ->whereYear('disbursement_date', $request->year)
                ->whereExists(function ($query) use ($request) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.id', 'loans.customer_id') // Proper correlation
                        ->where(['customers.branch_id' => Auth::user()->branch_id]);

                });
        }
            $total_paid = 0;
        $defaulted_amount = 0;
        $count = 0;




            foreach ($loans_w_arrears->get() as $lo) {
               // $installment = DB::table('installments')->where(['loan_id' => $lo->id])->whereMonth('due_date', $request->month)->whereYear('due_date', $request->year)->whereDate('due_date', '<', Carbon::now())->get();
                //$installment = DB::table('installments')->where(['loan_id' => $lo->id])->get();
               // $total_paid += $installment->sum('amount_paid');
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                $total_paid += $payments;

                if (!$lo->settled){
                    //check is the loan due date has passed
                    $date = Carbon::parse($lo->end_date);

                    if ($date->isPast()) {
                        if (!$date->isToday()) {
                            $count += 1;

                            $balance = $lo->total_amount - $payments;
                            $defaulted_amount += $balance;
                        }
                    }

                }





            }
        $disbursed_amount = $loans_w_arrears->sum('loan_amount');
        $total_amount = $loans_w_arrears->sum('total_amount');
        $percentage_collection = 0;

        if ($total_amount > 0){
            $percentage_collection = ($total_paid/$total_amount) * 100;

        }





            return [
                'disbursed_amount' => number_format($disbursed_amount),
                'total_amount' => number_format($total_amount),
                'collected_amount'=>number_format($total_paid),
                'defaulted_amount'=>number_format($defaulted_amount),
                'percentage_collection' => number_format($percentage_collection),
                'defaulted_count'=>number_format($count),


            ];



    }




    /**
     * system leads
     *
     *
    */
    public function leads(){
        $this->data['title'] = "System Leads";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        //$this->data['lfs'] = User::hasRole(['collection_officer', 'field_agent'])->where('status', true)->get();
        $this->data['lfs'] = User::whereHas('roles', function ($query) {
            return $query->whereIn("roles.name", ['field_agent', 'collection_officer', 'customer_informant', 'phone_handler']);
        })
            //->where(['users.status' => true, 'users.branch_id' => $branch->id])
            ->where('status', '=', true)
            ->get();

        return view('pages.reports.leads.index', $this->data);
    }

    public function leads_data(Request $request){
        //dd($request->all());


        if ($request->branch && $request->branch != 'all'){
            //dd($request->branch);
            $lo = DB::table('leads')
                /*->join('users', function ($join)  use($request->branch)  {
                    $join->on('leads.user_id', '=', 'users.id')
                        ->where('users.branch_id', '=', $request->branch);
                })*/
             ->join('users', function ($join) use ($request) {
                    $join->on('leads.officer_id', '=', 'users.id')
                        ->where('users.branch_id', $request->branch);

                })
                ->get();

        } elseif ($request->lf && $request->lf != 'all'){

            $lo = DB::table('leads')
                ->where(['user_id' => $request->lf])

                ->get();
        }
        else{
            $lo = DB::table('leads')->get();

        }
        return Datatables::of($lo)
            ->addColumn('officer', function ($lo) {
                $user = User::find($lo->officer_id);
                return $user->name;

            })
            ->addColumn('branch', function ($lo) {
                $user = User::find($lo->officer_id);
                return $user->branch;

            })

                ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);


                return '<a href="' . route('lead.delete', ['id' => $data]) . '"    class="sel-btn btn btn-xs btn-primary"><i class="feather icon-delete text-danger"></i> Delete</a>';

            })
            ->rawColumns(['action'])
            ->toJson();

    }

    public function lead_create(){
        $this->data['title'] = "System Leads";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
        return view('pages.reports.leads.create', $this->data);
    }

    public function importLead()
    {
        return view('pages.reports.leads.import');
    }

    public function lead_post(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'business' => 'required',
            'location' => 'required',
            'amount' => 'required',

        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $lead = DB::table('leads')->insert([
            'name' => $request->name,
            'phone_number' => $request->phone,
            'type_of_business' => $request->business,
            'location' => $request->location,
            'estimated_amount' => $request->amount,
            'officer_id' => \auth()->id(),
            'created_at' => Carbon::now()

        ]);

        return back()->with('success', 'Successfully added the lead');
    }

    public function lead_delete($id){
        $lead = DB::table('leads')->where(['id' => decrypt($id)])->first();
        if ($lead){
            DB::table('leads')->where(['id' => decrypt($id)])->delete();
            return back()->with('success', 'Successfully deleted the lead');


        }
        return back()->with('error', 'lead not found');
    }

    public function importLeads(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);

        $import = new LeadsImport;
        Excel::import($import, $request->file('file'));

        $message = 'Leads imported successfully!';
        $message .= ' Inserted: ' . $import->inserted . '.';
        $message .= ' Existing: ' . $import->existing . '.';

        if (count($import->errors)) {
            $message .= ' Errors: ' . count($import->errors) . ' rows with issues.';
            foreach ($import->errors as $error) {
                $message .= ' Row ' . $error['row'] . ': ' . $error['error'] . '. ';
            }
        }

        return back()->with('success', $message);
    }




}
