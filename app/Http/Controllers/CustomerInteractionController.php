<?php

namespace App\Http\Controllers;

use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\Customer_interaction_followup;
use App\models\CustomerInteraction;
use App\models\CustomerInteractionCategory;
use App\models\CustomerInteractionType;
use App\models\Installment;
use App\models\Loan;
use App\models\Pre_interaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CustomerInteractionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function interactions_report()
    {
        $user = \auth()->user();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $branches = Branch::query()->where('status', '=', true)->get();
            $credit_officers = User::role('field_agent')->where('status', true)->get();
            $interactions_count = CustomerInteraction::query()->count();
            $interactions_count_mtd = CustomerInteraction::query()->whereBetween('created_at', [now()->startOfMonth(), now()])->count();
            $this->data['sub_title'] = "List of All Customer Interactions";
        } elseif ($user->hasRole('field_agent')) {
            $branches = Branch::query()->where('id', '=', $user->branch_id)->get();
            $branch = Branch::find($user->branch_id);
            $interactions_count = CustomerInteraction::query()->whereHas('customer', function (Builder $builder) {
                $builder->where('user_id', '=', \auth()->id());
            })->count();
            $interactions_count_mtd = CustomerInteraction::query()->whereHas('customer', function (Builder $builder) {
                $builder->where('user_id', '=', \auth()->id());
            })->whereBetween('created_at', [now()->startOfMonth(), now()])->count();

            if ($user->hasRole('field_agent')) {
                $credit_officers = User::role('field_agent')->where('id', '=', $user->id)->where('status', true)->get();

            } else {
                $credit_officers = User::role('field_agent')->where('id', '=', $user->field_agent_id)->where('status', true)->get();

            }

            $this->data['sub_title'] = "List of Customer Interactions in " . $branch->bname;
        } else {
            $branches = Branch::query()->where('id', '=', Auth::user()->branch_id)->get();
            $branch = Branch::find($user->branch_id);
            $interactions_count = CustomerInteraction::query()->whereHas('customer', function (Builder $builder) {
                $builder->where('branch_id', '=', Auth::user()->branch_id);
            })->count();
            $interactions_count_mtd = CustomerInteraction::query()->whereHas('customer', function (Builder $builder) {
                $builder->where('branch_id', '=', Auth::user()->branch_id);
            })->whereBetween('created_at', [now()->startOfMonth(), now()])->count();
            $credit_officers = User::role('field_agent')->where('branch_id', '=', $branch->id)->where('status', true)->get();;
            $this->data['sub_title'] = "List of Customer Interactions in " . $branch->bname;
        }

        $this->data['title'] = "Customer Interaction Reports";
        $this->data['branches'] = $branches;
        $this->data['lfs'] = $credit_officers;
        $this->data['interactions_count'] = $interactions_count;
        $this->data['interactions_count_mtd'] = $interactions_count_mtd;
        $this->data['interaction_types'] = CustomerInteractionType::all();
        return view("pages.customer_interactions.customer-interaction-report", $this->data);
    }

    /**
     * Select a customer list
     *
     * @return \Illuminate\Http\Response
     */
    public function select_customer(): View
    {
        $this->data['title'] = "Create a New Customer Interaction";
        $this->data['sub_title'] = "From the list below, select a customer to create a new Interaction.";

        $branch = Branch::find(Auth::user()->branch_id);

        // if (Auth::user()->hasRole('collection_officer')) {
        //     $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => \auth()->user()->field_agent_id])->where('branch_id', '=', $branch->id)->get();
        //     $this->data['branches'] = Branch::query()->where(['status' => true, 'id' => $branch->id])->get();
        // } else {
        // }
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();

        $this->data['lfs'] = User::role('field_agent')->where('status', true)->where('branch_id', '=', $branch->id)->get();

        return view("pages.customer_interactions.select_customer", $this->data);
    }

    public function list_customer_interactions($customer_identifier): View
    {
        $customer = Customer::query()->findOrFail($customer_identifier);
        $this->data['interaction_types'] = CustomerInteractionType::all();
        $this->data['interaction_categories'] = CustomerInteractionCategory::where(['priority' => 2])->get();

        $this->data['customer'] = $customer;
        $this->data['title'] = $customer->full_name . ' - ID. ' . $customer->id_no . ' - ' . $customer->phone;
        $this->data['sub_title'] = "Customer Interactions Thread";

        return view('pages.customer_interactions.customer-index', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'interaction_type_id' => 'required|exists:customer_interaction_types,id',
            'remark' => 'required|min:5|max:1000',
            'next_scheduled_interaction' => 'nullable|date',
        ]);
        $cat = CustomerInteractionCategory::where(['id' => $request->interaction_category_id])->orWhere(['name' => $request->interaction_category_id])->first();

        if (!$cat) {
            return Redirect::back()->withErrors($validator)->withInput()->with('warning', $validator->errors()->first() . '. All fields are required');
        }

        if ($cat->name == "Prepayment" || $cat->name == "Due Collection" || $cat->name == "Arrear Collection") {
            $target = 0;
            $status = 1;
        } else {
            $target = 1;
            $status = 2;
        }
        $delete = false;

        if ($cat->name == "Arrear Collection") {
            //check if the arrear has been paid
            $arrear = Arrear::where(['id' => decrypt($request->input('model_id'))])->where('amount', '>', 0)->first();
            if ($arrear) {
                //check if loan is settled
                $loan = Loan::find($arrear->loan_id);
                if ($loan->settled) {
                    //delete pre interaction
                    $delete = true;
                }
            } else {
                $delete = true;
                //delete pre interaction
            }
        } elseif ($cat->name == "Prepayment" || $cat->name == "Due Collection") {
            $installment = Installment::where(['id' => decrypt($request->input('model_id'))])->first();
            if ($installment) {
                //check if loan is settled
                $loan = Loan::find($installment->loan_id);
                if ($loan->settled) {
                    //delete pre interaction
                    $delete = true;
                }

                //check if the installment has been paid
                $bal = $installment->total - $installment->amount_paid;
                if ($bal == 0) {
                    //delete pre interaction
                    $delete = true;
                }
            } else {
                $delete = true;
            }
        }

        if ($delete) {
            Pre_interaction::where(['id' => decrypt($request->pre_interaction_id)])->delete();
            return Redirect::back()->with('success', "This payment has already been completed");
        }

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput()->with('warning', $validator->errors()->first() . '. Kindly try again');
        }

        if (Carbon::parse($request->input('date_visited')) > now()) {
            return Redirect::back()->withErrors($validator)->withInput()->with('warning', 'The specified Visited date is invalid as it is yet to reach');
        }

        if ($request->input('next_scheduled_interaction') && Carbon::parse($request->input('next_scheduled_interaction'))->isPast()) {
            if (!Carbon::parse($request->input('next_scheduled_interaction'), "Africa/Nairobi")->isToday()) {
                return Redirect::back()->withErrors($validator)->withInput()->with('warning', 'The specified Next scheduled Interaction date is invalid as it is in the past');
            }
        }

        $customer = Customer::query()->find($request->input('customer_id'));

        $next_interaction = null;
        if ($request->input('next_scheduled_interaction') != null) {
            $next_interaction = Carbon::parse($request->input('next_scheduled_interaction'));
        }

        CustomerInteraction::query()->create([
            'customer_id' => $customer->id,
            'interaction_type_id' => $request->input('interaction_type_id'),
            'interaction_category_id' => $cat->id,
            'user_id' => Auth::id(),
            'remark' => $request->input('remark'),
            'next_scheduled_interaction' => $next_interaction,
            'model_id' => isset($request->model_id) ? decrypt($request->input('model_id')) : null,
            'status' => $status,
            'target' => $target,
            'followed_up' => $next_interaction == null ? 1 : 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (isset($request->pre_interaction_id)) {
            Pre_interaction::where(['id' => decrypt($request->pre_interaction_id)])->delete();
        }

        return Redirect::back()->with('success', "$customer->full_name's Interaction List has been updated successfully");
    }

    /**
     * Select a customer list datatable
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function select_customer_data(Request $request)
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        $customers = Customer::select('*')->whereIn('branch_id', $activeBranches);
        if ($request->lf != 'all') {
            $customers = $customers->where('field_agent_id', $request->lf);
        } elseif ($request->branch != 'all') {
            $customers = $customers->where('branch_id', $request->branch);
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $customers = Customer::select('*')->whereIn('branch_id', $activeBranches);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $customers = $customers->where('field_agent_id', Auth::id());
        } else {
            $customers = $customers->where('branch_id', Auth::user()->branch_id);
        }

        return DataTables::eloquent($customers)
            ->addColumn('branchName', function (Customer $customer) {
                return $customer->branch->bname;
            })
            ->addColumn('loanOfficer', function (Customer $customer) {
                return $customer->Officer->name;
            })
            ->addColumn('customerName', function (Customer $customer) {
                return $customer->fullName;
            })
            ->addColumn('mobileNumber', function (Customer $customer) {
                return $customer->phone;
            })
            ->addColumn('idNo', function (Customer $customer) {
                return $customer->id_no;
            })
            ->addColumn('createdDate', function (Customer $customer) {
                $dateTime = Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('action', function (Customer $customer) {
                $html = '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-settings"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" x-placement="top-end" style="position: absolute; transform: translate3d(87px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                            <a class="dropdown-item" href="' . route('customer-interactions.list_customer_interactions', $customer->id) . '" target="_blank"><i class="feather icon-eye"></i> Select</a>
                            <a class="dropdown-item" href="' . route('unhandled_arrears', encrypt($customer->id)) . '"><i class="feather icon-edit"></i> Add Arrear Interaction</a>
                              ';

                return $html;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * DataTable for a single customer's interactions
     *
     * @param string $customer_identifier
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function customer_interactions_data(Request $request, string $customer_identifier): \Illuminate\Http\JsonResponse
    {
        if ($customer_identifier != 'all') {
            $data = CustomerInteraction::join('users', function ($join) {
                $join->on('users.id', '=', 'customer_interactions.user_id');
            })
                ->join('customers', function ($join) use ($customer_identifier) {
                    $join->on('customers.id', '=', 'customer_interactions.customer_id')
                        ->where(['customer_id' => decrypt($customer_identifier)]);
                })
                ->join('customer_interaction_types', function ($join) {
                    $join->on('customer_interaction_types.id', '=', 'customer_interactions.interaction_type_id');

                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'customer_interactions.interaction_category_id');

                })
                ->select('customer_interactions.*', 'users.name as uname', 'customers.lname', 'customers.phone', 'customer_interaction_types.name as tname', 'customer_interaction_categories.name as cname');
        } elseif ($request->lf != 'all') {
            $data = CustomerInteraction::where(['customer_interactions.user_id' => $request->lf])
                ->join('users', function ($join) {
                    $join->on('users.id', '=', 'customer_interactions.user_id');
                })
                ->join('customers', function ($join) use ($request) {
                    $join->on('customers.id', '=', 'customer_interactions.customer_id');
                    //->where(['customers.field_agent_id' => $request->lf]);
                })
                ->join('customer_interaction_types', function ($join) {
                    $join->on('customer_interaction_types.id', '=', 'customer_interactions.interaction_type_id');

                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'customer_interactions.interaction_category_id');

                })
                ->select('customer_interactions.*', 'users.name as uname', 'customers.lname', 'customers.phone', 'customer_interaction_types.name as tname', 'customer_interaction_categories.name as cname');
        } elseif ($request->branch != 'all') {
            $data = CustomerInteraction::join('users', function ($join) {
                $join->on('users.id', '=', 'customer_interactions.user_id');
            })
                ->join('customers', function ($join) use ($request) {
                    $join->on('customers.id', '=', 'customer_interactions.customer_id')
                        ->where(['customers.branch_id' => $request->branch]);
                })
                ->join('customer_interaction_types', function ($join) {
                    $join->on('customer_interaction_types.id', '=', 'customer_interactions.interaction_type_id');

                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'customer_interactions.interaction_category_id');

                })
                ->select('customer_interactions.*', 'users.name as uname', 'customers.lname', 'customers.phone', 'customer_interaction_types.name as tname', 'customer_interaction_categories.name as cname');
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $data = CustomerInteraction::join('users', function ($join) {
                $join->on('users.id', '=', 'customer_interactions.user_id');
            })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'customer_interactions.customer_id');

                })
                ->join('customer_interaction_types', function ($join) {
                    $join->on('customer_interaction_types.id', '=', 'customer_interactions.interaction_type_id');

                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'customer_interactions.interaction_category_id');
                })
                ->select('customer_interactions.*', 'users.name as uname', 'customers.lname', 'customers.phone', 'customer_interaction_types.name as tname', 'customer_interaction_categories.name as cname');
        } elseif (Auth::user()->hasRole('field_agent')) {
            $data = CustomerInteraction::join('users', function ($join) {
                $join->on('users.id', '=', 'customer_interactions.user_id');
            })
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'customer_interactions.customer_id');
                    //  ->where(['customers.field_agent_id' => \auth()->id()]);
                })
                ->join('customer_interaction_types', function ($join) {
                    $join->on('customer_interaction_types.id', '=', 'customer_interactions.interaction_type_id');

                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'customer_interactions.interaction_category_id');

                })
                ->select('customer_interactions.*', 'users.name as uname', 'customers.lname', 'customers.phone', 'customer_interaction_types.name as tname', 'customer_interaction_categories.name as cname');
        } else {
            $data = CustomerInteraction::join('users', function ($join) {
                $join->on('users.id', '=', 'customer_interactions.user_id');
            })
                ->join('customers', function ($join) use ($request) {
                    $join->on('customers.id', '=', 'customer_interactions.customer_id')
                        ->where(['customers.branch_id' => \auth()->user()->branch_id]);
                })
                ->join('customer_interaction_types', function ($join) {
                    $join->on('customer_interaction_types.id', '=', 'customer_interactions.interaction_type_id');

                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'customer_interactions.interaction_category_id');

                })
                ->select('customer_interactions.*', 'users.name as uname', 'customers.lname', 'customers.phone', 'customer_interaction_types.name as tname', 'customer_interaction_categories.name as cname');
        }


        if ($request->status != "all") {
            $clone = clone $data;
            if ($request->status == "due") {
                $data = $clone->where(['customer_interactions.status' => 1])->whereDate('customer_interactions.next_scheduled_interaction', '=', Carbon::now())
                    ->orWhere(function ($query) {
                        $query->join('customer_interaction_followups', function ($join) {
                            $join->on('customer_interactions.id', '=', 'customer_interaction_followups.follow_up_id')
                                ->whereDate('customer_interaction_followups.next_scheduled_interaction', '=', Carbon::now())
                                ->where(['customer_interactions.status' => 1, 'customer_interaction_followups.status' => 2]);

                        });
                    });
            } elseif ($request->status == "overdue") {
                $data = $clone->where(['customer_interactions.status' => 1])->whereDate('customer_interactions.next_scheduled_interaction', '<', Carbon::now())
                    ->orWhere(function ($query) {
                        $query->join('customer_interaction_followups', function ($join) {
                            $join->on('customer_interactions.id', '=', 'customer_interaction_followups.follow_up_id')
                                ->whereDate('customer_interaction_followups.next_scheduled_interaction', '<', Carbon::now())
                                ->where(['customer_interactions.status' => 1, 'customer_interaction_followups.status' => 2]);

                        });
                    });

            } else {
                $data = $clone->where(['customer_interactions.status' => $request->status]);
            }
        }

        if ($request->category != "all") {
            $data = $data->where(['customer_interactions.interaction_category_id' => $request->category]);
        }

        return Datatables::of($data)
            ->editColumn('status', function ($data) {
                $status = '<label class="label label-success">OPEN</label>';
                if ($data->status == 2) {
                    $status = '<label class="label label-warning">CLOSED</label>';
                }
                return $status;
            })
            ->editColumn('target', function ($data) {

                if ($data->status == 1) {
                    $status = '--';
                } else {
                    if ($data->target == 1) {
                        $status = '<label class="label label-success">Success</label>';
                    } else {
                        $status = '<label class="label label-danger">Fail</label>';
                    }
                }
                return $status;
            })
            ->editColumn('instal_due', function ($data) {
                $date = "--";

                if ($data->interaction_category_id == 2 || $data->interaction_category_id == 3) {
                    $installment = Installment::find($data->model_id);
                    if ($installment) {
                        $date = $installment->due_date;
                    }
                }
                return $date;
            })
            ->addColumn('action', function ($data) {
                $html = '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-settings"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" x-placement="top-end" style="position: absolute; transform: translate3d(87px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                            <a class="dropdown-item" href="' . route('interaction.follow_up', encrypt($data->id)) . '" target="_blank"><i class="feather icon-eye"></i> Follow Up</a>
                            <a class="dropdown-item" href="' . route('update_follow_up', ['name' => 'close', 'id' => encrypt($data->id)]) . '"><i class="feather icon-edit"></i> Mark Settled</a>
                              ';

                return $html;
            })
            ->rawColumns(['status', 'action', 'target'])
            ->toJson();
    }

    /**
     * DataTable for a single customer's interactions
     *
     * @param string $customer_identifier
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function customer_interactions_report_data(Request $request): \Illuminate\Http\JsonResponse
    {
        $customer_interactions = CustomerInteraction::query()->with(
            [
                'interaction_type:id,name',
                'customer:id,fname,lname,branch_id,phone,field_agent_id'
            ]);
        $user = \auth()->user();

        if (Auth::user()->hasRole('field_agent')) {
            $customer_interactions->whereHas('customer', function (Builder $builder) use ($request, $user) {
                $builder->where('user_id', '=', $user->id);
            });
        } elseif ($request->lf and $request->branch and $request->lf != 'all' and $request->branch != 'all') {
            $customer_interactions->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', $request->lf);
                $builder->where('branch_id', '=', $request->branch);
            });
        } elseif ($request->lf and $request->branch and $request->lf == 'all' and $request->branch != 'all') {
            $customer_interactions->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('branch_id', '=', $request->branch);
            });
        } elseif ($request->lf and $request->branch and $request->lf != 'all' and $request->branch == 'all') {
            $customer_interactions->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('field_agent_id', '=', $request->lf);
            });
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $customer_interactions->whereHas('customer');
        } else {
            $customer_interactions->whereHas('customer', function (Builder $builder) use ($request) {
                $builder->where('branch_id', '=', Auth::user()->branch_id);
            });
        }

        return Datatables::of($customer_interactions)
            ->addColumn('user_name', function ($lo) {
                $user = User::query()->select('name')->find($lo->user_id)->setAppends([]);
                if ($user) {
                    return $user->name;
                }
                return '--';
            })
            ->addColumn('customer_branch', function ($lo) {
                $branch = Branch::find($lo->customer->branch_id)->setAppends([]);
                if ($branch) {
                    return $branch->bname;
                }
                return '--';
            })->toJson();
    }


    /**
     * All interactions
     * Interactions
     *
     * */
    public function all_interactions(): View
    {
        $this->data['title'] = "All Interactions";
        $this->data['sub_title'] = "List of all interactions.";
        $this->data['categories'] = CustomerInteractionCategory::all();
        $user = \auth()->user();

        $branch = Branch::find(Auth::user()->branch_id);
        $this->data['current_officer'] = 'all';
        $this->data['current_branch'] = 'all';
        $this->data['current_status'] = 'all';
        $this->data['month'] = date('m');

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['lfs'] = User::whereHas('roles', function ($query) {
                return $query->whereIn("name", ['field_agent', 'customer_informant', 'phone_handler']);
            })->where('status', true)->get();
        } else {
            $this->data['lfs'] = User::whereHas('roles', function ($query) {
                return $query->whereIn("roles.name", ['field_agent', 'customer_informant', 'phone_handler']);
            })->where(['users.status' => true, 'users.branch_id' => $branch->id])->get();

            $this->data['branches'] = Branch::where('id', '=', $branch->id)->get();
        }

        return view("pages.customer_interactions.all_interactions", $this->data);
    }

    /**
     * pre interactions
     * Interactions need to be done created by the system
     * Arrear collections and due collections
     *
     *
     */
    public function pre_interactions($id): View
    {
        $this->data['title'] = "Pre Interactions";
        $this->data['interaction_types'] = CustomerInteractionType::all();
        $this->data['interaction_categories'] = CustomerInteractionCategory::where(['priority' => 1])->where('name', "!=", "Arrear Collection")->get();

        if ($id == "arrears") {
            $type = "arrears";
            $this->data['title'] = "Arrears Pre Interactions";
            $this->data['sub_title'] = "List of Unattended interactions.";

            $category = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();
        } else {
            $type = "others";

            $this->data['title'] = "PrePayment/Due Pre Interactions";
            $this->data['sub_title'] = "List of Unattended Prepayment Interactions.";

            $category = CustomerInteractionCategory::where(['name' => 'Prepayment'])->first();
        }
        $this->data['type'] = $type;

        $this->data['id'] = $category->id;

        $branch = Branch::find(Auth::user()->branch_id);

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['lfs'] = User::role('field_agent')->where('status', true)->get();
        } else {
            if (Auth::user()->hasRole('field_agent')) {
                $this->data['lfs'] = User::role('field_agent')->where(['status' => true, 'id' => \auth()->user()->id])->get();
            } else {
                $this->data['lfs'] = User::role('field_agent')->where('status', true)->where('branch_id', '=', $branch->id)->get();
            }
            $this->data['branches'] = Branch::where('id', '=', $branch->id)->get();
        }

        return view("pages.customer_interactions.pre_interactions", $this->data);
    }

    public function pre_interactions_data(Request $request)
    {
        $user = \auth()->user();

        if ($request->lf != 'all') {
            $interactions = Pre_interaction::where(['interaction_category_id' => $request->id])
                ->join('customers', function ($join) use ($request) {
                    $join->on('customers.id', '=', 'pre_interactions.customer_id')
                        ->where('customers.field_agent_id', '=', $request->lf);
                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'pre_interactions.interaction_category_id');

                })
                ->select('pre_interactions.*', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'customers.branch_id', 'customer_interaction_categories.name');
        } elseif ($request->branch != 'all') {
            $interactions = Pre_interaction::where(['interaction_category_id' => $request->id])
                ->join('customers', function ($join) use ($request) {
                    $join->on('customers.id', '=', 'pre_interactions.customer_id')
                        ->where('customers.branch_id', $request->branch);;
                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'pre_interactions.interaction_category_id');

                })
                ->select('pre_interactions.*', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'customers.branch_id', 'customer_interaction_categories.name');
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $interactions = Pre_interaction::where(['interaction_category_id' => $request->id])
                ->join('customers', function ($join) {
                    $join->on('customers.id', '=', 'pre_interactions.customer_id');
                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'pre_interactions.interaction_category_id');

                })
                ->select('pre_interactions.*', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'customers.branch_id', 'customer_interaction_categories.name');

        } elseif (Auth::user()->hasRole('field_agent')) {
            $interactions = Pre_interaction::where(['interaction_category_id' => $request->id])
                ->join('customers', function ($join) use ($user) {
                    $join->on('customers.id', '=', 'pre_interactions.customer_id')
                        ->where('customers.field_agent_id', '=', $user->id);
                    ;
                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'pre_interactions.interaction_category_id');

                })
                ->select('pre_interactions.*', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'customers.branch_id', 'customer_interaction_categories.name');
        } else {
            $interactions = Pre_interaction::where(['interaction_category_id' => $request->id])
                ->join('customers', function ($join) use ($user) {
                    $join->on('customers.id', '=', 'pre_interactions.customer_id')
                        ->where('customers.branch_id', $user->branch_id);
                })
                ->join('customer_interaction_categories', function ($join) {
                    $join->on('customer_interaction_categories.id', '=', 'pre_interactions.interaction_category_id');

                })
                ->select('pre_interactions.*', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'customers.branch_id', 'customer_interaction_categories.name');
        }

        $category = CustomerInteractionCategory::find($request->id);
        if ($category->name == "Prepayment") {
            $interactions->whereDate('due_date', '>=', Carbon::now());
        }

        if ($request->status != "all") {
            if ($request->status == "due") {
                $interactions->whereDate('due_date', Carbon::now());
            } else {
                $interactions->whereDate('due_date', '<', Carbon::now());
            }
        }

        if ($request->date != '') {
            $interactions->whereDate('due_date', Carbon::parse($request->date));
        }

        return DataTables::eloquent($interactions)
            ->editColumn('LO', function (Pre_interaction $interactions) {
                $lo = User::find($interactions->field_agent_id);
                return $lo->name;
            })
            ->editColumn('branch', function (Pre_interaction $interactions) {
                $br = DB::table('branches')->where(['id' => $interactions->branch_id])->first();
                return $br->bname;
            })
            ->addColumn('action', function (Pre_interaction $interactions) {
                return '<button  class="interact btn btn-primary" data-pre_id="' . encrypt($interactions->id) . '" data-model="' . encrypt($interactions->model_id) . '" data-category="' . $interactions->name . '"  data-customer_id="' . $interactions->customer_id . '" data-remark="' . $interactions->system_remark . '"  data-remark="" data-date="' . $interactions->due_date . '" data-toggle="modal" data-target="#exampleModal" >
                            Add Interaction
                        </button>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * interactions follow up
     * or view interaction
     *
     * */
    public function interaction_follow_up($id)
    {
        $this->data['interaction'] = CustomerInteraction::find(decrypt($id));

        $this->data['title'] = "Interaction Follow up";
        $this->data['sub_title'] = $this->data['interaction']->customer->fullName . ' - ' . $this->data['interaction']->customer->phone;
        $this->data['interaction_types'] = CustomerInteractionType::all();

        return view("pages.customer_interactions.follow_up", $this->data);
    }

    public function interaction_followup_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'follow_up_id' => 'required|exists:customers,id',
            'interaction_type_id' => 'required|exists:customer_interaction_types,id',
            'remark' => 'required|min:5|max:1000',
            'next_scheduled_interaction' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput()->with('warning', $validator->errors()->first() . '. Kindly try again');
        }
        $interaction = CustomerInteraction::find(decrypt($request->follow_up_id));
        if (!$interaction) {
            return Redirect::back()->withErrors($validator)->withInput()->with('warning', $validator->errors()->first() . '. Something wrong!. Kindly try again');
        }

        //if (Carbon::parse($request->input('next_scheduled_interaction')) < now()) {
        if ($request->input('next_scheduled_interaction') && Carbon::parse($request->input('next_scheduled_interaction'))->isPast()) {
            if (!Carbon::parse($request->input('next_scheduled_interaction'), "Africa/Nairobi")->isToday()) {
                return Redirect::back()->withErrors($validator)->withInput()->with('warning', 'The specified Next scheduled Interaction date is invalid as it is in the past');
            }
        }

        Customer_interaction_followup::create([
            'follow_up_id' => decrypt($request->follow_up_id),
            'follow_by' => \auth()->id(),
            'remark' => $request->remark,
            'next_scheduled_interaction' => $request->next_scheduled_interaction,
            'status' => $request->next_scheduled_interaction == null ? 1 : 2
        ]);
        if ($request->next_scheduled_interaction != null) {
            $interaction->update([
                'next_scheduled_interaction' => $request->next_scheduled_interaction,
            ]);
        }
        return Redirect::back()->with('success', "Interaction List has been updated successfully");
    }

    public function update_follow_up($name, $id)
    {
        if ($name == 'interaction') {
            CustomerInteraction::where(['id' => decrypt($id)])->update([
                'followed_up' => 1
            ]);
        } elseif ($name == 'followup') {
            Customer_interaction_followup::where(['id' => decrypt($id)])->update([
                'status' => 1
            ]);
        } elseif ($name == 'close') {
            $completed = true;

            $int = CustomerInteraction::where(['id' => decrypt($id)])->first();
            $category = CustomerInteractionCategory::find($int->interaction_category_id);
            if ($category->name == "Prepayment" || $category->name == "Due Collection") {

                $installment = Installment::find($int->model_id);

                if ($installment) {
                    $completed = $installment->completed;
                }
            } elseif ($category->name == "Arrear Collection") {

                $arrear = Arrear::find($int->model_id);
                if ($arrear) {
                    //check loan if has been paid
                    $ln = Loan::find($arrear->loan_id);
                    if (!$ln->settled) {
                        $completed = !($arrear->amount > 0);
                    }
                }
            }

            if ($completed) {
                $int->update([
                    'status' => 2,
                    'target' => 1,
                    'closed_date' => Carbon::now(),
                    'closed_by' => \auth()->id()
                ]);
            } else {
                return Redirect::back()->with('error', "The interaction yet to be settled. Confirm all the payments has been made");
            }
        } else {
            return Redirect::back()->with('error', "Try again");
        }
        return Redirect::back()->with('success', "updated the interaction");
    }

    //unhandled arrears
    public function unhandled_arrears($id)
    {
        $customer = Customer::find(decrypt($id));
        $this->data['customer'] = $customer;
        $this->data['title'] = "Unhandled arrears";
        $this->data['sub_title'] = $customer->fullName . ' - ' . $customer->phone;
        $this->data['interaction_types'] = CustomerInteractionType::all();
        $this->data['category'] = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();

        return view("pages.customer_interactions.unhandled_arrears", $this->data);
    }

    public function unhandled_arrears_data($id)
    {
        $loans = Loan::where(['customer_id' => decrypt($id), 'settled' => false])->pluck('id');

        $cat = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();

        $interactions = CustomerInteraction::where(['interaction_category_id' => $cat->id, 'customer_id' => decrypt($id)])->pluck('model_id');
        $pre_interactions = Pre_interaction::where(['interaction_category_id' => $cat->id, 'customer_id' => decrypt($id)])->pluck('model_id');
        $arrears = Arrear::whereIn('loan_id', $loans)->whereNotIn('id', $interactions)->whereNotIn('id', $pre_interactions);

        return DataTables::eloquent($arrears)
            ->editColumn('loan_account', function ($l) {
                return $l->loan->loan_account;
            })
            ->editColumn('was_due', function ($l) {
                return $l->installment->due_date;
            })
            ->addColumn('action', function ($l) {

                return '<button  class="interact btn btn-primary" data-pre_id="' . encrypt($l->id) . '" data-model="' . encrypt($l->id) . '" data-category="' . $l->id . '"  data-customer_id="' . $l->id . '" data-remark="' . $l->id . '"  data-remark="" data-date="' . $l->id . '" data-toggle="modal" data-target="#exampleModal" >
                            Add Interaction
                        </button>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }
}
