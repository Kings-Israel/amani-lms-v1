<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\Branch;
use App\models\RoTarget;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class LoanOfficerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware(['role:admin'/*,'permission:publish articles|edit articles'*/]);
    }

    public function index()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['title'] = "Field Agents";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['title'] = "Field Agents ".$branch->bname;
        }

        $this->data['sub_title'] = "List of all Field Agents";

        return view('pages.registry.loanOfficer.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['title'] = "Create New Field Agent";
        $this->data['is_edit'] = false;

        return view('pages.registry.loanOfficer.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'phone' => 'required|digits:10|unique:users',
            'branch_id' => 'required|exists:branches,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => '254' . substr($request->phone, -9),
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'password' => Hash::make('254' . substr($request->phone, -9)),
            'salary' => $request->salary,
        ]);

        $user->assignRole('field_agent');
        $phone = '+254' . substr($request->phone, -9) ;
        $message = "Your LITSA CREDIT account has been created. Username: ".$request->email." Password: "."254" . substr($request->phone, -9);
        $user_type = true;
        $us = User::where('email', $request->email)->first();
        $fnd = dispatch(new Sms(
            $phone, $message,$us,$user_type
        ));

        return back()->with('success', 'Successfully created Field Agent');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['title'] = "Edit Field Agent ". $user->name;
        $this->data['field_agent'] = $user;
        $this->data['is_edit'] = true;

        return view('pages.registry.loanOfficer.form', $this->data);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'required',
            //'branch_id' => 'required|exists:branches,id'

        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('id', $id)->update([
            'name' => $request->name,
            'phone' => '254' . substr($request->phone, -9),
            'email' => $request->email,
            //'branch_id' => $request->branch_id,
            'password' => Hash::make('254' . substr($request->phone, -9)),
            'salary' => $request->salary,
        ]);

        return back()->with('success', 'Successfully updated Loan Officer');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function del($id)
    {
        $customers = DB::table('customers')->where(['field_agent_id' => decrypt($id)])->count();

        if ($customers > 0){
            return back()->with('error', 'You cannot delete an RO who have customers. Kindly Transfer customers');
        }
        $user = User::where('id', decrypt($id))->delete();
        return back()->with('success', 'successfully deleted the RO');
    }

    /**

     *Get loan officer data

     */
    public  function data(){
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = User::role('field_agent');
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first();

            $lo = User::role('field_agent')->where('branch_id', $branch->id);
        }

        return Datatables::of($lo)
            ->editColumn('status', function ($lo) {
                if($lo->status){
                    return 'Active';
                } else {
                    return 'Inactive';
                }
            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;

                return '<div class="btn-group text-center">
                            <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                <li><a href="' . route('field_agent.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning"></i> Edit</a></li>
                                <li><a href="' . route('admin.deactivate', ['id' => encrypt($data)]) . '"><i class="feather icon-eye text-info"></i> Change Status</a></li>
                                <li><a href="' . route('field_agent.del', ['id' => encrypt($data)]) . '"><i class="feather icon-edit text-warning"></i> Delete</a></li>
                            </ul>
                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);

    }

    public function branch_ros($id){

        $los = User::role('field_agent')->where(['branch_id' => $id, 'status' => true])->select(['id', 'name'])->get();
        //dd($los);
        $response ="";

        foreach ($los as $lo){
            $response.='<option value="'.$lo->id.'">  '.$lo->name.'  </option>';
        }

        return response()->json([
            'status' => 'success',
            'ros' => $response,
            'message' => 'success',

        ], 200);
    }
}
