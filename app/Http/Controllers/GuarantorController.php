<?php

namespace App\Http\Controllers;

use App\models\Branch;
use App\models\Business_type;
use App\models\Customer;
use App\models\Guarantor;
use App\models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class GuarantorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['guarantors'] = Guarantor::all();
        $this->data['branches'] = Branch::all();
        $this->data['title'] = "Guarantors";
        $this->data['sub_title'] = "List of all Guarantors";

        return view('pages.registry.guarantors.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['industries'] = Industry::all();
        $this->data['business'] = Business_type::all();
        $this->data['title'] = "Create New Guarantor";
        $this->data['is_edit'] = false;

        return view('pages.registry.guarantors.form', $this->data);
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
            'gname' => 'required|min:3',
            'gphone' => 'required|digits:10|unique:guarantors',
            'gid' => 'required',
            'location' => 'required',
            'gdob' => 'required',
            /*'latitude' => 'required',
            'longitude' => 'required',*/
            'marital_status' => 'required',
            'industry_id' => 'required|exists:industries,id',
            'business_id' => 'required|exists:business_types,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Guarantor::create([
            'gname' => $request->gname,
            'gphone' => '254' . substr($request->gphone, -9),
            'gdob' => $request->gdob,
            'gid' => $request->gid,
            'location' => $request->location,
            'latitude' => 0,
            'longitude' => 0,
            'marital_status' => $request->marital_status,
            'industry_id' => $request->industry_id,
            'business_id' => $request->business_id,
        ]);

        return back()->with('success', 'Successfully created Guarantor');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Guarantor::find(decrypt($id));
        $this->data['guarantor'] = $user;
        $this->data['customer'] = Customer::where('guarantor_id', $user->id)->first();
        $this->data['title'] = $user->gname;
        $this->data['industries'] = Industry::all();
        $this->data['business'] = Business_type::all();

        $this->data['sub_title'] = "Details ";

        return view('pages.registry.guarantors.view', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->data['industries'] = Industry::all();
        $this->data['business'] = Business_type::all();
        $this->data['title'] = "Edit Guarantor";
        $this->data['guarantor'] = Guarantor::where('id', decrypt($id))->get();

        $this->data['is_edit'] = true;

        return view('pages.registry.guarantors.form', $this->data);
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
            'gname' => 'required|min:3',
            'gphone' => 'required',
            'gid' => 'required',
            'location' => 'required',
            'gdob' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'marital_status' => 'required',
            'industry_id' => 'required|exists:industries,id',
            'business_id' => 'required|exists:business_types,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Guarantor::where('id', decrypt($id))->update([
            'gname' => $request->gname,
            'gphone' => '254' . substr($request->gphone, -9),
            'gdob' => $request->gdob,
            'gid' => $request->gid,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'marital_status' => $request->marital_status,
            'industry_id' => $request->industry_id,
            'business_id' => $request->business_id,

        ]);

        return back()->with('success', 'Successfully created Guarantor');
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

    public  function data(){
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = Guarantor::all();
        }
        else{
            /*$branch = Branch::where('id', Auth::user()->branch_id)->first();
            $lo = $branch->guarantors()->get();*/
            $lo = Guarantor::all();
        }

        return Datatables::of($lo)
            /*  ->editColumn('status', function ($lo) {
                if($lo->status){
                    return 'Active';
                }
                else{
                    return 'Inactive';
                }
            })*/
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);

                return '<div class="btn-group text-center">
                            <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="' . route('guarantors.show', ['id' => $data]) . '"><i class="feather icon-eye text-info"></i> View More</a>
                                    </li>
                                </ul>
                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);

    }

    /*********************************send sms form*******************/
    public function guarantors_sms(Request $request){
        //dd(\Request::route()->getName());

        $this->data['title'] = "SMS All Guarantors ";
        $this->data['is_selected'] =  false;
        $this->data['is_customer'] =  false;
        $this->data['is_guarantor'] =  true;
        return view('pages.registry.prospects.sms', $this->data);
    }
}
