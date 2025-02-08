<?php

namespace App\Http\Controllers;

use AfricasTalking\SDK\AfricasTalking;
use App\Jobs\Sms;
use App\models\Branch;
use App\models\Loan;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CollectionOfficerController extends Controller
{
    public function __construct()
    {
        //$this->middleware(['role:admin']);
        $this->middleware('role:admin|customer_informant|manager|accountant|field_agent|sector_manager');

    }
    public function index()
    {
        //$this->data['field_agents'] = LoanOfficer::all();
        //  $this->data['branches'] = Branch::all();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['title'] = "Collection Officers";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['title'] = "Collection Officers ".$branch->bname;
        }

        $this->data['sub_title'] = "List of all collection officers";

        return view('pages.registry.collectionOfficers.index', $this->data);

    }
    public function create()
    {

        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['title'] = "Create New Collection Officer";
        $this->data['is_edit'] = false;

        return view('pages.registry.collectionOfficers.form', $this->data);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users',
            'branch_id' => 'required|exists:branches,id',
            'field_agent_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => '254' . substr($request->phone, -9),
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'field_agent_id' => $request->field_agent_id,
            'password' => Hash::make('254' . substr($request->phone, -9)),
            'salary' => $request->salary,
        ]);

        $user->assignRole('collection_officer');
        $phone = '+254' . substr($request->phone, -9) ;
        $message = "Your LITSA CREDIT account has been created. Username: ".$request->email." Password: "."254" . substr($request->phone, -9);
        $user_type = true;
        $us = User::where('email', $request->email)->first();
        $fnd = dispatch(new Sms(
            $phone, $message,$us,$user_type
        ));

        return back()->with('success', 'Successfully created Collection Officer');

    }
    public function edit($id)
    {
        $user = User::find($id);
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['title'] = "Edit Collection Officer ". $user->name;
        $this->data['field_agent'] = $user;
        $this->data['is_edit'] = true;

        return view('pages.registry.collectionOfficers.form', $this->data);
    }

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

        return back()->with('success', 'Successfully updated Collection Officer');
    }


    public  function data(){
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = User::role('collection_officer');
        }
        else{
            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $lo = User::role('collection_officer')->where('branch_id', $branch->id);
        }

        return Datatables::of($lo)
            ->editColumn('status', function ($lo) {
                if($lo->status){
                    return 'Active';
                } else {
                    return 'Inactive';
                }
            })
            ->addColumn('ro', function ($lo) {
                return DB::table('users')->where(['id' => $lo->field_agent_id])->first()->name;
            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;

                return '<div class="btn-group text-center">
                            <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                <li><a href="' . route('field_agent.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning"></i> Edit</a></li>
                                    <li><a href="' . route('admin.deactivate', ['id' => encrypt($data)]) . '"><i class="feather icon-eye text-info"></i> Change Status</a></li>
                            </ul>
                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function sendsms()
    {
        $user_type = true;
        $us = User::where('email', 'muriithicharles846@gmail.com')->first();
        $username = config('app.AT_USERNAME');
        $apiKey = config('app.AT_KEY');
        $AT = new AfricasTalking($username, $apiKey);
        $sms = $AT->sms();
        $sms->send([
            'from' => config('app.AT_FROM'),
            'to' =>  "254111333000",
            'message' => 'test'
        ]);
    }
}
