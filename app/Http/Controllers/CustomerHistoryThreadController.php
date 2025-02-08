<?php

namespace App\Http\Controllers;

use App\models\Customer;
use App\models\CustomerHistoryThread;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CustomerHistoryThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $customer_identifier
     * @return View
     */
    public function list_customer_thread($customer_identifier): View
    {
        $customer = Customer::query()->findOrFail(decrypt($customer_identifier));
        $this->data['customer'] = $customer;
        $this->data['title'] = $customer->full_name .' - ID. '. $customer->id_no . ' - ' . $customer->phone;
        $this->data['sub_title'] = "Customer History Thread";
        return view('pages.customer_history.index', $this->data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'remark' => 'required|min:5|max:1000',
            'date_visited' => 'required|date',
            'next_scheduled_visit' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        if (Carbon::parse($request->input('date_visited')) > now()) {
            return Redirect::back()->withErrors($validator)->withInput()->with('warning', 'The specified Visited date is invalid as it is yet to reach');
        }

        if (Carbon::parse($request->input('next_scheduled_visit')) < now()) {
            return Redirect::back()->withErrors($validator)->withInput()->with('warning', 'The specified Next scheduled visit date is invalid as it is in the past');
        }

        $customer = Customer::query()->find($request->input('customer_id'));

        CustomerHistoryThread::query()->create([
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
            'remark' => $request->input('remark'),
            'date_visited' => Carbon::parse($request->input('date_visited')),
            'next_scheduled_visit' => Carbon::parse($request->input('next_scheduled_visit')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Redirect::back()->with('success', "$customer->full_name's Thread has been updated successfully");

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
        //
    }

    /**
     * DataTable
     *
     * @param string $customer_identifier
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function data(string $customer_identifier): \Illuminate\Http\JsonResponse
    {
        $customer = Customer::query()->findOrFail(decrypt($customer_identifier))->setAppends([]);

         $data = DB::table('customer_history_threads')
            ->join('users', 'users.id', '=', 'customer_history_threads.user_id')
            ->select('customer_history_threads.*','users.name')
            ->where('customer_history_threads.customer_id', '=', $customer->id);

         return Datatables::of($data)->toJson();
    }
}
