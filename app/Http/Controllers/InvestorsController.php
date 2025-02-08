<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\Branch;
use App\models\Expense;
use App\models\Investment;
use App\models\User_payments;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class InvestorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('role:admin|accountant');
        // Alternativly
    }
    public function index()
    {
        $this->data['title'] = "System Investors";
        $this->data['sub_title'] = "List of all investors";

        return view('pages.investors.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['branches'] = Branch::all();
        $this->data['roles'] = Role::where('name', 'investor')->get();
        $this->data['title'] = "Create New Investor";
        $this->data['is_edit'] = false;

        return view('pages.investors.form', $this->data);
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
            'phone' => 'required|digits:10',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|exists:roles,name',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $result = DB::transaction(function () use($request) {
            $user = User::create([
                'name' => $request->name,
                'phone' => '254' . substr($request->phone, -9),
                'email' => $request->email,
                'branch_id' => $request->branch_id,
                'password' => Hash::make('254' . substr($request->phone, -9))

            ]);
            $user->assignRole($request->role);

            $investment = Investment::create([
                'user_id' => User::where('email', $request->email)->first()->id,
                'amount' => $request->amount,
                'date_payed' => Carbon::now(),
                'transaction_no' => "IN/".date('ym').'/'.mt_rand(1000, 10000)
            ]);

            return true;
        });

        if ($result){
            $phone = '+254' . substr($request->phone, -9) ;
            $message = "Dear Partner, Your LITSA CREDIT account has been created. Username: ".$request->email." Password: "."254" . substr($request->phone, -9);
            $user = User::where('email', $request->email)->first();
            $type = true;

            $fnd = dispatch(new Sms(
                $phone, $message,$user, $type
            ));
            return back()->with('success', 'Successfully created Investor');
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
        $user = User::find(decrypt($id));
        $this->data['investor'] = $user;
        $this->data['branches'] = Branch::all();
        $this->data['roles'] = Role::where('name', 'investor')->get();
        $this->data['title'] = $user->name;
        $this->data['sub_title'] = "Details ";

        if ($user->hasRole('investor')){
            $investments = Investment::where('user_id', $user->id)->get();
            $this->data['investments'] =  $investments->sum('amount');
        }

        return view('pages.investors.view', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $inv = User::find(decrypt($id));
        $this->data['investor'] = $inv;
        $this->data['roles'] = Role::where('name', 'investor')->get();
        $this->data['title'] = "Edit New Investor";
        $this->data['is_edit'] = true;
        $this->data['branches'] = Branch::where('id', $inv->branch_id)->get();

        return view('pages.investors.form', $this->data);
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
            'phone' => 'required|digits:12',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|exists:roles,name'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('id', decrypt($id))->update([
            'name' => $request->name,
            'phone' => '254' . substr($request->phone, -9),
            'email' => $request->email,
            'branch_id' => $request->branch_id,
        ]);

        return back()->with('success', 'Successfully updated Investor');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    //investors data
    public function investors_data(){

        $lo = User::role(['investor'])->get();

        return Datatables::of($lo)
            ->editColumn('status', function ($lo) {
                if($lo->status){
                    return 'Active';
                } else {
                    return 'Inactive';
                }
            })

            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);

                return '<div class="btn-group text-center">
                            <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                <li><a href="' . route('investors.show', ['id' => $data]) . '"><i class="feather icon-eye text-info"></i> View</a></li>
                                <li><a href="' . route('investors.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning"></i> Edit</a></li>
                                <li><a href="' . route('admin.deactivate', ['id' => $data]) . '"><i class="feather icon-eye text-info"></i> Change Status</a></li>
                            </ul>
                        </div>';
            })
            ->rawColumns(['action'])
           // ->make(true);
         ->toJson();
    }

    public function investor_investments($id){

        $investments = Investment::where('user_id', decrypt($id))->get();

        return DataTables::of($investments)
            ->editColumn('amount', function ($investments){
                return number_format($investments->amount, 2);
            })
            ->toJson();
    }

    //add investment
    public function  add_investment(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'date_payed' => 'required',
            'user' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $investment = Investment::create([
            'user_id' => $request->user,
            'amount' => $request->amount,
            'date_payed' => $request->date_payed,
            'transaction_no' => "IN/".date('ym').'/'.mt_rand(1000, 10000)
        ]);

        return back()->with('success', "successfully added investment");
    }

    //ajax investors

    public function ajax_investors($id){

        //$fill = DB::table('volunteers')->where(['location_id'=>$id, 'is_active'=>true, 'is_full'=>false])->get();
        $fill = User::role(['investor'])->where('branch_id', $id)->get();

        $response='<option disabled selected>Select Investor </option>';

        foreach ($fill as $fills){
            $response.='<option value="'.$fills->id.'" >'.$fills->name.'</option>';
        }

        return response()->json(['success'=>true,'info'=>$response]);
    }

    //chosen investor investment
    public function ajax_investors_investments($id){
        //$fill = User::role(['investor'])->where('branch_id', $id)->get();
        $fill = Investment::where('user_id', $id)->get();
        $withdrawals = User_payments::where('user_id', $id)->whereHas('expense', function ($q){
            $q->where('expense_type_id', 2);
        })->sum('amount');

        $inv_rem = $fill->sum('amount') - $withdrawals;
        //dd($fill->sum('amount'));
        $response=$inv_rem;

       /* foreach ($fill as $fills){
            $response.='<option value="'.$fills->id.'">'.$fills->name.'</option>';

        }*/

        return response()->json(['success'=>true,'info'=>$response]);
    }

    //investment_withdrawal_post
    public function investment_withdrawal_post(Request $request){

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'branch_id' => 'required|exists:branches,id',
            'investor' => 'required|exists:users,id',



        ]);
       // dd($request->all());
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();


        }

        $fill = Investment::where('user_id', $request->investor)->get();
        $withdrawals = User_payments::where('user_id', $request->investor)->whereHas('expense', function ($q){
            $q->where('expense_type_id', 2);
        })->sum('amount');

        $inv_rem = $fill->sum('amount') - $withdrawals;
        //dd($fill->sum('amount'));
       // $response=$inv_rem;
        if ($request->amount > $inv_rem){
            return back()->with('error', 'You cannot withdraw more than your investments')->withInput();

        }

        $expense = Expense::create([
            'expense_type_id' => 2,
            'amount' => $request->amount,
            'branch_id' => $request->branch_id,
            'date_payed' => Carbon::now(),
            'description' => 'Investments settlement',
            'paid_by' => Auth::user()->id
        ]);
        $pay = User_payments::create([
            'user_id' => $request->investor,
            'expense_id' => $expense->id,
            'amount' => $request->amount,
            'date_payed' => Carbon::now(),
            'channel' => 'Manual',
            'transaction_id' => date('y-m').'/'.mt_rand(1000, 10000),
        ]);

        return back()->with('success', 'Successfully added the withdrawal')->withInput();



    }


    /****************************interest Payment**********************/
    public function interest_payment(Request $request){
      //  dd($request->all());

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'branch_id' => 'required|exists:branches,id',
            'investor' => 'required|exists:users,id',



        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();


        }



        $expense = Expense::create([
            'expense_type_id' => 3,
            'amount' => $request->amount,
            'branch_id' => $request->branch_id,
            'date_payed' => Carbon::now(),
            'description' => 'Interest Settlement',
            'paid_by' => Auth::user()->id
        ]);
        $pay = User_payments::create([
            'user_id' => $request->investor,
            'expense_id' => $expense->id,
            'amount' => $request->amount,
            'date_payed' => Carbon::now(),
            'channel' => 'Manual',
            'transaction_id' => date('y-m').'/'.mt_rand(1000, 10000),
        ]);

        return back()->with('success', 'Successfully added the Interest Payment')->withInput();



    }

}
