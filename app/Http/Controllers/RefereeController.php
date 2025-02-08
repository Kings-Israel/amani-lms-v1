<?php

namespace App\Http\Controllers;

use App\models\Branch;
use App\models\Business_type;
use App\models\Customer;
use App\models\Referee;
use App\models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class RefereeController extends Controller
{
    public function index()
    {
        $this->data['referees'] = Referee::all();
        $this->data['branches'] = Branch::all();
        $this->data['title'] = "Referees";
        $this->data['sub_title'] = "List of all Referees";

        return view('pages.registry.referees.index', $this->data);
    }

    public function create()
    {
        $this->data['customers'] = Customer::doesntHave('referees')->get();
        $this->data['business'] = Business_type::all();
        $this->data['title'] = "Create New Referee";
        $this->data['is_edit'] = false;

        return view('pages.registry.referees.form', $this->data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|min:3',
            'phone_number' => 'required|digits:10|unique:referees,phone_number',
            'id_number' => 'required',
            'customer_id' => 'required|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $referee = Referee::create([
            'full_name' => $request->full_name,
            'phone_number' => '254' . substr($request->phone_number, -9),
            'id_number' => $request->id_number,
        ]);

        $customer = Customer::find($request->customer_id);
        $customer->referees()->syncWithoutDetaching([$referee->id]);

        return back()->with('success', 'Successfully created Referee and linked to Customer');
    }

    public function show($id)
    {
        $referee = Referee::find(decrypt($id));
        $this->data['referee'] = $referee;

        $this->data['customer'] = $referee->customer->first();
        $this->data['title'] = $referee->rname;
        $this->data['industries'] = Industry::all();
        $this->data['business'] = Business_type::all();
        $this->data['sub_title'] = "Details";

        return view('pages.registry.referees.view', $this->data);
    }


    public function edit($id)
    {
        $this->data['industries'] = Industry::all();
        $this->data['business'] = Business_type::all();
        $this->data['title'] = "Edit Referee";
        $this->data['referee'] = Referee::where('id', decrypt($id))->get();
        $this->data['is_edit'] = true;

        return view('pages.registry.referees.form', $this->data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|min:3',
            'phone_number' => 'required',
            'id_number' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Referee::where('id', decrypt($id))->update([
            'full_name' => $request->full_name,
            'phone_number' => '254' . substr($request->phone_number, -9),
            'id_number' => $request->id_number,
        ]);

        return back()->with('success', 'Successfully updated Referee');
    }

    public function destroy($id)
    {
        //
    }

    public function data()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $referees = Referee::with('customer')->orderBy('created_at', 'desc')->get(); // Order by most recent
        } else {
            $referees = Referee::with('customer')->orderBy('created_at', 'desc')->get(); // Order by most recent
        }

        return Datatables::of($referees)
            ->addColumn('action', function ($referee) {
                $data = encrypt($referee->id);

                return '<div class="btn-group text-center">
                            <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="' . route('referee.show', ['id' => $data]) . '"><i class="feather icon-eye text-info"></i> View More</a>
                                    </li>
                                </ul>
                        </div>';
            })
            ->addColumn('customer_name', function ($referee) {
                $customer = $referee->customer->first();
                if ($customer) {
                    $full_name = trim($customer->fname . ' ' . $customer->mname . ' ' . $customer->lname);
                    return $full_name ?: 'N/A';
                }
                return 'N/A';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function referees_sms(Request $request)
    {
        $this->data['title'] = "SMS All Referees";
        $this->data['is_selected'] = false;
        $this->data['is_customer'] = false;
        $this->data['is_referee'] = true;

        return view('pages.registry.prospects.sms', $this->data);
    }
}
