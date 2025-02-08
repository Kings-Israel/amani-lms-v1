<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\Branch;
use App\models\Customer;
use App\models\CustomerSms;
use App\models\Installment;
use App\models\Investment;
use App\models\Loan;
use App\models\Loan_Default;
use App\models\Msetting;
use App\models\Payment;
use App\models\Regpayment;
use App\models\Setting;
use App\models\UserSms;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['title'] = "System Users";
        $this->data['sub_title'] = "List of all users";

        return view('pages.admin.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['roles'] = Role::where('name', '!=', 'investor')->get();
        $this->data['title'] = "Create New System User";
        $this->data['is_edit'] = false;
        return view('pages.admin.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric|unique:users',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|exists:roles,name'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $field_agent_id = null;
        // if ($request->role == 'collection_officer'){
        //     if (isset( $request->field_agent_id)){
        //         $field_agent_id = $request->field_agent_id ;
        //     } else {
        //         return back()->with('error', 'Provide the loan officer');
        //     }
        // }

        $password = Str::random(8);

        $user = User::create([
            'name' => $request->name,
            'phone' => '254' . substr($request->phone, -9),
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'salary' => $request->salary,
            'password' => Hash::make($password),
            'field_agent_id' => $field_agent_id,
        ]);

        $user->assignRole($request->role);

        if ($request->role != 'Intern'){
            $phone = '+254' . substr($request->phone, -9);
            $message = "Your LITSA CREDIT account has been created. Username: " . $request->email . " Password: " . $password;
            $user_type = true;
            $us = User::where('email', $request->email)->first();

            $fnd = dispatch(new Sms(
                $phone, $message, $us, $user_type
            ));
        }

        return back()->with('success', 'Successfully created user');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        $this->data['user'] = $user;
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['los'] = User::role('field_agent')->where(['branch_id' => $user->branch_id, 'status' => true])->select(['id', 'name'])->get();
        $this->data['roles'] = Role::where('name', '!=', 'investor')->get();
        $this->data['title'] = $user->name;
        $this->data['sub_title'] = "Details ";

        if ($user->hasRole('investor')) {
            $investments = Investment::where('user_id', $user->id)->get();
            $this->data['investments'] = $investments;

        }
        return view('pages.admin.view', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'required|digits:12',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|exists:roles,name'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $field_agent_id = null;
        if ($request->role == 'collection_officer') {

            if (isset( $request->field_agent_id)){
                $field_agent_id = $request->field_agent_id ;
            } else {
                return back()->with('error', 'Provide the loan officer');
            }
        }
        //check user email and phone unique
        $u = User::find(decrypt($id));
        if ($u->email != $request->email){
            $uemails = User::where('email', $request->email)->first();
            if ($uemails){
                return back()->with('error', $request->email.' has been used');
            }
        }
        elseif ($u->phone != '254' . substr($request->phone, -9)){
            $uphone = User::where('phone', $request->phone)->first();
            if ($uphone){
                return back()->with('error', $request->phone.' has been used');
            }
        }
        if ($request->password != null) {
            $user = User::where('id', decrypt($id))->update([
                'name' => $request->name,
                'phone' => '254' . substr($request->phone, -9),
                'email' => $request->email,
                'branch_id' => $request->branch_id,
                'salary' => $request->salary,
                'field_agent_id' => $field_agent_id,
                'password' => Hash::make($request->password)
            ]);
        } else {
            $user = User::where('id', decrypt($id))->update([
                'name' => $request->name,
                'phone' => '254' . substr($request->phone, -9),
                'email' => $request->email,
                'branch_id' => $request->branch_id,
                'salary' => $request->salary,
                'field_agent_id' => $field_agent_id

            ]);
        }
        $fetch_user = User::find(decrypt($id));

       // dd($fetch_user->roles );
        //detach previous role
        foreach ($fetch_user->roles as $role){
           // dd($role->name);
            //check if the user has loan officer
                if ($role->name == "field_agent" && $request->role != "field_agent"){
                    //check if he has active customers
                    $cus = DB::table('customers')->where(['field_agent_id' => $fetch_user->id])->first();
                    $co = DB::table('users')->where(['field_agent_id' => $fetch_user->id])->first();
                    if ($cus){
                        return back()->with('error', 'We cannot delete loan officer role because he has existing customers');
                    } elseif ($co){
                        return back()->with('error', 'We cannot delete loan officer role because he has an attached collection officer');
                    }
                }

            $fetch_user->removeRole($role->name);
        }
        //assign new role
        $new_role = $fetch_user->assignRole($request->role);
        if($user){
            return back()->with('success', 'Successfully updated user');
        }else{
            return back()->with('error', 'Failed to update user, kindly try again');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('id', decrypt($id))->delete();

        return back()->with('success', 'successfully deleted user');
    }

    public function data()
    {
        $lo = User::where('id', '!=', Auth::user()->id)->select('*')->get()->each->setAppends([]);
        return Datatables::of($lo)
            ->editColumn('status', function ($lo) {
                if ($lo->status) {
                    return '<h6><span class="badge badge-success"><b>ACTIVE</b></span></h6>';
                } else {
                    return '<h6><span class="badge badge-danger"><b>INACTIVE</b></span></h6>';
                }
            })
            ->addColumn('role', function ($lo) {
                $role = explode('_', $lo->roles()->first()->name);
                return array_key_exists(1, $role) ? Str::title($role[0]) . ' ' . Str::title($role[1]) : Str::title($role[0]);
            })
            ->editColumn('branch', function ($lo) {
                $branch = Branch::find($lo->branch_id);
                if ($branch){
                    return $branch->bname;
                } else {
                    return '--';
                }
            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;
                return '<div class="btn-group text-center">
                            <a type="button" class="btn btn-primary btn-sm" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="color: white"></i> </a>
                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                <li><a href="' . route('admin.show', ['id' => $data]) . '"><i class="feather icon-eye text-info"></i> View</a></li>
                                <li><a href="' . route('admin.deactivate', ['id' => encrypt($data)]) . '"><i class="feather icon-edit text-warning"></i> Change Status</a></li>
                            </ul>
                        </div>';
               })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    /*****************************change status of admin***********************/
    public function deactivate($id){
        $admin = User::where('id', decrypt($id))->first();
       // dd(decrypt($id));
        if ($admin->status){
            $admin->update(['status' => false]);
            return back()->with('success', 'Successfully Deactivated '.$admin->name);
        }
        else{
            $admin->update(['status' => true]);
            return back()->with('success', 'Successfully Activated '.$admin->name);
        }
    }
    /*****************************view specific user***************************/

    public function view($id)
    {
        $user = User::find($id);
        $this->data['user'] = $user;
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['roles'] = Role::where('name', '!=', 'field_agent')->get();


        $this->data['title'] = $user->name;
        $this->data['sub_title'] = "Details ";


        return view('pages.admin.view', $this->data);


    }

    //system setting
    public function settings()
    {
        $this->data['settings'] = Setting::first();
        $this->data['title'] = "System Settings";
        $this->data['sub_title'] = "List of set settings";
        $this->data['is_edit'] = false;

        return view('pages.admin.settings', $this->data);
    }

    //store settings
    public function settings_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_fee' => 'required|numeric|gt:0',
            'loan_processing_fee' => 'required|numeric|gt:0',
            'rollover_interest' => 'required|numeric|gt:0',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $setting = Setting::first();

        if ($setting) {
            $setting->update($request->input());
        } else {
            Setting::create($request->input());
        }
        return back()->with('success', 'Successfully updated settings');
    }

    public function changes()
    {
        $instals = Installment::all();
        foreach ($instals as $instal){
            $instal->update([
                'interest_payment_date' => $instal->last_payment_date
            ]);
        }

        echo 'done1';





    }

    public function customer_sms(){
        $this->data['title'] = "Customer SMS";
        $this->data['sub_title'] = "All Customer Sms";
        $sms = DB::table('customer_sms')->select('*')->orderBy('id', 'DESC')->count();
        $this->data['count'] = $sms;


        return view('pages.admin.customer_sms', $this->data);
    }

    public function customer_sms_data()
    {
        $sms = DB::table('customer_sms')->select('*')->orderBy('id', 'DESC');
        return DataTables::of($sms)
            ->editColumn('phone', function ($sms){
                if ($sms->phone == null){
                   // $user = Customer::find($sms->customer_id);
                    $user = DB::table('customers')->where('id', $sms->customer_id)->first();
                    return $user->phone;
                }
                else
                {
                    return $sms->phone;
                }
            })
            ->make(true);
    }

    public function system_sms(){
        $this->data['title'] = "System SMS";
        $this->data['sub_title'] = "All System Sms";


        return view('pages.admin.system_sms', $this->data);
    }

    public function system_sms_data()
    {
        $sms = UserSms::select('*')->orderBy('id', 'DESC');
        return DataTables::of($sms)
            ->editColumn('phone', function ($sms){
                if ($sms->phone == null){
                    $user = User::find($sms->user_id);
                    return $user->phone;
                }
                else
                {
                    return $sms->phone;
                }
            })
            ->make(true);
    }

    public function installments_change(){


    }

    public function view_users_last_seen()
    {
        $this->data['title'] = "System Users Login Status";
        $this->data['sub_title'] = "View Online Users and their Last Seen";
        return view('pages.admin.users_last_seen', $this->data);
    }

    public function view_users_last_seen_data()
    {
        $data = User::where('status', '=', true)->get(['id', 'name', 'phone', 'last_seen'])->each->setAppends([]);
        return DataTables::of($data)
            ->addColumn('online_status', function ($data){
                if(Cache::has('is_online' . $data->id)){
                    return ' <span class="badge badge-success" style="font-size: medium">Online</span> ';
                }else{
                    return '<span class="badge badge-secondary" style="font-size: medium">Offline</span>';
                }
            })
            ->addColumn('last_seen_diff', function ($data) {
                if ($data->last_seen != null){
                    return Carbon::parse($data->last_seen)->diffForHumans();
                }else{
                    return '--';
                }
            })
            ->rawColumns(['online_status'])
            ->make(true);
    }

    public function removeBranch()
    {
        $remove_branch = Branch::where('bname', 'Bungoma Branch')->first();
        $branch = Branch::where('bname', 'Bungoma')->first();

        $users = User::where('branch_id', $remove_branch)->get();
        foreach ($users as $user) {
            $user->update(['branch_id' => $branch->id]);
        }

        $customers = Customer::where('branch_id', $remove_branch->id)->get();
        foreach ($customers as $customer) {
            $customer->update(['branch_id' => $branch->id]);
        }

        $remove_branch->delete();
    }
}
