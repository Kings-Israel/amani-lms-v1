<?php

namespace App\Http\Controllers;

use App\models\Branch;
use App\models\Customer;
use App\models\Expense;
use App\models\Payment;
use App\models\Regpayment;
use App\models\User_payments;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('role:admin|investor|accountant|field_agent|manager|customer_informant|sector_manager');
    }

    public function index()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['title'] = "All Transactions";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['title'] = "All Transactions in ".$branch->bname;
        }

        $this->data['sub_title'] = "List of All Transactions";

        return view('pages.payments.index', $this->data);
    }

    /**************************A certain loan payments********************/

    public function payments($id)
    {
        $lo = Payment::where('loan_id', decrypt($id));

        return Datatables::of($lo)->make(true);
    }

    /***********************payments data*****************/
    public function data()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = Payment::select('*')->orderBy('date_payed', 'desc');
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $lo = Payment::whereHas('Loan', function ($query) use ($branch) {
                $query->whereHas('customer', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                });
            })->orderBy('date_payed', 'desc');
        }

        return Datatables::of($lo)
            ->addColumn('owner', function ($lo){
                return $lo->Loan()->first()->owner;
            })
            ->addColumn('type', function ($lo) {
                $name = $lo->Payment_type()->first()->name == 'Processing Fee' ? 'Application Fee' : $lo->Payment_type()->first()->name;
                return $name;
            })
            ->addColumn('loan_account', function ($lo){
                return $lo->Loan()->first()->loan_account;
            })
            ->make(true);
    }

    // public function data()
    // {
    //     if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
    //         $lo = Payment::select('*')->orderByDesc('id');
    //     } else {
    //         $branch = Branch::find(Auth::user()->branch_id);
    //         $lo = Payment::whereHas('Loan', function ($query) use ($branch) {
    //             $query->whereHas('customer', function ($query) use ($branch) {
    //                 $query->where('branch_id', $branch->id);
    //             });
    //         })->orderByDesc('id');
    //     }

    //     return Datatables::of($lo)
    //         ->addColumn('owner', function ($lo){
    //             return $lo->Loan()->first()->owner;
    //         })
    //         ->addColumn('type', function ($lo) {
    //             $name = $lo->Payment_type()->first()->name == 'Processing Fee' ? 'Application Fee' : $lo->Payment_type()->first()->name;
    //             return $name;
    //         })
    //         ->addColumn('loan_account', function ($lo){
    //             return $lo->Loan()->first()->loan_account;;
    //         })
    //         ->make(true);
    // }

    //settlement_transactions
    public function settlement_transactions()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['title'] = "All Settlement Transactions";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['title'] = "All Settlement Transactions in ".$branch->bname;
        }
        $this->data['sub_title'] = "List of All Settlement Transactions";

        return view('pages.payments.settlement_transactions', $this->data);
    }

    public function settlement_transactions_data(){

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = User_payments::select('*');
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $lo = $branch->settlements();
        }

        return Datatables::of($lo)
            ->addColumn('type', function ($lo){
                return $lo->expense()->first()->expense_name;
            })
            ->addColumn('user_name', function ($lo){
                return $lo->User()->first()->name;
            })
            ->addColumn('branch', function ($lo){
                $branch = Branch::where('id', $lo->User()->first()->branch_id)->first();

                return $branch->bname;
            })
            ->editcolumn('action', function ($lo){

                if ($lo->channel == 'Manual'){
                    return '<div class="btn-group text-center">
                                                <a type="button" class="sel-btn btn btn-xs btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">

                                                             <li><a href="' . route('settlement_transactions.edit', ['id' => encrypt($lo->id)]) . '"><i class="feather icon-edit text-info"></i> Edit</a></li>
                                                             <li><a href="' . route('settlement_transactions.delete', ['id' => encrypt($lo->id)]) . '"><i class="feather icon-delete text-danger"></i> Delete</a></li>

                                                        </ul>
                                        </div>';
                }
            })


            ->make(true);
    }


    /***********************edit manual ticket******************/

    public function settlement_transactions_edit($id){

        $this->data['sub_title'] = "Edit transaction";
        //$this->data['tran'] = User_payments::find(decrypt($id));

        $request = new Request();

        // if ($request->route()->getName() == 'settlement_transactions.edit') {
        //     $this->data['tran'] = User_payments::find(decrypt($id));
        //     $this->data['is_other'] = false;

        // } else {
            $this->data['tran'] = Expense::find(decrypt($id));
            $this->data['is_other'] = true;
        // }
       // dd($this->data['is_other']);

        return view('pages.payments.edit', $this->data);
    }

    public function settlement_transactions_update(Request $request){

       // dd($request->other);

        if ($request->other){
            $pay = Expense::where('id', decrypt($request->id))->update(['amount' => $request->amount]);

        }
        else{
            $pay = User_payments::where('id', decrypt($request->id))->update(['amount' => $request->amount]);

        }


        return back()->with('success', 'Successfully edited Transaction');
    }

    //delete transaction created manually
    public function settlement_transactions_delete($id){

        $find = User_payments::where('id', decrypt($id))->delete();

        return back()->with('success', 'successfully deleted transaction');
    }


    //settlement_transactions
    public function registration_transactions(){
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['title'] = "All Registration Transactions";
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['title'] = "All Registration Transactions in ".$branch->bname;
        }

        $this->data['sub_title'] = "List of All Registration Transactions";

        return view('pages.payments.registration_transactions', $this->data);
    }

    // public function registration_transactions_data(){

    //     if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
    //         $lo = DB::table("regpayments")->join("customers", 'regpayments.customer_id', '=', 'customers.id')
    //             ->select('regpayments.*', 'customers.fname', 'customers.lname', 'customers.phone', DB::raw('concat(fname, " ", lname) as  owner'))
    //             ->orderByDesc('regpayments.id');
    //     } else {
    //         $lo = DB::table("regpayments")->join("customers", 'regpayments.customer_id', '=', 'customers.id')
    //             ->select('regpayments.*', 'customers.fname', 'customers.lname', 'customers.phone', DB::raw('concat(fname, " ", lname) as  owner'))
    //             ->where([
    //                 ['customers.branch_id', '=', Auth::user()->branch_id],
    //             ])->orderByDesc('regpayments.id');
    //     }

    //     return Datatables::of($lo)
    //         ->addColumn('branch', function ($lo) {
    //             $Customer = Customer::find($lo->customer_id);
    //             return Branch::find($Customer->branch_id)->bname;
    //         })
    //         ->make(true);
    // }

    public function registration_transactions_data()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = DB::table("regpayments")
                ->join("customers", 'regpayments.customer_id', '=', 'customers.id')
                ->select('regpayments.*', 'customers.fname', 'customers.lname', 'customers.phone', DB::raw('concat(fname, " ", lname) as owner'))
                ->orderBy('regpayments.date_payed', 'desc');
        } else {
            $lo = DB::table("regpayments")
                ->join("customers", 'regpayments.customer_id', '=', 'customers.id')
                ->select('regpayments.*', 'customers.fname', 'customers.lname', 'customers.phone', DB::raw('concat(fname, " ", lname) as owner'))
                ->where([
                    ['customers.branch_id', '=', Auth::user()->branch_id],
                ])->orderBy('regpayments.date_payed', 'desc');
        }

        return Datatables::of($lo)
            ->addColumn('branch', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                return Branch::find($Customer->branch_id)->bname;
            })
            ->make(true);
    }



    //others_transactions
    public function others_transactions(){
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['title'] = "Petty Cash Transactions";
            // $this->data['title'] = "All Other Transactions";
            $this->data['branches'] = Branch::all();
            $this->data['check_role'] = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant');
        }
        else{
            $branch = Branch::find(Auth::user()->branch_id);
            $this->data['title'] = "All Other in ".$branch->bname;
            $this->data['branches'] = Branch::where('id', '=', Auth::user()->branch_id)->get();
            $this->data['check_role'] =true;
        }
        $this->data['sub_title'] = "List of All Miscelleneous Transactions";


        return view('pages.payments.others_transactions', $this->data);
    }

    public function others_transactions_data(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;
        if ($request->input('branch') != 'all'){
            if ($start < $end) {
                $lo = Expense::where('branch_id', $request->input('branch'))
                    ->whereBetween('date_payed', [$start, $end])
                    ->get();
            } elseif ($start == $end) {
                $lo = Expense::where('branch_id', $request->input('branch'))
                    ->whereDate('date_payed', $start)
                    ->get();
            } else {
                $lo = Expense::where('branch_id', $request->input('branch'))
                    ->get();
            }
        } elseif ($request->input('branch') == 'all' and $request->input('start_date') and $request->input('end_date')){
            if ($start < $end){
                $lo = Expense::whereBetween('date_payed', [$start, $end])
                    ->get();
            } elseif ($start == $end) {
                $lo = Expense::whereDate('date_payed', $start)->get();
            } else{
                $lo = Expense::all();
            }
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = Expense::all();
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $lo = Expense::where('branch_id', $branch->id)->get();
        }

        return Datatables::of($lo)
            ->editColumn('paid_by', function ($lo){
                info($lo);
                return User::where('id', $lo->paid_by)->first()->name;
            })
            ->addColumn('action', function ($lo){
                return '<div class="btn-group text-center">
                            <a type="button" class="sel-btn btn btn-xs btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                <li><a href="' . route('others_transactions.edit', ['id' => encrypt($lo->id)]) . '"><i class="feather icon-edit text-info"></i> Edit</a></li>
                                <li><a href="' . route('others_transactions.delete', ['id' => encrypt($lo->id)]) . '"><i class="feather icon-delete text-danger"></i> Delete</a></li>
                            </ul>
                        </div>';
            })
            ->make(true);
    }

    /*********************************delete other transactions*************************/
    public function other_transaction_delete($id){

        $event = Expense::find(decrypt($id))->delete();

        return back()->with('success', 'successfully deleted the expense');
    }

    public function removeDuplicates()
    {
        $payments = Payment::all()->groupBy('transaction_id');

        $duplicates = array();

        foreach ($payments as $key => $payment) {
            $reconciliation = DB::table('reconsiliation_transactions')->where('transaction_id', $key)->first();

            if ($payment->count() > 1 && !$reconciliation) {
                array_push($duplicates, $payment);
            }
        }

        return collect($duplicates);
    }
}
