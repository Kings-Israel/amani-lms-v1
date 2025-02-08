<?php

namespace App\Http\Controllers;

use App\models\Branch;
use App\models\Customer;
use App\models\Group;
use App\models\Loan;
use App\models\Payment;
use App\models\Product;
use App\models\Regpayment;
use App\models\Rollover;
use App\models\Setting;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class GroupController extends Controller
{
    public function __construct()
    {
//        $this->middleware('role:admin');
        $this->middleware('role:admin|customer_informant|manager|field_agent|sector_manager', ['only' => ['edit', 'update', 'delete','create', 'store','destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['title'] = "Groups";

        $this->data['sub_title'] = "List of Registered LITSA CREDIT Groups.";

        $this->data['customers'] = Group::all();


        return view('pages.groups.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['title'] = "Create New Group";
        $this->data['is_edit'] = false;
        $this->data['customers'] = Customer::all();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager')){
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        }else{
            $this->data['branches'] = Branch::where('id', '=', Auth::user()->branch_id)->get();
        }
        return view('pages.groups.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leader_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'field_agent_id' => 'required|exists:users,id',
            'name' => 'required',
            'customers_count' => 'required|integer|min:1|max:25',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $group = Group::where(['name' => $request->name, 'leader_id' => $request->leader_id])->first();
        $loan = Loan::where(['customer_id' => $request->leader_id, 'settled' => false])->first();
        $customer = Customer::find($request->leader_id);
        $branch = Branch::find($request->branch_id);
        $field_agent = User::find($request->field_agent_id);
        if ($customer->branch_id != $branch->id){
            return back()->with('error', 'Could not register group. The selected branch and branch the leader is from are not the same.')->withInput();
        }
        if ($loan){
            return back()->with('error', 'Could not register group. The selected leader, '.$customer->fname. ' '. $customer->lname.' still has an unsettled loan.')->withInput();
        }
        if (!$field_agent){
            return back()->with('error', 'Could not register group. The selected loan officer could not be found, refresh page and try again')->withInput();
        }
        $all_groups = Group::all();
        $check_customer = false;
        foreach ($all_groups as $all_group){
            if ($all_group->customers()->where('customer_id', $customer->id)->exists()){
                $check_customer = true;
            }
        }
        if ($check_customer){
            return back()->with('error', 'Could not register group. The selected leader, '.$customer->fname. ' '. $customer->lname.' is registered under another group')->withInput();
        }

        if (!isset($group)) {
            if ($branch and $field_agent){
                $check = Group::where('branch_id', '=', $branch->id)->count();
                $id = $check + 1;
                $check_id = Group::where('unique_id', '=', $branch->bname.sprintf("%03d",$id))->first();
                if ($check_id){
                    $last_added = Group::where('branch_id', '=', $branch->id)->orderBy('unique_id', 'desc')->first();
                    $last_added = $last_added->unique_id;
                    $last_added_id = substr($last_added, -3);
                    $id = $last_added_id + 1;
                }
                $group = Group::create([
                    'leader_id' => $request->leader_id,
                    'branch_id' => $branch->id,
                    'field_agent_id'=> $field_agent->id,
                    'name' => $request->name,
                    'unique_id'=> $branch->bname.sprintf("%03d",$id),
                    'customers_count' => $request->customers_count,
                    'status'=>false,
                    'approved'=>false,
                    'created_by' => Auth::user()->id,
                    'created_at' => Carbon::now('Africa/Nairobi'),
                    'updated_at' => Carbon::now('Africa/Nairobi'),
                ]);
                $group->customers()->attach($request->input('leader_id') === null ? [] : $request->input('leader_id'), ['role' => 'leader']);

                //send SMS to group leader notifying UNIQUE ID

                return redirect()->to('/groups')->with('success', 'Successfully registered the group '.$request->name);
            }
            else{
                return back()->with('error', 'Could not register group. branch selection is invalid')->withInput();
            }

            }
            else
                {
            return back()->with('error', 'Could not register group. This group seems to already exist, check selected details and try again')->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $group = Group::find(decrypt($id));
        $leader = $group->leader()->first();
        $this->data['days_remaining'] = "N/A";


        $this->data['title'] = "Group Details";
        $this->data['sub_title'] = $group->name. ' details';

        $this->data['group'] = $group;
        $this->data['payments'] = Payment::where('loan_id', 2)->first();
        $this->data['leader'] = $leader;

        $group_loans = Loan::where('group_id', $group->id)->where('disbursed', true)->get();
        $totalAmount = 0;
        $paidAmount = 0;
        foreach ($group_loans as $group_loan){
            $totalAmount += $group_loan->getTotalAttribute();
            $paidAmount += $group_loan->getAmountPaidAttribute();
        }
        $active_group_loans = Loan::where('group_id', $group->id)->where(['disbursed'=> true, 'settled'=> false])->get();
        $active_totalAmount = 0;
        $active_paidAmount = 0;

        foreach ($active_group_loans as $group_loan){
            $active_totalAmount += $group_loan->getTotalAttribute();
            $active_paidAmount += $group_loan->getAmountPaidAttribute();
        }
        $loans_with_arreas = Loan::where('settled', false)->where('group_id', $group->id)->whereHas('arrears')->get();
        $l = array();
        foreach ($loans_with_arreas as $lns){
            $last_payment_date = $lns->last_payment_date;
            if ($last_payment_date == null || $last_payment_date > Carbon::now()->subDays(180))
            {
                array_push($l, $lns);
            }
        }

        $arrears_count = count($l);
        $arrears_total = 0;
        foreach ($l as $t) {
            $arrears_total += $t->total_arrears;
        }
        //rolled over loans
        $rolled_over_loans = Loan::whereHas('rollover', function (Builder $query) {
            $query->whereMonth('rollover_date', '=', Carbon::now())
                ->whereYear('rollover_date', '=', Carbon::now());
        })->where(['settled' => false, 'disbursed' => true, 'group_id'=> $group->id])->get();
        $rolled_over_loans_count = $rolled_over_loans->count();
        $rolled_over_balance = 0;
        foreach ($rolled_over_loans as $lo){
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

        //non performing loans
        $non_performing_loans = array();
        $branch_unsettled_loans = $group->loans()->where(['settled' => false, 'disbursed' => true])->get();
        foreach ($branch_unsettled_loans as $lns)
        {
            $last_payment_date = $lns->last_payment_date;
            if ($last_payment_date != null && $last_payment_date < Carbon::now()->subDays(180))
            {
                array_push($non_performing_loans, $lns);
            }
        }
        $non_performing_count = count($non_performing_loans);
        $non_performing_balance = 0;
        foreach ($non_performing_loans as $lo){
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

        $this->data['loanCount'] = count($group_loans);
        $this->data['totalAmount'] = $totalAmount;
        $this->data['paidAmount'] = $paidAmount;
        $this->data['activeloanCount'] = count($active_group_loans);
        $this->data['activetotalAmount'] = $active_totalAmount;
        $this->data['activepaidAmount'] = $active_paidAmount;
        $this->data['arrearsCount'] = $arrears_count;
        $this->data['rolledOverCount'] = $rolled_over_loans_count;
        $this->data['rolledOverAmount'] = $rolled_over_balance;
        $this->data['nonPerfCount'] = $non_performing_count;
        $this->data['nonPerfAmount'] = $non_performing_balance;
        $this->data['arrearsAmount'] = $arrears_total;




        return view('pages.groups.view', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $group = Group::find(decrypt($id));
        $this->data['title'] = "Edit Group Details";
        $this->data['is_edit'] = true;
        $this->data['customer'] = Customer::find($group->leader_id);
        $this->data['group'] = $group;
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $unsettled = Loan::where(['group_id'=>$group->id , 'settled'=>false])->first();
        if ($unsettled){
            $this->data['is_unsettled'] = true;
        }else{
            $this->data['is_unsettled'] = false;
        }
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager')){
            $this->data['field_agents'] = User::role('field_agent')->where('status', true)->get();
        }
        elseif (Auth::user()->hasRole('field_agent')){
            $this->data['field_agents'] = User::where('id', Auth::user()->id)->get();
        }
        else{
            $this->data['field_agents'] = User::role('field_agent')->where(['branch_id'=>Auth::user()->branch_id ,'status'=>true])->get();
        }

        return view('pages.groups.form', $this->data);
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
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            'leader_id' => 'required|exists:customers,id',
            'prev_leader' => 'required|exists:customers,id',
            'name' => 'required',
            'customers_count' => 'required|integer|min:2|max:15',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $group = Group::find(decrypt($id));
        $loan = Loan::where(['customer_id' => $request->leader_id, 'settled' => false])->first();
        $group_loan = Loan::where(['group_id' => $group->id, 'settled' => false])->first();
        $customer = Customer::find($request->leader_id);
        //disabled this check
//        if ($loan){
//            return back()->with('error', 'Could not update group. The selected leader, '.$customer->fname. ' '. $customer->lname.' still has an unsettled loan.')->withInput();
//        }
//        if ($group_loan){
//            return back()->with('error', 'Could not update group. The selected group, has members with unsettled loans.')->withInput();
//        }
        if (isset($group)){
            $field_agent_id = $group->field_agent_id;
            if ($field_agent_id != $request->get('lf')){
                $lf = User::find($request->get('lf'));
                if ($lf){
                    $field_agent_id = $request->get('lf');
                    foreach ($group->customers()->get() as $customer){
                        $customer->update([
                            'field_agent_id' => $field_agent_id,
                            'updated_at'=>Carbon::now()
                        ]);
                    }
                }
            }
            $group->update([
                'leader_id' => $request->leader_id,
                'name' => $request->name,
                'customers_count' => $request->customers_count,
                'field_agent_id' => $field_agent_id,
                'updated_at' => Carbon::now('Africa/Nairobi'),
            ]);
            //detach previous leader
            $group->customers()->detach([$request->input('prev_leader')]);

            //reattach as group member
            $group->customers()->attach($request->input('prev_leader') === null ? [] : $request->input('prev_leader'), ['role' => 'member']);

            //check if new group leader was a member, if so detach
            $check = $group->customers()->where('customer_id', $request->input('leader_id'))->first();
            if ($check){
                if ($check->pivot->role == 'member'){
                    $group->customers()->detach([$request->input('leader_id')]);
                }
            }

            //attach new group leader
            $group->customers()->attach($request->input('leader_id') === null ? [] : $request->input('leader_id'), ['role' => 'leader']);

            return redirect()->to('/groups')->with('success', 'Successfully updated '.$request->name.' group details');
        }
        else{
            return back()->with('error', 'Could not update group. Contact Admin for assistance')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group = Group::where(['id' => decrypt($id), 'approved' => false])->first();

        $members = $group->customers()->get();
        $check = false;
        foreach ($members as $member){
            $loan = Loan::where(['customer_id'=> $member->id, 'group_id'=>$group->id, 'settled'=>false])->first();
            if ($loan){
                $check = true;
            }
        }
        if ($check){
            return back()->with('error', 'Group cannot be deleted, there is a member who still has an active loan.');
        }
        $group->delete();
        return back()->with('success', 'Successfully deleted '.$group->name );
    }

    //group data, index page
    public function data()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager')){
            $groups = DB::table('groups')
                ->join('customers', 'customers.id', '=', 'groups.leader_id')
                ->join('users', 'users.id', '=', 'groups.created_by')
                ->select('groups.*','customers.phone', DB::raw('concat(customers.fname, " ",  customers.lname) as  leader'));
        }
        elseif (Auth::user()->hasRole('field_agent')){
            $groups = DB::table('groups')
                ->join('customers', 'customers.id', '=', 'groups.leader_id')
                ->join('users', 'users.id', '=', 'groups.created_by')
                ->select('groups.*','customers.phone', DB::raw('concat(customers.fname, " ",  customers.lname) as  leader'))
                ->where('groups.field_agent_id', '=', Auth::user()->id);
        }
        else
            {
            $groups = DB::table('groups')
                ->join('customers', 'customers.id', '=', 'groups.leader_id')
                ->join('users', 'users.id', '=', 'groups.created_by')
                ->select('groups.*','customers.phone', DB::raw('concat(customers.fname, " ",  customers.lname) as  leader'))
                ->where('groups.branch_id', '=', Auth::user()->branch_id);
        }

        return Datatables::of($groups->get())
            ->addColumn('field_agent', function ($groups) {
                $user = User::find($groups->field_agent_id);
                return $user->name;
            })
            ->addColumn('created_by', function ($groups) {
                $user = User::find($groups->created_by);
                return $user->name;
            })
            ->editColumn('approved_by', function ($groups) {
                if ($groups->approved_by != null){
                    $user = User::find($groups->approved_by);
                    return $user->name;
                }else{
                    return 'Pending Approval';
                }
            })
            ->editColumn('leader', function ($groups) {
                return $groups->leader .'<br>' .' - '.  $groups->phone;
            })
            ->editColumn('approval_date', function ($groups) {
                if ($groups->approval_date != null){
                    return $groups->approval_date;
                }else{
                    return 'Pending Approval';
                }
            })
            ->addColumn('members_count', function ($groups) {
                $group = Group::find($groups->id);
                $members_count = $group->customers()->get();
                return count($members_count);
            })
            ->addColumn('status', function ($groups) {
                $group = Group::find($groups->id);
                if ($group->status == true){
                    return 'active';
                }else{
                    return 'inactive';
                }
            })
            ->addColumn('approved', function ($groups) {
                $group = Group::find($groups->id);
                if ($group->approved == true){
                    return 'approved';
                }else{
                    return 'pending';
                }
            })
            ->addColumn('members', function ($groups) {
                $group = Group::find($groups->id);
                $members = $group->customers()->get();
                $data = array();
                foreach ($members as $member){
                    array_push($data,
                        array(
                            'name' => $member->fname. ' ' .$member->lname,
                            'contact' =>$member->phone,
                            'role' => $member->pivot->role
                        ));
                }
               return $data;
            })
            ->addColumn('branch', function ($groups) {
                $branch = Branch::find($groups->branch_id);
                return $branch->bname;
            })
            ->addColumn('action', function ($group) {
                $data = encrypt($group->id);
                if (Auth::user()->hasRole('admin')){
                    return '<div class="btn-group text-center">
                                        <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                <ul class="dropdown-menu" style="">
                                                    <li><a class="dropdown-item" href="' . route('groups.show', ['id' => $data]) . '"><i class="feather icon-info text-info" ></i> View</a></li>
                                                    <li><a class="dropdown-item" href="' . route('groups.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                                                    <li><a class="dropdown-item" href="' . route('groups.suspend_group', ['id' => $data]) . '"><i class="feather icon-x-circle text-danger" ></i> Suspend</a></li>
                                                    <li><a class="dropdown-item" href="' . route('groups.reactivate_group', ['id' => $data]) . '"><i class="feather icon-check-circle text-success" ></i> Reactivate</a></li>
                                                </ul>
                                </div>';
                }
                else{
                    return '<div class="btn-group text-center">
                                        <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                <ul class="dropdown-menu" style="">
                                                    <li><a class="dropdown-item" href="' . route('groups.show', ['id' => $data]) . '"><i class="feather icon-info text-info" ></i> View</a></li>
                                                    <li><a class="dropdown-item" href="' . route('groups.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                                                </ul>
                                </div>';
                }

            })
            ->rawColumns(['action', 'leader'])
            ->toJson();
    }

    //add member modal data
    public function customer_data($id)
    {
        $group = Group::find(decrypt($id));
        $group_id = $group->id;
        $lo = Customer::where(['status'=> true, 'branch_id'=>$group->branch_id, 'field_agent_id'=>$group->field_agent_id])->select('*');
        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {
                $branch = Branch::find($lo->branch_id);
                return $branch->bname;
            })
            ->addColumn('loan_status', function ($lo) {
                $loans = Loan::where(['customer_id'=>$lo->id, 'settled'=>false])->first();
                if ($loans){
                    return '<h6><span class="badge badge-danger"><b>Unsettled</b></span></h6>';
                }else{
                    return '<h6><span class="badge badge-success"><b>Settled</b></span></h6>';
                }

            })
            ->addColumn('action', function ($lo) use ($group_id) {
                $data = $lo->id;
                return  '<a class="sel-btn btn btn-xs btn-primary"  href="' . route('groups.add_member', ['customer_id' => encrypt($data), 'group_id'=>encrypt($group_id)]) . '"><i class="feather icon-edit text-warning"></i> Add</a>';
            })
            ->rawColumns(['action', 'loan_status'])
            ->make(true);

    }

    //group leader data
    public function leader_data()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager')){
            $lo = Customer::where('status', true)->select('*');
        }elseif (Auth::user()->hasRole('field_agent')){
            $lo = Customer::where(['status'=> true, 'field_agent_id'=>Auth::user()->id])->select('*');

        }
        else{
            $lo = Customer::where(['status'=> true, 'branch_id'=>Auth::user()->branch_id])->select('*');
        }

        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {
                $branch = Branch::find($lo->branch_id);
                return $branch->bname;
            })
            ->addColumn('field_agent', function ($lo) {
               $field_agent = User::find($lo->field_agent_id);
                return $field_agent->name;
            })
            ->addColumn('loan_status', function ($lo) {
                $loans = Loan::where(['customer_id'=>$lo->id, 'settled'=>false])->first();
                if ($loans){
                    return '<h6><span class="badge badge-danger"><b>Unsettled</b></span></h6>';
                }else{
                    return '<h6><span class="badge badge-success"><b>Settled</b></span></h6>';
                }

            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;
                $fullname = $lo->fname . ' ' . $lo->lname;
                $field_agent = User::find($lo->field_agent_id);
                $field_agent = $field_agent->name;
                $field_agent_id = $lo->field_agent_id;
                return '<button type="button" data-dismiss="modal" data-id="' . $data . '" data-fullname="' . $fullname . '"   data-amount="' . $lo->prequalified_amount . '" data-idno="' . $lo->id_no . '" data-phone="' . $lo->phone . '" data-field_agent="'. $field_agent . '" data-field_agent_id="'. $field_agent_id. '" class="sel-btn btn btn-xs btn-primary"><i class="feather icon-edit text-warning"></i> Select</a>';
            })
            ->rawColumns(['action', 'loan_status'])
            ->make(true);

    }

    //add member to group
    public function add_member($group_id, $customer_id)
    {
        $group = Group::find(decrypt($group_id));
        $customer = Customer::find(decrypt($customer_id));
        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false])->first();
        if ($group->approved and $group->status == false){
            return back()->with('error', 'Members cannot be added to this group as it is currently under suspension.');
        }
        if ($loan){
            return back()->with('error', 'Could not add member. The selected customer, '.$customer->fname. ' '. $customer->lname.', still has an unsettled loan.');
        }
        $branch = Branch::find($group->branch_id);
        if ($customer->branch_id != $branch->id){
            return back()->with('error', 'Could not add member. '. $customer->fname. ' '. $customer->lname.' is not registered under '.$branch->bname.' branch');
        }
        $all_groups = Group::all();
        $check_customer = false;
        foreach ($all_groups as $all_group){
            if ($all_group->customers()->where('customer_id', $customer->id)->exists()){
                $check_customer = true;
            }
        }
        if ($check_customer){
            return back()->with('error', 'Could not register group. The selected leader, '.$customer->fname. ' '. $customer->lname.' is registered under another group');
        }
        if ($group->customers()->where('customer_id', $customer->id)->exists()){
            return redirect()->back()->with('error', 'Selected customer could not be added. '.$customer->fname.' is already a member of '. $group->name);
        }else
            {
            $max = $group->customers_count;
            $current_count = $group->customers()->count();
            if ($current_count >= $max){
                return back()->with('error', 'Max number of members ('.$max.') has been reached, change group details and try again.');
            }
            $group->customers()->attach($customer->id, ['role' => 'member']);
            return redirect()->back()->with('success', 'Successfully added '.$customer->fname.' to '. $group->name);
        }

    }

    //remove member from group
    public function remove_member($group_id, $customer_id)
    {
        $group = Group::find(decrypt($group_id));
        $customer = Customer::find(decrypt($customer_id));
        if ($group->approved == true){
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('manager')){
                $loan = Loan::where(['customer_id' => $customer->id, 'group_id'=>$group->id ,'settled' => false])->first();
                if ($loan){
                    return back()->with('error', 'Could not remove member. The selected customer, '.$customer->fname. ' '. $customer->lname.', still has an unsettled loan under this group.');
                }
                if ($group->customers()->where('customer_id', $customer->id)->exists()){
                    if ($group->customers()->where('customer_id', $customer->id)->first()->pivot->role == 'leader'){
                        return redirect()->back()->with('error', 'Group leaders cannot be removed from their groups, first change group leader and retry');
                    }
                    $group->customers()->detach([$customer->id]);
                    return redirect()->back()->with('success', $customer->fname.' has successfully been removed from this group.');
                }else
                {
                    return redirect()->back()->with('error', 'Sorry... Something went wrong, contact admin for assistance.');
                }
            }
            else{
                return redirect()->back()->with('error', 'Sorry... You are unauthorized to complete this request');
            }
        }
        else{
            $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false])->first();
            if ($loan){
                return back()->with('error', 'Could not remove member. The selected customer, '.$customer->fname. ' '. $customer->lname.', still has an unsettled loan.');
            }
            if ($group->customers()->where('customer_id', $customer->id)->exists()){
                if ($group->customers()->where('customer_id', $customer->id)->first()->pivot->role == 'leader'){
                    return redirect()->back()->with('error', 'Group leaders cannot be removed from their groups, first change group leader and retry');
                }
                $group->customers()->detach([$customer->id]);
                return redirect()->back()->with('success', $customer->fname.' has successfully been removed from this group.');
            }else
            {
                return redirect()->back()->with('error', 'Sorry... Something went wrong, contact admin for assistance.');
            }
        }



    }

    //group members data
    public function group_members($id)
    {
        $group = Group::find(decrypt($id));
        $members = $group->customers()->get();
        return DataTables::of($members)
            ->addColumn('name', function ($members){
                return $members->fname .' '.$members->lname;
            })
            ->addColumn('role', function ($members){
                return $members->pivot->role;
            })
            ->addColumn('created_at', function ($members){
                return $members->pivot->created_at;
            })
            ->addColumn('group_loan', function ($members) use ($group){
                $loans = Loan::where(['customer_id' => $members->id, 'group_id'=>$group->id])->count();
                return $loans;
            })
            ->addColumn('action', function ($members) use ($group){
                $data = encrypt($members->id);
                return '<div class="btn-group text-center">
                                        <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                <ul class="dropdown-menu" style="">
                                                    <li><a class="dropdown-item" href="' . route('groups.remove_member', ['customer_id' => $data, 'group_id'=>encrypt($group->id)]) . '"><i class="feather icon-crosshair text-danger" ></i> Remove</a></li>
                                                </ul>
                                </div>';
            })
            ->make(true);
    }

    //group approval page
    public function approval()
    {
        $this->data['title'] = "Group Approval";
        $this->data['sub_title'] = "Registered groups awaiting approval";
        $this->data['groups'] = Group::where('approved', false)->get();
        return view('pages.groups.approve', $this->data);
    }

    //approval data
    public function awaiting_approval_data()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager')){
            $groups = DB::table('groups')
                ->join('customers', 'customers.id', '=', 'groups.leader_id')
                ->join('users', 'users.id', '=', 'groups.created_by')
                ->select('groups.*','groups.name', 'customers.phone', DB::raw('concat(customers.fname, " ",  customers.lname) as  leader'))
                ->where('groups.approved', '=', false);
        }
        else
            {
            $groups = DB::table('groups')
                ->join('customers', 'customers.id', '=', 'groups.leader_id')
                ->join('users', 'users.id', '=', 'groups.created_by')
                ->select('groups.*','groups.name', 'customers.phone', DB::raw('concat(customers.fname, " ",  customers.lname) as  leader'))
                ->where('groups.approved', '=', false)
                ->where('groups.branch_id', '=', Auth::user()->branch_id);

        }

        return Datatables::of($groups->get())
            ->addColumn('checkbox', function ($groups) {
                return '<input class="mt-3" type="checkbox" name="id[]" value="' . encrypt($groups->id) . '" >';
            })
            ->addColumn('created_by', function ($groups) {
                $user = User::find($groups->created_by);
                return $user->name;
            })
            ->editColumn('approved_by', function ($groups) {
                if ($groups->approved_by != null){
                    $user = User::find($groups->approved_by);
                    return $user->name;
                }else{
                    return 'Pending Approval';
                }
            })
            ->editColumn('approval_date', function ($groups) {
                if ($groups->approval_date != null){
                    return $groups->approval_date;
                }else{
                    return 'Pending Approval';
                }
            })
            ->addColumn('members_count', function ($groups) {
                $group = Group::find($groups->id);
                $members_count = $group->customers()->get();
                return count($members_count);
            })
            ->addColumn('status', function ($groups) {
                $group = Group::find($groups->id);
                if ($group->status == true){
                    return 'active';
                }else{
                    return 'inactive';
                }
            })
            ->addColumn('approved', function ($groups) {
                $group = Group::find($groups->id);
                if ($group->approved == true){
                    return 'approved';
                }else{
                    return 'pending';
                }
            })
            ->addColumn('members', function ($groups) {
                $group = Group::find($groups->id);
                $members = $group->customers()->get();
                $data = array();
                foreach ($members as $member){
                    array_push($data,
                        array(
                            'name' => $member->fname. ' ' .$member->lname,
                            'contact' =>$member->phone,
                            'role' => $member->pivot->role
                        ));
                }
                return $data;
            })
            ->addColumn('branch', function ($groups) {
                $branch = Branch::find($groups->branch_id);
                return $branch->bname;
            })
            ->addColumn('action', function ($group) {
                $data = encrypt($group->id);
                return '<div class="btn-group text-center">
                                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                            <li><a class="approve" href="' . route('groups.approve_single', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Approve</a></li>
                                                            <li><a class="ldelete" href="' . route('groups.delete', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                                                        </ul>
                                        </div>';
            })
            ->rawColumns(['action','checkbox'])
            ->toJson();
    }

    //approve single group
    public function approve_single($id)
    {
        Group::find(decrypt($id))->update(['approved' => true, 'status' => true, 'approval_date' => Carbon::now('Africa/Nairobi'), "approved_by" => Auth::user()->id]);
        $group = Group::find(decrypt($id));
        //send sms to group members?

        return back()->with('success', 'Successfully approved '.$group->name);

    }

    //approve multiple
    public function approve_multiple(Request $request)
    {
        $result = DB::transaction(function () use ($request) {
            foreach ($request->id as $id) {
                Group::find(decrypt($id))->update(['approved' => true, 'status' => true, 'approval_date' => Carbon::now('Africa/Nairobi'), "approved_by" => Auth::user()->id]);
            }
            return true;
        });
        if ($result) {
            return back()->with('success', 'Successfully approved the selected groups');
        } else {
            return back()->with('error', 'Sorry, something went wrong, try to approve the groups one at a time');
        }
    }

    //suspend or set group as inactive
    public function suspend_group($id)
    {
        $group = Group::find(decrypt($id));
        $members = $group->customers()->get();

        if ($group->approved == false){
            return back()->with('error', 'Group is yet to be approved');
        }
        if ($group->status == false){
            return back()->with('success', 'Group is already suspended');
        }
        $check = false;
        foreach ($members as $member){
            $loan = Loan::where(['customer_id'=> $member->id, 'group_id'=>$group->id, 'settled'=>false])->first();
            if ($loan){
                $check = true;
            }
        }
        if ($check){
            return back()->with('error', 'Group cannot be suspended, there is a member who still has an active loan.');
        }
        if (Auth::user()->hasRole('admin')){
            Group::find(decrypt($id))->update(['status' => false]);
            return back()->with('success', 'Successfully set '.$group->name.' as suspended');
        }else{
            return back()->with('error', 'Action Not Allowed, contact admin for support');
        }
    }

    //reactivate suspended group
    public function reactivate_group($id)
    {
        $group = Group::find(decrypt($id));
        if ($group->approved == false){
            return back()->with('error', 'Group is yet to be approved');
        }
        if ($group->status == true){
            return back()->with('success', 'Group is already active');
        }
        if (Auth::user()->hasRole('admin')){
            Group::find(decrypt($id))->update(['status' => true]);
            return back()->with('success', 'Successfully reactivated '.$group->name);
        }else{
            return back()->with('error', 'Action Not Allowed, contact admin for support');
        }
    }

    //group loans data
    public function group_loans_data($id)
    {
        $group = Group::find(decrypt($id));
        $lo = DB::table('loans')
            ->join('customers', 'customers.id', '=', 'loans.customer_id')
            ->join('products', 'products.id', '=', 'loans.product_id')
            ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
            ->where('loans.group_id', '=', $group->id);

        return Datatables::of($lo)
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
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');


                return number_format($payments, 1);


            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                }
                else{
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));

                }

                return number_format($total, 1);


            })

            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest;
                }
                else{
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100));


                }
                $balance = $total - $payments;



                return number_format($balance, 1);


            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);
                return '<div class="btn-group text-center">
                                        <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                <ul class="dropdown-menu" style="">
                                                    <li><a class="dropdown-item" href="' . route('loans.show', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> View</a></li>
                                                </ul>
                                </div>';
            })
            ->rawColumns(['action'])
            //->make(true);
            ->toJson();

    }
}
