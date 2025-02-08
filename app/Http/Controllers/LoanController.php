<?php

namespace App\Http\Controllers;

use App\CustomerInteractionCategory;
use App\models\Branch;
use App\models\Collateral;
use App\models\Customer;
use App\models\Group;
use App\models\Guarantor;
use App\models\Installment;
use App\models\Loan;
use App\models\Loan_Default;
use App\models\LoanType;
use App\models\Payment;
use App\models\Product;
use App\models\Regpayment;
use App\models\RestructuredInstallment;
use App\models\Rollover;
use App\models\Setting;
use App\models\Referee;
use App\models\Customer_location;
use App\models\Raw_payment;
use App\Services\Custom;
use App\User;
use App\models\Arrear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Monolog\Handler\IFTTTHandler;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DisbursementImport;
use App\Imports\ReconcileTransactions;
use Yajra\DataTables\Facades\DataTables;
use App\Jobs\Sms;
use App\models\CustomerInteraction;
use App\models\Pre_interaction;
use App\models\Business_type;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|customer_informant|manager|accountant|field_agent|sector_manager', ['only' => ['edit', 'update', 'delete','create', 'store','destroy', 'group_create', 'store_group_loan']]);
    }

    public function index()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $title = "All Loans";
            $sub_title = "List of All registered LITSA CREDIT Loans";
            $branches = Branch::query()->where('status', '=', true)->get();
            $lfs = User::role('field_agent')->where('status', true)->get();
            $check_role = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant') or Auth::user()->hasRole('sector_manager') or Auth::user()->hasRole('customer_informant');
        } elseif (Auth::user()->hasRole(['field_agent', 'collection_officer'])) {
            $title = Auth::user()->name. " -  Registered loans";
            $sub_title = "List of all registered Loans under your supervision";
            $branch = Branch::find(Auth::user()->branch_id);
            $branches = Branch::where('id', '=', $branch->id)->get();

            if (\auth()->user()->hasRole('field_agent')){
                $lfs = User::where(['id'=> Auth::user()->id])->where('status', true)->get();
            } else{
                $lfs = User::where(['id'=> Auth::user()->field_agent_id])->where('status', true)->get();
            }
            $check_role = false;
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $title = "All registered Loans in " . $branch->bname;
            $sub_title = "List of all registered Loans under ". $branch->bname ." branch";
            $branches = Branch::where('id', '=', $branch->id)->get();
            $lfs = User::role('field_agent')->where('branch_id', '=', $branch->id)->where('status', true)->get();
            $check_role = true;
        }

        return view('pages.loans.index', [
            'title' => $title,
            'sub_title' => $sub_title,
            'branches' => $branches,
            'lfs' => $lfs,
            'check_role' => $check_role
        ]);
    }

    public function active()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $title = "All Active Loans";
            $sub_title = "List of All Active LITSA CREDIT Loans";
            $branches = Branch::query()->where('status', '=', true)->get();
            $lfs = User::role('field_agent')->where('status', true)->get();
            $check_role = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant') or Auth::user()->hasRole('sector_manager') or Auth::user()->hasRole('customer_informant');
        } elseif (Auth::user()->hasRole(['field_agent', 'collection_officer'])) {
            $title = Auth::user()->name. " -  Registered loans";
            $sub_title = "List of all Active Loans under your supervision";
            $branch = Branch::find(Auth::user()->branch_id);
            $branches = Branch::where('id', '=', $branch->id)->get();

            if (\auth()->user()->hasRole('field_agent')){
                $lfs = User::where(['id'=> Auth::user()->id])->where('status', true)->get();
            } else{
                $lfs = User::where(['id'=> Auth::user()->field_agent_id])->where('status', true)->get();
            }
            $check_role = false;
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $title = "All Active Loans in " . $branch->bname;
            $sub_title = "List of all Active Loans under ". $branch->bname ." branch";
            $branches = Branch::where('id', '=', $branch->id)->get();
            $lfs = User::role('field_agent')->where('branch_id', '=', $branch->id)->where('status', true)->get();
            $check_role = true;
        }

        return view('pages.loans.active', [
            'title' => $title,
            'sub_title' => $sub_title,
            'branches' => $branches,
            'lfs' => $lfs,
            'check_role' => $check_role
        ]);
    }

    public function create()
    {
        if (!Auth::user()->hasRole(['field_agent', 'admin', 'customer_informant', 'sector_manager'])) {
            return back()->with('error', 'Unauthorized');
        }
        $title = "Create New Loan";
        $is_edit = false;
        $customers = Customer::all();
        $products = Product::all();
        $loan_types = LoanType::all();
        $prequalified_amounts = DB::table('prequalified_loans')->get();
        $loan = NULL;
        return view('pages.loans.registration', [
            'title' => $title,
            'is_edit' => $is_edit,
            'customers' => $customers,
            'products' => $products,
            'loan_types' => $loan_types,
            'loan' => $loan,
            'prequalified_amounts' => $prequalified_amounts,
        ]);
    }

    public function store(Request $request)
    {
        // Role check
        if (!Auth::user()->hasRole(['field_agent', 'admin', 'customer_informant', 'sector_manager'])) {
            return back()->with('error', 'Unauthorized');
        }

        // Retrieve prequalified amounts
        $prequalified_amounts = DB::table('prequalified_loans')->get()->pluck('amount');

        // Validation rules
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|min:3',
            'id_no' => 'required|exists:customers,id_no',
            'phone' => 'required|exists:customers,phone',
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'loan_type' => 'required|exists:loan_types,id',
            'purpose' => 'required',
            'installments' => 'required',
            'loan_amount' => ['required', 'int', Rule::in($prequalified_amounts)],
            'loan_form' => 'max:10000',
            'audio_file' => 'nullable|mimes:mp3,wav|max:20480',
            'video_file' => 'nullable|mimes:mp4,avi|max:102400', // 50 MB
            'customer_id_front' => 'required|mimes:jpeg,png,jpg|max:20480',
            'customer_id_back' => 'required|mimes:jpeg,png,jpg|max:20480',
            'guarantor_id' => 'required|mimes:jpeg,png,jpg|max:20480',
        ]);

        // If validation fails
        if ($validator->fails()) {

            $errors = $validator->errors();

            if ($errors->has('customer_id_front')) {
                return back()->withErrors($validator)->with('error', 'Customer ID front image must be less than 5 MB')->withInput();
            }
            if ($errors->has('customer_id_back')) {
                return back()->withErrors($validator)->with('error', 'Customer ID back image must be less than 5 MB')->withInput();
            }
            if ($errors->has('guarantor_id')) {
                return back()->withErrors($validator)->with('error', 'Guarantor ID image must be less than 5 MB')->withInput();
            }
            if ($errors->has('video_file')) {
                return back()->withErrors($validator)->with('error', 'Video file must be less than 100 MB')->withInput();
            }

            return back()->withErrors($validator)->withInput();
        }

        $product = Product::find($request->product_id);
        $enddate = Carbon::now()->addDays($product->duration);
        $loan_type = LoanType::find($request->loan_type);

        // Find if customer has unsettled loan
        $loan = Loan::where(['customer_id' => $request->customer_id, 'settled' => false])->first();
        $customer = Customer::find($request->customer_id);
        $branch = Branch::find($customer->branch_id);

        // Check if loan amount exceeds 81k
        if ($request->loan_amount > 81000) {
            return back()->with('error', 'Loan amount cannot be greater than 81,000')->withInput();
        }

        if ($customer->status == 1) {
            if (!isset($loan)) {
                // Calculate loan amount with interest deduction
                $loan_amount = $request->product_id == 6
                    ? floor($request->loan_amount / ((int) $product->interest + 100)) * 100
                    : $request->loan_amount - ($request->loan_amount * ($product->interest / 100));

                $create_loan = Loan::create([
                    'loan_amount' => $loan_amount,
                    'total_amount' => $request->loan_amount,
                    'product_id' => $request->product_id,
                    'customer_id' => $request->customer_id,
                    'loan_type_id' => $loan_type->id,
                    'date_created' => Carbon::now('Africa/Nairobi'),
                    'purpose' => $request->purpose,
                    'loan_account' => $branch->bname . "-" . date('m/d') . "-" . mt_rand(10, 10000),
                    'end_date' => $enddate,
                    'created_by' => Auth::id(),
                    'create_loan_ip' => $request->ip()
                ]);

                // Store loan form document
                if ($file = $request->file('loan_form')) {
                    $file_name = $customer->fname . '_' . $customer->lname . '-' . $create_loan->id;
                    $extension = $file->extension();
                    $file_name = $file_name . "." . $extension;
                    Storage::disk('public')->putFileAs('loan_application_forms', $file, $file_name);
                    $path = Storage::url('loan_application_forms/' . $file_name);
                    $create_loan->update(['document_path' => $path]);
                }

                // Store customer ID front and back images
                if ($customer_id_front = $request->file('customer_id_front')) {
                    $front_name = $customer->fname . '_' . $customer->lname . '-front-' . $create_loan->id;
                    $front_extension = $customer_id_front->extension();
                    $front_name = $front_name . "." . $front_extension;
                    Storage::disk('public')->putFileAs('customer_ids', $customer_id_front, $front_name);
                    $front_path = Storage::url('customer_ids/' . $front_name);
                    $create_loan->update(['customer_id_front' => $front_path]);
                }

                if ($customer_id_back = $request->file('customer_id_back')) {
                    $back_name = $customer->fname . '_' . $customer->lname . '-back-' . $create_loan->id;
                    $back_extension = $customer_id_back->extension();
                    $back_name = $back_name . "." . $back_extension;
                    Storage::disk('public')->putFileAs('customer_ids', $customer_id_back, $back_name);
                    $back_path = Storage::url('customer_ids/' . $back_name);
                    $create_loan->update(['customer_id_back' => $back_path]);
                }

                // Store guarantor ID image
                if ($guarantor_id = $request->file('guarantor_id')) {
                    $guarantor_name = $customer->fname . '_' . $customer->lname . '-guarantor-' . $create_loan->id;
                    $guarantor_extension = $guarantor_id->extension();
                    $guarantor_name = $guarantor_name . "." . $guarantor_extension;
                    Storage::disk('public')->putFileAs('guarantor_ids', $guarantor_id, $guarantor_name);
                    $guarantor_path = Storage::url('guarantor_ids/' . $guarantor_name);
                    $create_loan->update(['guarantor_id' => $guarantor_path]);
                }

                // Store audio file if uploaded
                if ($audio = $request->file('audio_file')) {
                    $audio_name = $customer->fname . '_' . $customer->lname . '-audio-' . $create_loan->id;
                    $audio_extension = $audio->extension();
                    $audio_name = $audio_name . "." . $audio_extension;
                    Storage::disk('public')->putFileAs('loan_application_audio', $audio, $audio_name);
                    $audio_path = Storage::url('loan_application_audio/' . $audio_name);
                    $create_loan->update(['audio_path' => $audio_path]);
                }

                // Store video file if uploaded
                if ($video = $request->file('video_file')) {
                    $video_name = $customer->fname . '_' . $customer->lname . '-video-' . $create_loan->id;
                    $video_extension = $video->extension();
                    $video_name = $video_name . "." . $video_extension;
                    Storage::disk('public')->putFileAs('loan_application_video', $video, $video_name);
                    $video_path = Storage::url('loan_application_video/' . $video_name);
                    $create_loan->update(['video_path' => $video_path]);
                }

                return back()->with('success', 'Successfully created Loan');
            } else {
                return back()->with('error', 'Could not create a new Loan. This customer has unsettled Loan')->withInput();
            }
        } else {
            return back()->with('error', 'Could not create a new Loan. This customer has been blocked, contact admin for more information')->withInput();
        }
    }


    // public function store(Request $request)
    // {
    //     if (!Auth::user()->hasRole(['field_agent', 'admin', 'customer_informant', 'sector_manager'])) {
    //         return back()->with('error', 'Unauthorized');
    //     }

    //     $prequalified_amounts = DB::table('prequalified_loans')->get()->pluck('amount');

    //     $validator = Validator::make($request->all(), [
    //         'fullname' => 'required|min:3',
    //         'id_no' => 'required|exists:customers,id_no',
    //         'phone' => 'required|exists:customers,phone',
    //         'product_id' => 'required|exists:products,id',
    //         'customer_id' => 'required|exists:customers,id',
    //         'loan_type' => 'required|exists:loan_types,id',
    //         'purpose' => 'required',
    //         'installments' => 'required',
    //         'loan_amount' => ['required', 'int', Rule::in($prequalified_amounts)],
    //         'loan_form' => 'max:10000',
    //         'audio_file' => 'nullable|mimes:mp3,wav|max:20480',
    //         'video_file' => 'nullable|mimes:mp4,avi|max:51200',
    //     ]);

    //     if ($validator->fails()) {
    //         return back()->withErrors($validator)->withInput();
    //     }
    //     $product = Product::find($request->product_id);
    //     $enddate = Carbon::now()->addDays($product->duration);

    //     $loan_type = LoanType::find($request->loan_type);
    //     /*************************find if the customer has a loan she has not cleared***************/
    //     $loan = Loan::where(['customer_id' => $request->customer_id, 'settled' => false])->first();
    //     $customer = Customer::find($request->customer_id);
    //     $branch = Branch::find($customer->branch_id);

    //     //check if loan amouunt is more than 81k
    //     if ($request->loan_amount > 81000) {
    //         return back()->with('error', 'Loan amount cannot be greater than 81,000')->withInput();
    //     }

    //     if ($customer->status == 1) {
    //         if (!isset($loan)) {
    //             if ($request->product_id == 6) {
    //                 // Deduct Interest
    //                 $loan_amount = floor($request->loan_amount / ((int) $product->interest + 100)) * 100;
    //             } else {
    //                 $loan_amount = $request->loan_amount - ($request->loan_amount * ($product->interest / 100));
    //             }

    //             $create_loan = Loan::create([
    //                 'loan_amount' => $loan_amount,
    //                 'total_amount' => $request->loan_amount,
    //                 'product_id' => $request->product_id,
    //                 'customer_id' => $request->customer_id,
    //                 'loan_type_id' => $loan_type->id,
    //                 'date_created' => Carbon::now('Africa/Nairobi'),
    //                 'purpose' => $request->purpose,
    //                 'loan_account' => $branch->bname . "-" . date('m/d') . "-" . mt_rand(10, 10000),
    //                 'end_date' => $enddate,
    //                 'created_by' => Auth::id(),
    //                 'create_loan_ip' => $request->ip()
    //             ]);

    //             if($file = $request->file('loan_form'))
    //             {
    //                 $file_name = $customer->fname.'_'.$customer->lname.'-'.$create_loan->id;
    //                 $extension = $file->extension();
    //                 $file_name = $file_name .".". $extension;
    //                 Storage::disk('public')->putFileAs('loan_application_forms', $file, $file_name);
    //                 $path = Storage::url('loan_application_forms/'.$file_name);
    //                 $create_loan->update([
    //                     'document_path'=>$path
    //                 ]);
    //             }

    //                // Store audio file if uploaded
    //             if ($audio = $request->file('audio_file')) {
    //                 $audio_name = $customer->fname.'_'.$customer->lname.'-audio-'.$create_loan->id;
    //                 $audio_extension = $audio->extension();
    //                 $audio_name = $audio_name . "." . $audio_extension;
    //                 Storage::disk('public')->putFileAs('loan_application_audio', $audio, $audio_name);
    //                 $audio_path = Storage::url('loan_application_audio/' . $audio_name);
    //                 $create_loan->update(['audio_path' => $audio_path]);
    //             }

    //             // Store video file if uploaded
    //             if ($video = $request->file('video_file')) {
    //                 $video_name = $customer->fname.'_'.$customer->lname.'-video-'.$create_loan->id;
    //                 $video_extension = $video->extension();
    //                 $video_name = $video_name . "." . $video_extension;
    //                 Storage::disk('public')->putFileAs('loan_application_video', $video, $video_name);
    //                 $video_path = Storage::url('loan_application_video/' . $video_name);
    //                 $create_loan->update(['video_path' => $video_path]);
    //             }


    //             return back()->with('success', 'Successfully created Loan');
    //         } else {
    //             return back()->with('error', 'Could not create a new Loan. This customer has unsettled Loan')->withInput();
    //         }
    //     } else {
    //         return back()->with('error', 'Could not create a new Loan. This customer has been blocked, contact admin for more information')->withInput();
    //     }
    // }

    // public function show($id)
    // {
    //     $loan = Loan::find(decrypt($id));
    //     $customer = Customer::where('id', $loan->customer_id)->first();

    //     if ($loan->disbursed) {
    //         $formatted_dt1 = Carbon::parse($loan->end_date);
    //         $formatted_dt2 = Carbon::parse($loan->disbursement_date);
    //         $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
    //         $days_remaining = $date_diff;
    //     } else {
    //         $days_remaining = "N/A";
    //     }
    //     $title = "Loan Details";
    //     $sub_title = "Detailed Loan Details";

    //     $loan = $loan;
    //     $payments = Payment::where('loan_id', $id)->first();
    //     $customer = $customer;
    //     $guarantor = Guarantor::find($customer->guarantor_id);
    //     $collaterals = Collateral::where(['loan_id' => $loan->id])->get();
    //     $collaterals = $collaterals;

    //     return view('pages.loans.view', [
    //         'days_remaining' => $days_remaining,
    //         'title' => $title,
    //         'sub_title' => $sub_title,
    //         'loan' => $loan,
    //         'payments' => $payments,
    //         'customer' => $customer,
    //         'guarantor' => $guarantor,
    //         'collaterals' => $collaterals,
    //         'customer_id' => encrypt($customer->id),
    //     ]);
    // }

    public function show($id)
    {
        $loan = Loan::findOrFail(decrypt($id));
        $customer = Customer::where('id', $loan->customer_id)->firstOrFail();

        if ($loan->disbursed) {
            $formatted_dt1 = Carbon::parse($loan->end_date);
            $formatted_dt2 = Carbon::parse($loan->disbursement_date);
            $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
            $days_remaining = $date_diff;
        } else {
            $days_remaining = "N/A";
        }

        $title = "Loan Details";
        $sub_title = "Detailed Loan Details";

        $totalAmount = 0;
        $paidAmount = 0;
        $balance = 0;
        $principalAmount = 0;
        $interestAmount = 0;

        if ($loan->disbursed) {
            $totalAmount = $loan->getTotalAttribute();
            $paidAmount = $loan->getAmountPaidAttribute();
            $balance = $loan->getBalanceAttribute();
            $principalAmount = $loan->loan_amount;

            $interestAmount = $loan->rolled_over
                ? $loan->loan_amount * ($loan->product()->first()->interest / 100) + $loan->rollover()->first()->rollover_interest
                : $loan->loan_amount * ($loan->product()->first()->interest / 100);
        }

        $registrationFees = $customer->regpayments()->sum('amount');

        $payments = Payment::where('loan_id', $loan->id)->get();
        $guarantor = Guarantor::find($customer->guarantor_id);
        $collaterals = Collateral::where(['loan_id' => $loan->id])->get();

        return view('pages.loans.view', [
            'days_remaining' => $days_remaining,
            'title' => $title,
            'sub_title' => $sub_title,
            'loan' => $loan,
            'payments' => $payments,
            'customer' => $customer,
            'guarantor' => $guarantor,
            'collaterals' => $collaterals,
            'customer_id' => encrypt($customer->id),
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'balance' => $balance,
            'principalAmount' => $principalAmount,
            'interestAmount' => $interestAmount,
            'registrationFees' => $registrationFees,
        ]);
    }

    public function edit($id)
    {
        if (Auth::user()->hasRole(['field_agent', 'manager', 'customer_informant'])) {
            return back()->with('error', 'Unauthorized');
        }

        $loan = Loan::find(decrypt($id));
        $title = "Edit Loan";
        $is_edit = true;
        $customer = Customer::find($loan->customer_id);
        $products = Product::all();
        $loan = $loan;
        $prequalified_amounts = DB::table('prequalified_loans')->get();

        return view('pages.loans.registration', [
            'title' => $title,
            'is_edit' => $is_edit,
            'customer' => $customer,
            'products' => $products,
            'loan' => $loan,
            'prequalified_amounts' => $prequalified_amounts
        ]);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->hasRole(['field_agent', 'manager', 'customer_informant'])) {
            return back()->with('error', 'Unauthorized');
        }
        $prequalified_amounts = DB::table('prequalified_loans')->get()->pluck('amount');

        $validator = Validator::make($request->all(), [
            'fullname' => 'required|min:3',
            'id_no' => 'required',
            'phone' => 'required',
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'purpose' => 'required',
            'installments' => 'required',
            'loan_amount' => ['required', 'int', Rule::in($prequalified_amounts)],
            'loan_form' => 'mimes:pdf|max:10000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Validation Errors, refresh page and try again.');
        }

        if ($request->loan_amount > 81000){
            return back()->with('error', 'Loan amount cannot be greater than 81,000')->withInput();
        }

        if ($request->product_id == 5) {
            // Make sure the loan amount is 7000
            if ($request->loan_amount != 7000) {
                return back()->withErrors(['loan_amount' => 'Invalid amount for product selected'])->withInput()->with('error'. 'Enter correct amount for the product selected.');
            }
        } else {
            $accepted_amounts = [13500, 20250, 27000, 33760, 40500, 54000, 67500, 81000];
            if (!collect($accepted_amounts)->contains($request->loan_amount)) {
                return back()->withErrors(['loan_amount' => 'Invalid amount for product selected'])->withInput()->with('error'. 'Enter correct amount for the product selected.');
            }
        }

        $product = Product::find($request->product_id);

        $enddate = Carbon::now()->addDays($product->duration);

        /*************************find if the customer has a loan she has not cleared***************/
        //$loan = Loan::where(['customer_id' => $request->customer_id, 'settled' => false])->first();
        $loan = Loan::find(decrypt($id));
        $customer = Customer::find($request->customer_id);
        if ($loan) {
            try {
                DB::beginTransaction();
                if ($request->product_id == 6) {
                    // Deduct Interest
                    $loan_amount = floor($request->loan_amount / ((int) $product->interest + 100)) * 100;
                } else {
                    $loan_amount = $request->loan_amount - ($request->loan_amount * ($product->interest / 100));
                }

                $loan->update([
                    'loan_amount' => $loan_amount,
                    'total_amount' => $request->loan_amount,
                    'purpose' => $request->purpose,
                    'product_id' => $request->product_id,
                    'last_edited_by' => Auth::id(),
                ]);

                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant'))
                {
                    $customer->update([
                        "phone" => $request->phone,
                        "id_no" => $request->id_no,
                    ]);
                }

                if($file = $request->file('loan_form'))
                {
                    $file_name = $customer->fname.'_'.$customer->lname.'-'.$loan->id;
                    $extension = $file->extension();
                    $file_name = $file_name .".". $extension;
                    $exists = Storage::disk('public')->exists('loan_application_forms/'.$file_name);
                    if ($exists){
                        Storage::disk('public')->delete('loan_application_forms/'.$file_name);
                    }
                    Storage::disk('public')->putFileAs('loan_application_forms', $file, $file_name);
                    $path = Storage::url('loan_application_forms/'.$file_name);
                    $loan->update([
                        'document_path'=>$path
                    ]);

                    return Redirect::route('loans.show', ['id'=>encrypt($loan->id)])->with('success', 'Successfully updated Loan details and uploaded loan document');
                }

                if ($loan->disbursed) {
                    // return Redirect::back()->with('error', "Sorry, you are not permitted to update a disbursed loan. Contact Admin for support.");
                    $first_installment = Installment::where('loan_id', $loan->id)->first()->due_date;
                    $start_date = Carbon::parse($first_installment);
                    info($start_date);

                    // Move all payments to reg payment
                    $payments = Payment::where('loan_id', $loan->id)->whereIn('payment_type_id', [1, 3])->get();
                    $reg_payment = Regpayment::where('customer_id', $loan->customer->id)->first();
                    // $amount_paid = $loan->total_amount_paid;
                    $amount_paid = 0;
                    foreach ($payments as $payment) {
                        $amount_paid += $payment->amount;
                    }

                    $reg_payment->update([
                        'amount' => $reg_payment->amount + $amount_paid
                    ]);

                    // Delete all installments
                    Installment::where('loan_id', $loan->id)->delete();
                    Payment::where('loan_id', $loan->id)->delete();

                    // Restructure Loan
                    $loan->update([
                        'loan_amount' => $loan->loan_amount,
                        'product_id' => $request->product_id,
                        'total_amount' => $request->loan_amount,
                        'total_amount_paid' => 0
                    ]);

                    // Create Installments
                    $loan = Loan::find($loan->id);

                    $settings = Setting::first();

                    //create installments
                    $product = Product::find($loan->product_id);

                    $lp_fee = 0;

                    if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                    {
                        //break down loans to daily installments based on product duration
                        $principle_amount = round($loan->total_amount / $product->duration);
                        $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                        if ($settings->lp_fee){
                            $lp_fee = $settings->lp_fee / $product->duration;
                        }
                    }
                    else //WEEKLY REPAYMENTS
                    {
                        $principle_amount = round($loan->total_amount / $product->installments);
                        $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                        if ($settings->lp_fee){
                            $lp_fee = $settings->lp_fee / $product->installments;
                        }
                    }
                    $amountPayable = $principle_amount;
                    $days = 0;

                    if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                    {
                        for ($i = 0; $i < $product->duration; $i++) {
                            $days = $days + 1;
                            if ($i == 0) {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => $start_date ? (Carbon::parse($start_date)->addDays($days)->equalTo(now()->format('Y-m-d')) ? true : false) : (Carbon::now()->addDays(1)->addDays($days)->equalTo(now()) ? true : false),
                                    "being_paid" => true,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            } else {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => $start_date ? (Carbon::parse($start_date)->addDays($days)->equalTo(now()->format('Y-m-d')) ? true : false) : (Carbon::now()->addDays(1)->addDays($days)->equalTo(now()) ? true : false),
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            }
                        }
                    } else {
                        for ($i = 0; $i < $product->installments; $i++) {
                            $days = $days + 7;
                            if ($i == 0) {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => true,
                                    "being_paid" => true,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            } else {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => false,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            }
                        }
                    }

                    Payment::create([
                        'loan_id' => $loan->id,
                        'amount' => $loan->loan_amount,
                        'transaction_id' => Str::random(10),
                        'date_payed' => $start_date ? Carbon::parse($start_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                        'channel' => "MPESA",
                        'payment_type_id' => 2,
                    ]);

                    //check if registration payment is more than required
                    $reg = Regpayment::where('customer_id', $loan->customer_id)->first();

                    if ($reg) {
                        //meaning the registration is greater than required so put the extra in loan processing fee
                        if ($reg->amount > $settings->registration_fee) {
                            //balance after registration
                            $bal = $reg->amount - $settings->registration_fee;
                            $loans = Loan::where(['customer_id' => $loan->customer_id, 'settled' => true])->count();
                            if ($loans <= 0) {
                                if ($bal >= $loan->balance){
                                    $remainda = $bal - $loan->balance;
                                    Payment::create([
                                        'payment_type_id' => 1,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now(),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $loan->balance
                                    ]);
                                    $loan->update(['settled' => true]);
                                } else {
                                    Payment::create([
                                        'payment_type_id' => 1,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now(),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $bal
                                    ]);
                                    $remainda = 0;
                                }
                                //add the amount to current installment being paid
                                $handle_installments = new MpesaPaymentController();
                                $handle_installments->handle_installments($loan, $bal);

                                $reg->update([
                                    'amount' => $settings->registration_fee + $remainda
                                ]);
                            } else {
                                if ($settings->loan_processing_fee > 0) {
                                    //meaning the remaining balance is greater than loan processing fee
                                    if ($bal > $settings->loan_processing_fee) {
                                        Payment::create([
                                            'payment_type_id' => 3,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::parse($reg_payment->updated_at),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $settings->loan_processing_fee
                                        ]);
                                        $rem = $bal - $settings->loan_processing_fee;

                                        //then add the remaining to the loan settlement
                                        if ($rem >= $loan->balance){
                                            $remainda = $rem - $loan->balance;
                                            Payment::create([
                                                'payment_type_id' => 1,
                                                'loan_id' => $loan->id,
                                                'date_payed' => Carbon::parse($reg_payment->updated_at),
                                                'transaction_id' => $reg->transaction_id,
                                                'channel' => 'MPESA',
                                                'amount' => $loan->balance
                                            ]);
                                            $loan->update(['settled' => true]);
                                        } else {
                                            Payment::create([
                                                'payment_type_id' => 1,
                                                'loan_id' => $loan->id,
                                                'date_payed' => Carbon::parse($reg_payment->updated_at),
                                                'transaction_id' => $reg->transaction_id,
                                                'channel' => 'MPESA',
                                                'amount' => $rem
                                            ]);
                                            $remainda = 0;
                                        }
                                        //add the amount to current installment being paid
                                        $handle_installments = new MpesaPaymentController();
                                        $handle_installments->handle_installments($loan, $rem);
                                    } //amount remaining is not greater than loan processing fee
                                    else {
                                        Payment::create([
                                            'payment_type_id' => 3,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::parse($reg_payment->updated_at),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $bal
                                        ]);
                                        $remainda = 0;
                                    }
                                    $reg->update([
                                        'amount' => $settings->registration_fee + $remainda
                                    ]);
                                } else {
                                    if ($bal >= $loan->balance){
                                        $remainda = $bal - $loan->balance;
                                        Payment::create([
                                            'payment_type_id' => 1,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now(),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $loan->balance
                                        ]);
                                        $loan->update(['settled' => true]);
                                    } else {
                                        Payment::create([
                                            'payment_type_id' => 1,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now(),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $bal
                                        ]);
                                        $remainda = 0;
                                    }
                                    //add the amount to current installment being paid
                                    $handle_installments = new MpesaPaymentController();
                                    $handle_installments->handle_installments($loan, $bal);

                                    $reg->update([
                                        'amount' => $settings->registration_fee + $remainda
                                    ]);
                                }
                            }

                            $loan->update(['total_amount_paid' => $bal]);
                        }
                    }
                }
                DB::commit();
                return Redirect::back()->with('success', 'Successfully updated Loan details.');
            } catch (\Throwable $th) {
                info($th);
                DB::rollBack();
                return back()->withInput()->with('error', 'Something went wrong. Contact admin for help.');
            }
        } else {
            return Redirect::back()->with('error', 'Something went wrong, refresh page and try again.')->withInput();
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasRole(['admin', 'manager', 'accountant', 'sector_manager'])) {
            return back()->with('error', 'Unauthorized');
        }

        $loan = Loan::where(['id' => decrypt($id), 'disbursed' => false])->first();

        $customer = Customer::find($loan->customer_id);
        $file_name = $customer->fname.'_'.$customer->lname.'-'.$loan->id;
        $extension = 'pdf';
        $file_name = $file_name .".". $extension;
        $exists = Storage::disk('public')->exists('loan_application_forms/'.$file_name);
        if ($exists){
            Storage::disk('public')->delete('loan_application_forms/'.$file_name);
        }

        $collateral = Collateral::where('loan_id', $loan->id)->get();

        foreach($collateral as $col) {
            unlink(public_path($col->image_url));

            $col->delete();
        }

        $loan->delete();

        return back()->with('success', 'Successfully deleted Loan');

    }

    public function loans_delete_document($id)
    {
        $loan = Loan::find(decrypt($id));
        $loan->update([
            'document_path'=>null
        ]);

        $customer = Customer::find($loan->customer_id);
        $file_name = $customer->fname.'_'.$customer->lname.'-'.$loan->id;
        $extension = 'pdf';
        $file_name = $file_name .".". $extension;
        $exists = Storage::disk('public')->exists('loan_application_forms/'.$file_name);
        if ($exists){
            Storage::disk('public')->delete('loan_application_forms/'.$file_name);
        }

        return back()->with('success', 'Successfully deleted Loan Application Form');
    }

    /**
     * get the customers associated to give a loan
     */
    public function customer_data()
    {
        $user = Auth::user();
        if ($user->hasRole('field_agent')){
            $lo = Customer::where('status', true)->where('field_agent_id', '=', $user->id)
                ->whereDoesntHave('loans', function($query) {
                    $query->where('settled', false);
                })->select('*');
        } else {
            $lo = Customer::where('status', true)
                ->whereDoesntHave('loans', function($query) {
                    $query->where('settled', false);
                })->select('*');
        }

        return Datatables::of($lo)
            ->addColumn('loan_status', function ($lo) {
                // $incomplete_loans = Loan::where(['customer_id'=>$lo->id, 'settled'=>false])->first();
                // if($incomplete_loans){
                //     return '<span class="badge badge-danger">Unsettled Loan</span>';
                // }else{
                //     return '<span class="badge badge-success">Valid Applicant</span>';
                // }
                return '<span class="badge badge-success">Valid Applicant</span>';
            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;
                $fullname = $lo->fname . ' ' . $lo->lname;
                return '<button type="button" data-dismiss="modal" data-id="' . $data . '" data-fullname="' . $fullname . '"   data-amount="' . $lo->prequalified_amount . '" data-idno="' . $lo->id_no . '" data-phone="' . $lo->phone . '" class="sel-btn btn btn-xs btn-primary"><i class="feather icon-edit text-warning"></i> Select</a>';
                /*return '<div class="btn-group text-center">
                            <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i></a>
                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                <li><a href="' . route('branches.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                            </ul>
                        </div>';*/
            })
            ->rawColumns(['action', 'loan_status'])
            ->make(true);
    }

    /*loans data*/
    public function data(Request $request)
    {
        set_time_limit(300);

        $date = $request->date;

        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        if ($request->lf != 'all') {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.id_no', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('customers.field_agent_id', '=', $request->lf)
                ->when($date && $date != '', function ($query) use ($date) {
                    $query->whereDate('loans.created_at', $date);
                })
                ->whereIn('customers.branch_id', $activeBranches);
        }
        elseif ($request->branch != 'all'){
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.id_no', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->when($date && $date != '', function ($query) use ($date) {
                    $query->whereDate('loans.created_at', $date);
                })
                ->where('customers.branch_id', '=', $request->branch);
        }
        elseif (Auth::user()->hasRole('admin')||Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.id_no', 'customers.phone', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->when($date && $date != '', function ($query) use ($date) {
                    $query->whereDate('loans.created_at', $date);
                })
                ->whereIn('customers.branch_id', $activeBranches);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.id_no', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->when($date && $date != '', function ($query) use ($date) {
                    $query->whereDate('loans.created_at', $date);
                })
                ->where('customers.field_agent_id', '=', Auth::user()->id);
        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.id_no', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->when($date && $date != '', function ($query) use ($date) {
                    $query->whereDate('loans.created_at', $date);
                })
                ->where('customers.branch_id', '=', Auth::user()->branch_id);
        }

        return Datatables::of($lo)
            ->editColumn('owner', function ($lo){
                $customer = Customer::find($lo->customer_id);
                $group = Group::find($lo->group_id);
                if ($group){
                    $group = $customer->group()->first();
                    $group_name = $group->name;
                    return $customer->fname. ' '. $customer->lname. '<br>'. '<span class="badge badge-primary" style="font-size: small">'.$group_name.'</span>';
                } else {
                    return $customer->fname. ' '. $customer->lname;
                }
            })
            ->editColumn('owner_phone_number', function ($lo){
                $customer = Customer::find($lo->customer_id);

                return $customer->phone;
            })
            ->editColumn('owner_id_no', function ($lo){
                $customer = Customer::find($lo->customer_id);

                return $customer->id_no;
            })
            ->addColumn('loans_applied', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                return $customer->times_loan_applied;
            })
            ->addColumn('registration_fee_paid', function ($lo) {
                $setting = Setting::first();
                $customer = Customer::find($lo->customer_id);
                $reg_payment = $customer->regpayments()->sum('amount') >= $setting->registration_fee ? true : false;
                if ($reg_payment) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
            ->addColumn('branch', function ($lo) {
                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('location', function ($lo) {
                $location = Customer_location::where('customer_id', $lo->customer_id)->first();

                return Str::title($location->ward).', '.Str::title($location->constituency).', '.Str::title($location->county->cname);
            })
            ->addColumn('referee', function ($lo) {
                $customer_referee = DB::table('customer_referee')->where('customer_id', $lo->customer_id)->first();

                if ($customer_referee) {
                    $referee = Referee::find($customer_referee->referee_id);

                    return Str::headline($referee->full_name).' ('.$referee->phone_number.')';
                }
                return '--';
            })
            ->addColumn('businessType', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                return $Customer->business_type_id ? Business_type::find($Customer->business_type_id)->bname : '';
            })
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return "YES";
                }
                return "NO";
            })
            ->editColumn('created_by', function ($lo) {
                if ($lo->created_by != null) {
                    return User::find($lo->created_by)->name;
                }
                return "--";
            })
            ->editColumn('approved_by', function ($lo) {
                if ($lo->approved_by != null) {
                    $user = User::find($lo->approved_by);
                    if ($user){
                        return $user->name;
                    } else {
                        return "--";
                    }
                }
                return "--";
            })
            ->addColumn('field_agent', function ($lo) {
                if ($lo->customer_id != null) {
                    $user = User::find(Customer::find($lo->customer_id)->field_agent_id);
                    if ($user){
                        return $user->name;
                    } else {
                        return "--";
                    }
                }
                return "--";
            })
            ->editColumn('disbursed_by', function ($lo) {
                if ($lo->disbursed_by != null) {
                    return User::find($lo->disbursed_by)->name;
                }
                return "--";
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
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                return number_format($payments);
            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->has_lp_fee) {
                    $setting = Setting::query()->first();
                    if ($setting->lp_fee) {
                        $lp_fee = $setting->lp_fee;
                    } else {
                        $lp_fee = 0;
                    }
                } else {
                    $lp_fee = 0;
                }
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->total_amount + $rollover->rollover_interest + $lp_fee;
                } else {
                    $total = $lo->total_amount + $lp_fee;
                }
                // return number_format($total, 1);
                return number_format($total);
            })
            ->addColumn('balance', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');
                $product = Product::find($lo->product_id);
                if ($lo->has_lp_fee) {
                    $setting = Setting::query()->first();
                    if ($setting->lp_fee) {
                        $lp_fee = $setting->lp_fee;
                    } else {
                        $lp_fee = 0;
                    }
                } else {
                    $lp_fee = 0;
                }
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->total_amount + $rollover->rollover_interest + $lp_fee;
                } else {
                    $total = $lo->total_amount + $lp_fee;
                }
                $balance = $total - $payments;

                return number_format($balance);
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);
                if (Auth::user()->hasRole('admin')||Auth::user()->hasRole('accountant')||Auth::user()->hasRole('manager')||Auth::user()->hasRole('field_agent')|| Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                    if ($lo->document_path != null){
                        return '<div class="btn-group text-center">
                                    <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                    <ul class="dropdown-menu" style="">
                                        <li><a class="dropdown-item" href="' . route('loans.show', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> View</a></li>
                                        <li><a class="dropdown-item" href="' . asset($lo->document_path) . '"><i class="feather icon-download text-success" ></i> View Loan Documents</a></li>
                                        <li><a class="dropdown-item" href="' . asset($lo->audio_path) . '"><i class="feather icon-download text-success" ></i> Loan Audio</a></li>
                                        <li><a class="dropdown-item" href="' . route('loans.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                                    </ul>
                                </div>';
                    } else {
                        return '<div class="btn-group text-center">
                                    <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                    <ul class="dropdown-menu" style="">
                                        <li><a class="dropdown-item" href="' . route('loans.show', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> View</a></li>
                                        <li><a class="dropdown-item" href="' . route('loans.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                                    </ul>
                                </div>';
                    }
                } else {
                    return '<div class="btn-group text-center">
                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                <ul class="dropdown-menu" style="">
                                    <li><a class="dropdown-item" href="' . route('loans.show', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> View</a></li>
                                    <li><a class="dropdown-item" href="' . route('loans.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                                </ul>
                            </div>';
                }
            })
            ->rawColumns(['action', 'owner', 'owner_phone_number', 'owner_id_no', 'branch', 'balance', 'disbursed', 'amount_paid', 'settled', 'total', 'approved', 'created_by', 'disbursed_by'])
            ->toJson();
    }

    /*active loans data*/
    public function activeData(Request $request)
    {
        set_time_limit(300);

        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        if ($request->lf != 'all') {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('customers.field_agent_id', '=', $request->lf)
                ->where('approved', 1)
                ->where('disbursed', 1)
                ->where('settled', 0)
                ->whereIn('customers.branch_id', $activeBranches);
        }
        elseif ($request->branch != 'all'){
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('customers.branch_id', '=', $request->branch)
                ->where('approved', 1)
                ->where('disbursed', 1)
                ->where('settled', 0);
        }
        elseif (Auth::user()->hasRole('admin')||Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->whereIn('customers.branch_id', $activeBranches)
                ->where('approved', 1)
                ->where('disbursed', 1)
                ->where('settled', 0);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.field_agent_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('customers.field_agent_id', '=', Auth::user()->id)
                ->where('approved', 1)
                ->where('disbursed', 1)
                ->where('settled', 0);
        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('customers.branch_id', '=', Auth::user()->branch_id)
                ->where('approved', 1)
                ->where('disbursed', 1)
                ->where('settled', 0);
        }

        return Datatables::of($lo)
            ->editColumn('owner', function ($lo){
                $customer = Customer::find($lo->customer_id);
                return $customer->fname. ' '. $customer->lname;
            })
            ->editColumn('owner_phone_number', function ($lo){
                $customer = Customer::find($lo->customer_id);

                return $customer->phone;
            })
            ->addColumn('branch', function ($lo) {
                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('field_agent', function ($lo) {
                if ($lo->customer_id != null) {
                    $user = User::find(Customer::find($lo->customer_id)->field_agent_id);
                    if ($user){
                        return $user->name;
                    } else {
                        return "--";
                    }
                }
                return "--";
            })
            ->addColumn('amount_paid', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                return number_format($payments);
            })
            ->addColumn('total', function ($lo) {
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->total_amount + $rollover->rollover_interest;
                } else {
                    $total = $lo->total_amount;
                }
                return number_format($total);
            })
            ->addColumn('balance', function ($lo) {
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->total_amount + $rollover->rollover_interest;
                } else {
                    $total = $lo->total_amount;
                }
                $balance = $total - $payments;

                return number_format($balance);
            })
            ->addColumn('location', function ($lo) {
                $location = Customer_location::where('customer_id', $lo->customer_id)->first();

                return Str::title($location->ward).', '.Str::title($location->constituency).', '.Str::title($location->county->cname);
            })
            // ->addColumn('referee', function ($lo) {
            //     $customer_referee = DB::table('customer_referee')->where('customer_id', $lo->customer_id)->first();

            //     $referee = Referee::find($customer_referee->referee_id);

            //     return Str::headline($referee->full_name).' ('.$referee->phone_number.')';
            // })
            ->addColumn('referee', function ($lo) {
                $customer_referee = DB::table('customer_referee')->where('customer_id', $lo->customer_id)->first();
                if (!$customer_referee) {
                    return '--'; // Return a default value if no customer referee exists
                }

                $referee = Referee::find($customer_referee->referee_id);
                if (!$referee) {
                    return '--'; // Return a default value if the referee is not found
                }

                return Str::headline($referee->full_name) . ' (' . $referee->phone_number . ')';
            })
            ->addColumn('businessType', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                return $Customer->business_type_id ? Business_type::find($Customer->business_type_id)->bname : '';
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);
                if (Auth::user()->hasRole('admin')||Auth::user()->hasRole('accountant')||Auth::user()->hasRole('manager')||Auth::user()->hasRole('field_agent')|| Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
                    if ($lo->document_path != null){
                        return '<div class="btn-group text-center">
                                    <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                    <ul class="dropdown-menu" style="">
                                        <li><a class="dropdown-item" href="' . route('loans.show', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> View</a></li>
                                        <li><a class="dropdown-item" href="' . asset($lo->document_path) . '"><i class="feather icon-download text-success" ></i> View Loan Documents</a></li>
                                        <li><a class="dropdown-item" href="' . route('loans.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                                    </ul>
                                </div>';
                    } else {
                        return '<div class="btn-group text-center">
                                    <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                    <ul class="dropdown-menu" style="">
                                        <li><a class="dropdown-item" href="' . route('loans.show', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> View</a></li>
                                        <li><a class="dropdown-item" href="' . route('loans.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                                    </ul>
                                </div>';
                    }
                } else {
                    return '<div class="btn-group text-center">
                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                <ul class="dropdown-menu" style="">
                                    <li><a class="dropdown-item" href="' . route('loans.show', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> View</a></li>
                                    <li><a class="dropdown-item" href="' . route('loans.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>
                                </ul>
                            </div>';
                }
            })
            ->rawColumns(['action', 'owner', 'owner_phone_number', 'branch', 'balance', 'amount_paid', 'total'])
            ->toJson();
    }

    /******************************* Agent loans Waiting Approval ***********************/
    public function approvals_initial()
    {
        $title = "Agents Wait Approval Loans";
        $sub_title = "List of All Unapproved Loans from Agents";
        $loans = Loan::where('approved', false)->get();
        return view('pages.loans.approve', [
            'title' => $title,
            'sub_title' => $sub_title,
            'loans' => $loans,
        ]);
    }

    public function waitingapproval(Request $request)
    {
        $title = "Agents Loans Wait Acceptance";
        $sub_title = "List of All Unaccepted Loans Agents";

        $loans = Loan::where('approved', false)->get();
        $approval_token_session = encrypt("empty");

        if (Session::get("approval_token_session")){
            $approval_token_session = Session::get("approval_token_session");
        }

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $branches = Branch::query()->where('status', '=', true)->get();
            $lfs = User::role('field_agent')->where('status', true)->get();
            $sub_title = "List of All Unaccepted Loans Agents";
            $check_role = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant');
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $branches = Branch::where('id', '=', $branch->id)->get();
            $lfs = User::role('field_agent')->where('status', true)->where('branch_id', '=', $branch->id)->get();
            $sub_title = "List of All registered Customers in " . $branch->bname;
            $check_role = false;
        }

        $approval_token_session = $approval_token_session;

        return view('pages.loans.agent_approve_revamped', [
            'title' => $title,
            'sub_title' => $sub_title,
            'loans' => $loans,
            'approval_token_session' => $approval_token_session,
            'branches' => $branches,
            'lfs' => $lfs,
            'check_role' => $check_role
        ]);
    }

    public function waiting_approve_loans(Request $request)
    {
        if ($request->branch) {
            if($request->branch == "all"){
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest',  DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false]])
                ->where('loans.source', 'branch')
                ->where([['loans.source', '=', 'branch']])

                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch') && $request->branch != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch);
                });
            } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest',  DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false], ['customers.branch_id', '=', $request->branch]])
                ->where([['loans.source', '=', 'branch']])

                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch_id);
                });
            }
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false]])
                ->where('loans.source', 'branch')
                ->where([['loans.source', '=', 'branch']])


                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch_id);
                });
        } elseif (Auth::user()->hasRole('field_agent')){
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest',  DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false], ['customers.field_agent_id', '=', Auth::user()->id]])
                ->where([['loans.source', '=', 'branch']])

                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch_id);
                });
        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest',  DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false], ['customers.branch_id', '=', Auth::user()->branch_id]])
                ->where([['loans.source', '=', 'branch']])

                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch_id);
                });
        }

        return Datatables::of($lo->get())
            ->editColumn('owner', function ($lo){
                $customer = Customer::find($lo->customer_id);
                $group = Group::find($lo->group_id);
                if ($group){
                    $group = $customer->group()->first();
                    $group_name = $group->name;

                    return $customer->fname. ' '. $customer->lname. '<br>'. '<span class="badge badge-primary" style="font-size: small">'.$group_name.'</span>';
                } else {
                    return $customer->fname. ' '. $customer->lname;
                }
            })
            ->addColumn('phone', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                return $customer->phone;
            })
            ->addColumn('id_number', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                return $customer->id_no;
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);
                $customerId = encrypt($lo->customer_id);
                $customer = encrypt($customerId);

                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('manager') || Auth::user()->hasRole('field_agent') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')){
                    if ($lo->document_path != null){
                        return '<div class="btn-group text-center">
                                    <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                    <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                        <li><a class="approve dropdown-item" href="' . route('loans.postApproveMultiple', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Accept</a></li>
                                        <li><a class="dropdown-item" href="' . route('registrys.edit', ['id' =>  ($lo->customer_id)]) . '"><i class="feather icon-edit" ></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="' . asset($lo->document_path) . '"><i class="feather icon-download text-success" ></i> View Loan Documents</a></li>
                                        <li><a class="dropdown-item" href="' . route('loans.viewDocuments', ['id' => $data]) . '"> <i class="feather icon-folder text-success"></i> View All Documents</a></li>
                                        <li><a class="ldelete dropdown-item" href="' . route('loans.delete', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                                    </ul>
                                </div>';
                    }else{
                        return '<div class="btn-group text-center">
                                    <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                    <ul class="dropdown-menu" style=" left: -10em; padding: 1em">

                                        <li><a class="approve dropdown-item" href="' . route('loans.postApproveMultiple', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Accept</a></li>
                                        <li><a class="dropdown-item" href="' . route('registrys.edit', ['id' =>  ($lo->customer_id)]) . '"><i class="feather icon-edit" ></i> Edit</a></li>
                                        <li><a class="dropdown-item" href="' . route('loans.viewDocuments', ['id' => $data]) . '"> <i class="feather icon-folder text-success"></i> View All Documents</a></li>
                                        <li><a class="ldelete dropdown-item" href="' . route('loans.delete', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                                    </ul>
                                </div>';
                    }
                } else {
                    return '<div class="btn-group text-center">
                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                    <li><a class="approve dropdown-item" href="' . route('loans.postApproveMultiple', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Accept</a></li>
                                    <li><a class="ldelete dropdown-item" href="' . route('loans.delete', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                                </ul>
                            </div>';
                }

            })
            ->addColumn('checkbox', function ($lo) {
                // return '<input type="checkbox" name="id[]" title="Check to approve" value="' . encrypt($lo->id) . '" >';
                return encrypt($lo->id);
            })
            ->addColumn('regPayment', function ($lo) {
                $setting = Setting::first();
                $customer = Customer::find($lo->customer_id);
                $reg_payment = $customer->regpayments()->sum('amount') >= $setting->registration_fee ? true : false;
                if ($reg_payment) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
            ->addColumn('branch', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('location', function ($lo) {
                $location = Customer_location::where('customer_id', $lo->customer_id)->first();

                return Str::title($location->ward).', '.Str::title($location->constituency).', '.Str::title($location->county->cname);
            })
            ->addColumn('referee', function ($lo) {
                $customer_referee = DB::table('customer_referee')->where('customer_id', $lo->customer_id)->first();

                if ($customer_referee) {
                    $referee = Referee::find($customer_referee->referee_id);

                    return Str::headline($referee->full_name).' ('.$referee->phone_number.')';
                }
                return '--';
            })
            ->rawColumns(['action', 'checkbox', 'owner'])
            ->make(true);
    }

    // public function viewDocuments($id)
    // {
    //     $loanId = decrypt($id);
    //     $loan = Loan::findOrFail($loanId);

    //     $documents = [
    //         'customer_id_front' => $loan->customer_id_front,
    //         'customer_id_back' => $loan->customer_id_back,
    //         'guarantor_id' => $loan->guarantor_id,
    //         'document_path' => $loan->document_path,
    //         'audio_path' => $loan->audio_path,
    //         'video_path' => $loan->video_path
    //     ];

    //     return view('pages.loans.documents', compact('loan', 'documents'));
    // }
    public function viewDocuments($id)
    {
        $loanId = decrypt($id);
        $loan = Loan::findOrFail($loanId);

        $documents = [
            'customer_id_front' => $loan->customer_id_front,
            'customer_id_back' => $loan->customer_id_back,
            'guarantor_id' => $loan->guarantor_id,
            'document_path' => $loan->document_path,
            'audio_path' => $loan->audio_path,
            'video_path' => $loan->video_path,
            'external_video_link' => $loan->external_video_link
        ];

        return view('pages.loans.documents', compact('loan', 'documents'));
    }

    public function deleteVideo($id)
    {
        $loanId = decrypt($id);
        $loan = Loan::findOrFail($loanId);

        // Delete the video file from storage
        if ($loan->video_path) {
            Storage::delete($loan->video_path);
            $loan->video_path = null;
            $loan->save();
        }

        return redirect()->back()->with('success', 'Video deleted successfully.');
    }

    public function updateExternalVideoLink(Request $request, $id)
    {
        $loanId = decrypt($id);
        $loan = Loan::findOrFail($loanId);

        $request->validate([
            'external_video_link' => 'url|nullable'
        ]);

        $loan->external_video_link = $request->input('external_video_link');
        $loan->save();

        return redirect()->back()->with('success', 'External video link updated successfully.');
    }



    /******************************* loan Approval ***********************/
    public function approval_initial()
    {
        $title = "Wait Approval Loans";
        $sub_title = "List of All Unapproved Loans";
        $loans = Loan::where('approved', false)->get();
        return view('pages.loans.approve', [
            'title' => $title,
            'sub_title' => $sub_title,
            'loans' => $loans,
        ]);
    }

    public function approval(Request $request)
    {
        $title = "Wait Approval Loans";
        $sub_title = "List of All Unapproved Loans";

        $loans = Loan::where('approved', false)->get();
        $approval_token_session = encrypt("empty");

        if (Session::get("approval_token_session")){
            $approval_token_session = Session::get("approval_token_session");
        }

        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $branches = Branch::query()->where('status', '=', true)->get();
            $lfs = User::role('field_agent')->where('status', true)->get();
            $sub_title = "List of All registered Customers";
            $check_role = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant');
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $branches = Branch::where('id', '=', $branch->id)->get();
            $lfs = User::role('field_agent')->where('status', true)->where('branch_id', '=', $branch->id)->get();
            $sub_title = "List of All registered Customers in " . $branch->bname;
            $check_role = false;
        }

        $approval_token_session = $approval_token_session;

        return view('pages.loans.approve_revamped', [
            'title' => $title,
            'sub_title' => $sub_title,
            'loans' => $loans,
            'approval_token_session' => $approval_token_session,
            'branches' => $branches,
            'lfs' => $lfs,
            'check_role' => $check_role
        ]);
    }

    public function approve_loans(Request $request)
    {
        if ($request->branch) {
            if($request->branch == "all"){
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest',  DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false]])
                ->where([['loans.source', '=', 'main']])
                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch') && $request->branch != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch);
                });
            } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest',  DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false], ['customers.branch_id', '=', $request->branch]])
                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch_id);
                });
            }
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
                ->where('loans.approved', '=', false)
                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch_id);
                });
        } elseif (Auth::user()->hasRole('field_agent')){
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest',  DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false], ['customers.field_agent_id', '=', Auth::user()->id]])
                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch_id);
                });
        } else {
            $lo = DB::table('loans')
                ->join('customers', 'customers.id', '=', 'loans.customer_id')
                ->join('products', 'products.id', '=', 'loans.product_id')
                ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest',  DB::raw('concat(fname, " ", lname) as  owner'))
                ->where([['loans.approved', '=', false], ['customers.branch_id', '=', Auth::user()->branch_id]])
                ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                    $query->whereDate('loans.created_at', Carbon::parse($request->date));
                })
                ->when($request->has('lf') && $request->lf != 'all', function ($query) use ($request) {
                    $query->where('customers.field_agent_id', $request->lf);
                })
                ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                    $query->where('customers.branch_id', $request->branch_id);
                });
        }

        return Datatables::of($lo->get())
            ->editColumn('owner', function ($lo){
                $customer = Customer::find($lo->customer_id);
                $group = Group::find($lo->group_id);
                if ($group){
                    $group = $customer->group()->first();
                    $group_name = $group->name;

                    return $customer->fname. ' '. $customer->lname. '<br>'. '<span class="badge badge-primary" style="font-size: small">'.$group_name.'</span>';
                } else {
                    return $customer->fname. ' '. $customer->lname;
                }
            })
            ->addColumn('phone', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                return $customer->phone;
            })
            ->addColumn('id_number', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                return $customer->id_no;
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);
                if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('manager') || Auth::user()->hasRole('field_agent') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')){
                    if ($lo->document_path != null){
                        return '<div class="btn-group text-center">
                                    <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                    <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                        <li><a class="approve dropdown-item" href="' . route('loans.post_approve', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Approve</a></li>
                                        <li><a class="dropdown-item" href="' . asset($lo->document_path) . '"><i class="feather icon-download text-success" ></i> View Loan Documents</a></li>
                                        <li><a class="ldelete dropdown-item" href="' . route('loans.delete', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                                    </ul>
                                </div>';
                    }else{
                        return '<div class="btn-group text-center">
                                    <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                    <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                        <li><a class="approve dropdown-item" href="' . route('loans.post_approve', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Approve</a></li>
                                        <li><a class="ldelete dropdown-item" href="' . route('loans.delete', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                                    </ul>
                                </div>';
                    }
                } else {
                    return '<div class="btn-group text-center">
                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                    <li><a class="approve dropdown-item" href="' . route('loans.post_approve', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Approve</a></li>
                                    <li><a class="ldelete dropdown-item" href="' . route('loans.delete', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                                </ul>
                            </div>';
                }

            })
            ->addColumn('checkbox', function ($lo) {
                // return '<input type="checkbox" name="id[]" title="Check to approve" value="' . encrypt($lo->id) . '" >';
                return encrypt($lo->id);
            })
            ->addColumn('regPayment', function ($lo) {
                $setting = Setting::first();
                $customer = Customer::find($lo->customer_id);
                $reg_payment = $customer->regpayments()->sum('amount') >= $setting->registration_fee ? true : false;
                if ($reg_payment) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
            ->addColumn('branch', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                return Branch::find($Customer->branch_id)->bname;
            })
            ->addColumn('location', function ($lo) {
                $location = Customer_location::where('customer_id', $lo->customer_id)->first();

                return Str::title($location->ward).', '.Str::title($location->constituency).', '.Str::title($location->county->cname);
            })
            ->addColumn('referee', function ($lo) {
                $customer_referee = DB::table('customer_referee')->where('customer_id', $lo->customer_id)->first();

                if ($customer_referee) {
                    $referee = Referee::find($customer_referee->referee_id);

                    return Str::headline($referee->full_name).' ('.$referee->phone_number.')';
                }
                return '--';
            })
            ->rawColumns(['action', 'checkbox', 'owner'])
            ->make(true);
    }

    /************************** approve a specific loan ****************************/
    public function post_approve(Request $request, $id)
    {
        // $token = decrypt(Session::get("approval_token_session"));

        $start_date = !empty($request->query('start_date')) && $request->query('start_date') != '' ? $request->query('start_date') : NULL;

        // $service = new Custom();
        // $tk = $service->check_token_validity($token, 'approve');
        // if ($tk == 0){
        //     return back()->with('error', 'Your activity token is invalid');
        // }

        /**************check if loan processing and reg fee has been paid**************/
        $payment = Payment::where(['loan_id' => decrypt($id), 'payment_type_id' => 3])->sum('amount');
        $loan = Loan::find(decrypt($id));
        $reg = Regpayment::where('customer_id', $loan->customer_id)->first();

        if ($reg) {
            $check_pay = $payment + (int) $reg->amount;
        } else {
            $check_pay = $payment;
        }

        if ($check_pay >= (int) Setting::first()->required_pay()) {
            Loan::find(decrypt($id))->update([
                'approved' => true,
                "approved_date" => $start_date,
                "approved_by" => Auth::user()->id,
                "approve_loan_ip" => $request->ip()
            ]);

            $loan = Loan::find(decrypt($id));

            $settings = Setting::first();

            //create installments
            $product = Product::find($loan->product_id);
            // $amountPayable = $loan->loan_amount + ($loan->loan_amount * $product->interest/100);
            $lp_fee = 0;
            if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
            {
                //break down loans to daily installments based on product duration
                $principle_amount = round($loan->total_amount / $product->duration);
                $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                if ($settings->lp_fee){
                    $lp_fee = $settings->lp_fee / $product->duration;
                }
            }
            else //WEEKLY REPAYMENTS
            {
                $principle_amount = round($loan->total_amount / $product->installments);
                $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                if ($settings->lp_fee){
                    $lp_fee = $settings->lp_fee / $product->installments;
                }
            }
            $amountPayable = $principle_amount;
            $days = 0;

            if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
            {
                for ($i = 0; $i < $product->duration; $i++) {
                    $days = $days + 1;
                    if ($i == 0) {
                        Installment::create([
                            "loan_id" => $loan->id,
                            "principal_amount" => $principle_amount,
                            "total" => $amountPayable,
                            "interest" => $interest_payable,
                            "lp_fee" => $lp_fee,
                            "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days - 1) : Carbon::now()->addDays(1)->addDays($days),
                            "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                            "current" => true,
                            "being_paid" => true,
                            "amount_paid" => 0,
                            "position" => $i + 1
                        ]);
                    } else {
                        Installment::create([
                            "loan_id" => $loan->id,
                            "principal_amount" => $principle_amount,
                            "total" => $amountPayable,
                            "interest" => $interest_payable,
                            "lp_fee" => $lp_fee,
                            "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days - 1) : Carbon::now()->addDays(1)->addDays($days),
                            "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                            "current" => false,
                            "amount_paid" => 0,
                            "position" => $i + 1
                        ]);
                    }
                }
            } else {
                for ($i = 0; $i < $product->installments; $i++) {
                    $days = $days + 7;
                    if ($i == 0) {
                        Installment::create([
                            "loan_id" => $loan->id,
                            "principal_amount" => $principle_amount,
                            "total" => $amountPayable,
                            "interest" => $interest_payable,
                            "lp_fee" => $lp_fee,
                            "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                            "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                            "current" => true,
                            "being_paid" => true,
                            "amount_paid" => 0,
                            "position" => $i + 1
                        ]);
                    } else {
                        Installment::create([
                            "loan_id" => $loan->id,
                            "principal_amount" => $principle_amount,
                            "total" => $amountPayable,
                            "interest" => $interest_payable,
                            "lp_fee" => $lp_fee,
                            "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                            "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                            "current" => false,
                            "amount_paid" => 0,
                            "position" => $i + 1
                        ]);
                    }
                }
            }

            Payment::create([
                'loan_id' => $loan->id,
                'amount' => $loan->loan_amount,
                'transaction_id' => Str::random(10),
                'date_payed' => $start_date ? Carbon::parse($start_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                'channel' => "MPESA",
                'payment_type_id' => 2,
            ]);

            $loan->update([
                "has_lp_fee" => true,
                "disbursed" => true,
                "disbursement_date" => $start_date,
                "end_date" => $start_date ? Carbon::parse($start_date)->addDays($loan->product()->first()->duration - 1) : Carbon::now()->addDays($loan->product()->first()->duration),
                "disbursed_by" => Auth::user()->id,
                'disburse_loan_ip' => $request->ip()
            ]);

            // check if registration payment is more than required
            $reg = Regpayment::where('customer_id', $loan->customer_id)->first();

            if ($reg) {
                //meaning the registration is greater than required so put the extra in loan processing fee
                if ($reg->amount > $settings->registration_fee) {
                    //balance after registration
                    $bal = $reg->amount - $settings->registration_fee;
                    $loans = Loan::where(['customer_id' => $loan->customer_id, 'settled' => true])->count();
                    if ($loans >= 1) {
                        if ((int)$settings->loan_processing_fee > 0) {
                            //meaning the remaining balance is greater than loan processing fee
                            if ((int) $bal > (int) $settings->loan_processing_fee) {
                                Payment::create([
                                    'payment_type_id' => 3,
                                    'loan_id' => $loan->id,
                                    'date_payed' => Carbon::now(),
                                    'transaction_id' => $reg->transaction_id,
                                    'channel' => 'MPESA',
                                    'amount' => $settings->loan_processing_fee
                                ]);
                                $rem = $bal - $settings->loan_processing_fee;

                                //then add the remaining to the loan settlement
                                if ($rem >= $loan->balance){
                                    $remainda = $rem - $loan->balance;
                                    Payment::create([
                                        'payment_type_id' => 1,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now(),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $loan->balance
                                    ]);
                                    $loan->update(['settled' => true]);
                                } else {
                                    Payment::create([
                                        'payment_type_id' => 1,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now(),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $rem
                                    ]);
                                    $remainda = 0;
                                }
                                //add the amount to current installment being paid
                                $handle_installments = new MpesaPaymentController();
                                $handle_installments->handle_installments($loan, $rem);
                            } //amount remaining is not greater than loan processing fee
                            else {
                                Payment::create([
                                    'payment_type_id' => 3,
                                    'loan_id' => $loan->id,
                                    'date_payed' => Carbon::now(),
                                    'transaction_id' => $reg->transaction_id,
                                    'channel' => 'MPESA',
                                    'amount' => $bal
                                ]);
                                $remainda = 0;
                            }
                            $reg->update([
                                'amount' => $settings->registration_fee + $remainda
                            ]);
                        } else {
                            if ($bal >= $loan->balance){
                                $remainda = $bal - $loan->balance;
                                Payment::create([
                                    'payment_type_id' => 1,
                                    'loan_id' => $loan->id,
                                    'date_payed' => Carbon::now(),
                                    'transaction_id' => $reg->transaction_id,
                                    'channel' => 'MPESA',
                                    'amount' => $loan->balance
                                ]);
                                $loan->update(['settled' => true]);
                            } else {
                                Payment::create([
                                    'payment_type_id' => 1,
                                    'loan_id' => $loan->id,
                                    'date_payed' => Carbon::now(),
                                    'transaction_id' => $reg->transaction_id,
                                    'channel' => 'MPESA',
                                    'amount' => $bal
                                ]);
                                $remainda = 0;
                            }
                            //add the amount to current installment being paid
                            $handle_installments = new MpesaPaymentController();
                            $handle_installments->handle_installments($loan, $bal);

                            $reg->update([
                                'amount' => $settings->registration_fee + $remainda
                            ]);
                        }
                    } else {
                        if ($bal >= $loan->balance){
                            $remainda = $bal - $loan->balance;
                            Payment::create([
                                'payment_type_id' => 1,
                                'loan_id' => $loan->id,
                                'date_payed' => Carbon::now(),
                                'transaction_id' => $reg->transaction_id,
                                'channel' => 'MPESA',
                                'amount' => $loan->balance
                            ]);
                            $loan->update(['settled' => true]);
                        } else {
                            Payment::create([
                                'payment_type_id' => 1,
                                'loan_id' => $loan->id,
                                'date_payed' => Carbon::now(),
                                'transaction_id' => $reg->transaction_id,
                                'channel' => 'MPESA',
                                'amount' => $bal
                            ]);
                            $remainda = 0;
                        }
                        //add the amount to current installment being paid
                        $handle_installments = new MpesaPaymentController();
                        $handle_installments->handle_installments($loan, $bal);

                        $reg->update([
                            'amount' => $settings->registration_fee + $remainda
                        ]);
                    }
                }
            }

            $env = "sandbox";

            if ($env == 'live') {
                /***************************Send sms*********************************/
                $phone = '+254' . substr($loan->customer->phone, -9);
                $user_type = false;
                $message = "Your Loan of Ksh " . $loan->total_amount . ' has been approved. You will receive an Mpesa notification';
                // dispatch(new Sms(
                //     $phone, $message, $loan->customer, $user_type
                // ));

                //update admin and accountant
                $aphones = ["+25411591065"];
                foreach ($aphones as $aphone)
                {
                    $suser_type = true;
                    if ($loan->loan_type_id == 1){
                        $amessage = "Daily Repayment Loan of Ksh " . $loan->total_amount . ' has been approved and sent to ' . $phone;
                    } elseif ($loan->loan_type_id == 2) {
                        $amessage = "Weekly Repayment Loan of Ksh " . $loan->total_amount . ' has been approved and sent to ' . $phone;
                    } else {
                        $amessage = "Loan of Ksh " . $loan->total_amount . ' has been approved and sent to ' . $phone;
                    }
                    $auser = User::first();
                    // dispatch(new Sms(
                    //     $aphone, $amessage, $auser, $suser_type
                    // ));
                }
            }

            return back()->with('success', 'Successfully approved Loan');
        }

        return back()->with('error', 'Loan Processing fee has not been paid');
    }

    /******************************* approve multiple ***************************/
    public function post_approve_multiple(Request $request)
    {
        // $token = decrypt(Session::get("approval_token_session"));

        // $service = new Custom();

        // $tk = $service->check_token_validity($token, 'approve');

        // if ($tk == 0){
        //     return back()->with('error', 'Your activity token is invalid');
        // }

        $result = DB::transaction(function () use ($request) {
            $usa = \auth()->user();
            $ip = $request->ip();
            $approved_date = Carbon::now('Africa/Nairobi');
            $start_date = $request->has('start_date') && !empty($request->start_date) && $request->start_date != '' ? $request->start_date : NULL;

            foreach ($request->id as $id) {
                $payment = Payment::where(['loan_id' => decrypt($id), 'payment_type_id' => 3])->sum('amount');
                $loan = Loan::find(decrypt($id));
                $reg = Regpayment::where('customer_id', $loan->customer_id)->first();

                if ($reg) {
                    $check_pay = $payment + (int)$reg->amount;
                } else {
                    $check_pay = $payment;
                }

                if ($check_pay >= (int)Setting::first()->required_pay()) {
                    $loan = Loan::find(decrypt($id))->update([
                        'approved' => true,
                        "approved_date" => $start_date ? Carbon::parse($start_date) : $approved_date,
                        "approved_by" => $usa->id,
                        "approve_loan_ip" => $ip
                    ]);
                    $settings = Setting::first();

                    $loan = Loan::find(decrypt($id));

                    //create installments
                    $product = Product::find($loan->product_id);

                    $lp_fee = 0;

                    if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                    {
                        //break down loans to daily installments based on product duration
                        $principle_amount = round($loan->total_amount / $product->duration);
                        $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                        if ($settings->lp_fee){
                            $lp_fee = $settings->lp_fee / $product->duration;
                        }
                    }
                    else //WEEKLY REPAYMENTS
                    {
                        $principle_amount = round($loan->total_amount / $product->installments);
                        $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                        if ($settings->lp_fee){
                            $lp_fee = $settings->lp_fee / $product->installments;
                        }
                    }
                    $amountPayable = $principle_amount;
                    $days = 0;

                    if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                    {
                        for ($i = 0; $i < $product->duration; $i++) {
                            $days = $days + 1;
                            if ($i == 0) {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days - 1) : Carbon::now()->addDays(1)->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => true,
                                    "being_paid" => true,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            } else {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days - 1) : Carbon::now()->addDays(1)->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => false,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            }
                        }
                    } else {
                        for ($i = 0; $i < $product->installments; $i++) {
                            $days = $days + 7;
                            if ($i == 0) {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => true,
                                    "being_paid" => true,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            } else {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => false,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            }
                        }
                    }

                    $loan->update([
                        "has_lp_fee" => true,
                        "disbursed" => true,
                        "disbursement_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                        "end_date" => $start_date ? Carbon::parse($start_date)->addDays($loan->product()->first()->duration - 1) : Carbon::now()->addDays($loan->product()->first()->duration),
                        "disbursed_by" => Auth::user()->id,
                        'disburse_loan_ip' => $request->ip()
                    ]);

                    //check if registration payment is more than required
                    $reg = Regpayment::where('customer_id', $loan->customer_id)->first();
                    // $settings = Setting::first();
                    if ($reg) {
                        //meaning the registration is greater than required so put the extra in loan processing fee
                        if ($reg->amount > $settings->registration_fee) {
                            //balance after registration
                            $bal = $reg->amount - $settings->registration_fee;
                            $loans = Loan::where(['customer_id' => $loan->customer_id, 'settled' => true])->count();
                            if ($loans >= 1) {
                                if ((int)$settings->loan_processing_fee > 0) {
                                    //meaning the remaining balance is greater than loan processing fee
                                    if ((int) $bal > (int) $settings->loan_processing_fee) {
                                        Payment::create([
                                            'payment_type_id' => 3,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now(),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $settings->loan_processing_fee
                                        ]);
                                        $rem = $bal - $settings->loan_processing_fee;

                                        //then add the remaining to the loan settlement
                                        if ($rem >= $loan->balance) {
                                            $remainda = $rem - $loan->balance;
                                            Payment::create([
                                                'payment_type_id' => 1,
                                                'loan_id' => $loan->id,
                                                'date_payed' => Carbon::now(),
                                                'transaction_id' => $reg->transaction_id,
                                                'channel' => 'MPESA',
                                                'amount' => $loan->balance
                                            ]);
                                            $loan->update(['settled' => true]);
                                        } else {
                                            Payment::create([
                                                'payment_type_id' => 1,
                                                'loan_id' => $loan->id,
                                                'date_payed' => Carbon::now(),
                                                'transaction_id' => $reg->transaction_id,
                                                'channel' => 'MPESA',
                                                'amount' => $rem
                                            ]);
                                            $remainda = 0;
                                        }
                                        //add the amount to current installment being paid
                                        $handle_installments = new MpesaPaymentController();
                                        $handle_installments->handle_installments($loan, $rem);
                                    } //amount remaining is not greater than loan processing fee
                                    else {
                                        Payment::create([
                                            'payment_type_id' => 3,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now(),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $bal
                                        ]);
                                        $remainda = 0;
                                    }
                                    $reg->update([
                                        'amount' => $settings->registration_fee + $remainda
                                    ]);
                                } else {
                                    if ($bal >= $loan->balance){
                                        $remainda = $bal - $loan->balance;
                                        Payment::create([
                                            'payment_type_id' => 1,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now(),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $loan->balance
                                        ]);
                                        $loan->update(['settled' => true]);
                                    } else {
                                        Payment::create([
                                            'payment_type_id' => 1,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now(),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $bal
                                        ]);
                                        $remainda = 0;
                                    }
                                    //add the amount to current installment being paid
                                    $handle_installments = new MpesaPaymentController();
                                    $handle_installments->handle_installments($loan, $bal);

                                    $reg->update([
                                        'amount' => $settings->registration_fee + $remainda
                                    ]);
                                }
                            } else {
                                if ($bal >= $loan->balance){
                                    $remainda = $bal - $loan->balance;
                                    Payment::create([
                                        'payment_type_id' => 1,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now(),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $loan->balance
                                    ]);
                                    $loan->update(['settled' => true]);
                                } else {
                                    Payment::create([
                                        'payment_type_id' => 1,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now(),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $bal
                                    ]);
                                    $remainda = 0;
                                }
                                //add the amount to current installment being paid
                                $handle_installments = new MpesaPaymentController();
                                $handle_installments->handle_installments($loan, $bal);

                                $reg->update([
                                    'amount' => $settings->registration_fee + $remainda
                                ]);
                            }
                        }
                    }

                    $env = "sandbox";

                    if ($env == 'live') {
                        /***************************Send sms*********************************/
                        $phone = '+254' . substr($loan->customer->phone, -9);
                        $user_type = false;
                        $message = "Your Loan of Ksh " . $loan->total_amount . ' has been approved. You will receive an Mpesa notification';
                        // dispatch(new Sms(
                        //     $phone, $message, $loan->customer, $user_type
                        // ));

                        //update admin and accountant
                        $aphones = ["+25411591065"];
                        foreach ($aphones as $aphone)
                        {
                            $suser_type = true;
                            if ($loan->loan_type_id == 1){
                                $amessage = "Daily Repayment Loan of Ksh " . $loan->total_amount . ' has been approved and sent to ' . $phone;
                            }elseif ($loan->loan_type_id == 2){
                                $amessage = "Weekly Repayment Loan of Ksh " . $loan->total_amount . ' has been approved and sent to ' . $phone;
                            }else{
                                $amessage = "Loan of Ksh " . $loan->total_amount . ' has been approved and sent to ' . $phone;
                            }
                            $auser = User::first();
                            // dispatch(new Sms(
                            //     $aphone, $amessage, $auser, $suser_type
                            // ));
                        }
                    }
                } else {
                    return false;
                }
            }
            return true;
        });

        if ($result) {
            return back()->with('success', 'Successfully approved Loans');
        } else {
            return back()->with('error', 'Some of the loans registration fee has not been paid');
        }
    }

    /************************************** group loans logic *******************************************/
    public function group_create()
    {
        if (!Auth::user()->hasRole(['field_agent'])) {
            return back()->with('error', 'Unauthorized');
        }
        $this->data['title'] = "Create New Loan - Group Lending";
        $this->data['is_edit'] = false;
        $this->data['customers'] = Customer::all();
        $this->data['products'] = Product::all();
        $this->data['loan_types'] = LoanType::all();

        return view('pages.loans.group-registration', $this->data);
    }

    public function store_group_loan(Request $request)
    {
        if (!Auth::user()->hasRole(['field_agent'])) {
            return back()->with('error', 'Unauthorized');
        }
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|min:3',
            'id_no' => 'required|exists:customers,id_no',
            'group_id' => 'required|exists:groups,id',
            'phone' => 'required|exists:customers,phone',
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'purpose' => 'required',
            'loan_type' => 'required|exists:loan_types,id',
            'installments' => 'required',
            'loan_amount' => 'required',
            'loan_form' => 'mimes:pdf|max:10000',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Validation Error, refresh page and try again');
        }
        $product = Product::find($request->product_id);
        $enddate = Carbon::now()->addDays($product->duration);
        //dd($enddate);


        /*************************find if the customer has a loan she has not cleared***************/
        $loan = Loan::where(['customer_id' => $request->customer_id, 'settled' => false])->first();
        $group = Group::find($request->group_id);
        $customer = Customer::find($request->customer_id);
        $branch = Branch::find($customer->branch_id);
        $loan_type = LoanType::find($request->loan_type);
        if (!$group){
            return back()->with('error', 'Could not create a new Loan. This group details not found')->withInput();
        }
        if ($customer->status == 1){
            if (!isset($loan)) {
                if ($request->loan_amount > 20000 || $request->loan_amount > $customer->prequalified_amount){
                    return back()->with('error', 'Loan amount cannot be greater than '.$customer->prequalified_amount)->withInput();

                }

                $create_loan = Loan::create([
                    'loan_amount' => $request->loan_amount,
                    'product_id' => $request->product_id,
                    'group_id' => $request->group_id,
                    'customer_id' => $request->customer_id,
                    'loan_type_id' => $loan_type->id,
                    'date_created' => Carbon::now('Africa/Nairobi'),
                    'purpose' => $request->purpose,
                    'loan_account' => $branch->bname . "-" . date('m/d') . "-" . mt_rand(10, 10000),
                    'end_date' => $enddate,
                    'created_by' => Auth::id(),
                    'create_loan_ip' => $request->ip()
                ]);

                if($file = $request->file('loan_form'))
                {

                    $file_name = $customer->fname.'_'.$customer->lname.'-'.$create_loan->id;
                    $extension = $file->extension();
                    $file_name = $file_name .".". $extension;
                    Storage::disk('public')->putFileAs('loan_application_forms', $file, $file_name);
                    $path = Storage::url('loan_application_forms/'.$file_name);
                    $create_loan->update([
                        'document_path'=>$path
                    ]);
                }

                return back()->with('success', 'Successfully created Group Loan');
            }
            else{
                return back()->with('error', 'Could not create a new Loan. This customer has unsettled Loan')->withInput();
            }
        }
        else{
            return back()->with('error', 'Could not create a new Loan. This customer has been blocked, contact admin for more information')->withInput();
        }
    }

    public function customer_group_data()
    {
        $user = Auth::user();
        if ($user->hasRole('field_agent')){
            $lo = Customer::whereHas('group', function ($q){
                $q->where('approved', '=', true);
            })->whereDoesntHave('loans', function($query) {
                $query->where('settled', false);
            })->with('group')->where('field_agent_id', '=', $user->id)->select('*');
        } else {
            $lo = Customer::whereHas('group', function ($q){
                $q->where('approved', '=', true);
            })->whereDoesntHave('loans', function($query) {
                $query->where('settled', false);
            })->with('group')->select('*');
        }

        return Datatables::of($lo)
            ->addColumn('loan_status', function ($lo) {
                return '<span class="badge badge-success">Valid Applicant</span>';
            })
            ->addColumn('group_name', function ($lo) {

                return ($lo->group) ? $lo->group[0]->name : '--';
            })
            ->addColumn('group_id', function ($lo) {
                return ($lo->group) ? $lo->group[0]->id : '--';
            })
            ->addColumn('action', function ($lo) {
                $cust = Customer::find($lo->id);
                $data = $lo->id;
                $fullname = $lo->fname . ' ' . $lo->lname;
                $group_id = $cust->group()->first()->id;
                $group = $cust->group()->first()->name;
                return '<button type="button" data-dismiss="modal" data-id="' . $data . '" data-fullname="' . $fullname . '"
                            data-amount="' . $lo->prequalified_amount . '" data-idno="' . $lo->id_no . '" data-phone="' . $lo->phone . '"
                                data-group_id="'.$group_id.'" data-group_name="'.$group.'" class="sel-btn btn btn-xs btn-primary">
                                <i class="feather icon-edit text-warning"></i> Select</a>';
            })
            ->rawColumns(['action', 'loan_status'])
            ->make(true);

    }

    /************************************** restructure customer loans *******************************************/
    public function loan_restructure()
    {
        $title = "Restructure Customer Loans to Daily Repayments";
        $sub_title = "Select Customer";
        $branches = Branch::query()->where('status', '=',true)->get();
        $lfs = User::role('field_agent')->where('status', true)->get();

        return view("pages.loans.restructure.select_customer", [
            'title' => $title,
            'sub_title' => $sub_title,
            'branches' => $branches,
            'lfs' => $lfs,
        ]);
    }

    public function show_customer_loans($id)
    {
        $customer = Customer::find($id);
        if ($customer){
            $title = $customer->fname .' '. $customer->lname. "'s Loans";
            $sub_title = "Select Loan";
            $customer = $customer;

            return view("pages.loans.restructure.customer_loans", [
                'title' => $title,
                'sub_title' => $sub_title,
                'customer' => $customer,
            ]);
        }else{
            return redirect()->back()->with('error', 'Customer not found!');
        }
    }

    public function customer_loans_data($id)
    {
        $customer = Customer::find($id);
        $lo = DB::table('loans')
            ->join('customers', 'customers.id', '=', 'loans.customer_id')
            ->join('products', 'products.id', '=', 'loans.product_id')
            ->select('loans.*', 'customers.fname', 'customers.lname', 'customers.phone', 'customers.branch_id', 'products.product_name', 'products.installments', 'products.interest', DB::raw('concat(fname, " ", lname) as  owner'))
            ->where('customers.id', '=', $customer->id);

        return Datatables::of($lo)
            ->editColumn('owner', function ($lo){
                $customer = Customer::find($lo->customer_id);
                $group = Group::find($lo->group_id);
                if ($group){
                    //$group = $customer->group()->first();
                    $group_name = $group->name;

                    return $customer->fname. ' '. $customer->lname. '<br>'. '<span class="badge badge-primary" style="font-size: small">'.$group_name.'</span>';
                }else{
                    return $customer->fname. ' '. $customer->lname;
                }

            })
            ->addColumn('branch', function ($lo) {

                $Customer = Customer::find($lo->customer_id);

                return Branch::find($Customer->branch_id)->bname;
            })
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

                return number_format($payments);
            })
            ->addColumn('total', function ($lo) {
                $product = Product::find($lo->product_id);
                if ($lo->has_lp_fee) {
                    $setting = Setting::query()->first();
                    if ($setting->lp_fee) {
                        $lp_fee = $setting->lp_fee;
                    } else {
                        $lp_fee = 0;
                    }
                } else {
                    $lp_fee = 0;
                }
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest + $lp_fee;
                } else {
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $lp_fee;
                }

                return number_format($total);
            })
            ->addColumn('balance', function ($lo) {
                /*$total = $this->payment()->where('payment_type_id', 1)->sum('amount');*/
                $payments = Payment::where(['loan_id' => $lo->id, 'payment_type_id' => 1])->sum('amount');

                $product = Product::find($lo->product_id);
                if ($lo->has_lp_fee) {
                    $setting = Setting::query()->first();
                    if ($setting->lp_fee) {
                        $lp_fee = $setting->lp_fee;
                    } else {
                        $lp_fee = 0;
                    }
                } else {
                    $lp_fee = 0;
                }
                if ($lo->rolled_over) {
                    $rollover = Rollover::where('loan_id', $lo->id)->first();
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $rollover->rollover_interest + $lp_fee;
                }
                else{
                    $total = $lo->loan_amount + ($lo->loan_amount * ($product->interest / 100)) + $lp_fee;
                }
                $balance = $total - $payments;

                return number_format($balance);
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);
               return '<div class="btn-group text-center">
                                        <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                <ul class="dropdown-menu" style="">
                                                    <li><a class="dropdown-item" href="' . route('loans.restructure', ['id' => $data]) . '"><i class="feather icon-eye text-info" ></i> Select</a></li>
                                                </ul>
                                </div>';
            })
            ->rawColumns(['action', 'owner'])
            //->make(true);
            ->toJson();

    }

    public function restructureCustomerData(Request $request)
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');
        $customers = Customer::select('*')->whereIn('branch_id', $activeBranches);
        if ($request->lf != 'all') {
            $customers = $customers->where('field_agent_id', $request->lf);
        }
        elseif ($request->branch != 'all'){
            $customers = $customers->where('branch_id', $request->branch);
        }
        elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $customers = Customer::select('*')->whereIn('branch_id', $activeBranches);
        }
        elseif (Auth::user()->hasRole('field_agent')) {
            $customers = $customers->where('field_agent_id', Auth::id());
        }
        else {
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
                return '<a class="btn btn-primary" href="' . route('loans.show_customer_loans', $customer->id) . '"><i class="feather icon-eye"></i> Select</a>';;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function restructure($id)
    {
        $loan = Loan::find(decrypt($id));
        $customer = Customer::where('id', '=', $loan->customer_id)->first();
        $title = $customer->fname .' '. $customer->lname. "'s Loan ID - ". $loan->id;
        $sub_title = "Select Restructure Plan";
        $loanAmount = $loan->loan_amount;
        $complete = $loan->Installments()->where(['completed'=>true])->count();
        $incomplete = $loan->Installments()->where(['completed'=>false])->count();
        $paidAmount = $loan->amount_paid;
        $balance = $loan->balance;
        $loan = $loan;

        return view("pages.loans.restructure.restructure", [
            'title' => $title,
            'sub_title' => $sub_title,
            'loanAmount' => $loanAmount,
            'complete' => $complete,
            'incomplete' => $incomplete,
            'paidAmount' => $paidAmount,
            'balance' => $balance,
            'loan' => $loan,
        ]);
    }

    public function restructure_post(Request $request, $loan_id){
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:7|max:70',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $loan = Loan::find(decrypt($loan_id));
        if ($loan->settled == true){
            return redirect()->back()->with('warning', 'Loan cannot be restructured as it has been settled');
        } else {
            if ($loan->restructured == true){
                return redirect()->back()->with('warning', 'Selected loan has already been restructured before, contact System Admin for assistance.');
            }
            //moves previous installments to the restructured Installment table
            $installments = Installment::where('loan_id', '=', $loan->id)->get();
                foreach ($installments as $installment){
                    $oldInstallments = RestructuredInstallment::create([
                        "loan_id" => $loan->id,
                        "principal_amount" => $installment->principal_amount,
                        "total" => $installment->total,
                        "interest" => $installment->interest,
                        "due_date" =>$installment->due_date,
                        "start_date" => $installment->start_date,
                        "current" => $installment->current,
                        "being_paid" => $installment->being_paid,
                        "amount_paid" => $installment->amount_paid,
                        "position" => $installment->position,
                        "in_arrear" => $installment->in_arrear,
                        "completed" => $installment->completed,
                        "interest_payment_date" => $installment->interest_payment_date,
                        "last_payment_date" => $installment->last_payment_date,
                        "for_rollover" => $installment->for_rollover,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);

                    //delete
                    $installment->delete();
                }
            //create new based on the set duration
            $product = Product::find($loan->product_id);
            $interest_on_balance = ($loan->balance * $product->interest / 100) /  $request->get('days'); //interest on balance
            $principle_amount = ceil($loan->balance / $request->get('days')) - $interest_on_balance; //balance subtract computed interest
            $interest_payable = $interest_on_balance;
            $amountPayable = $principle_amount + $interest_payable; //should still sum to original balance
            $days = 0;
            for ($i = 0; $i < $request->get('days'); $i++) {
                //$days = $days + 7;
                $days = $days + 1;
                if ($i == 0) {
                    Installment::create([
                        "loan_id" => $loan->id,
                        "principal_amount" => $principle_amount,
                        "total" => $amountPayable,
                        "interest" => $interest_payable,
                        "due_date" => Carbon::now()->addDays($days),
                        "start_date" => Carbon::now(),
                        "current" => true,
                        "being_paid" => true,
                        "amount_paid" => 0,
                        "position" => $i + 1
                    ]);
                } else {
                    Installment::create([
                        "loan_id" => $loan->id,
                        "principal_amount" => $principle_amount,
                        "total" => $amountPayable,
                        "interest" => $interest_payable,
                        "due_date" => Carbon::now()->addDays($days),
                        "start_date" => Carbon::now(),
                        "current" => false,
                        "amount_paid" => 0,
                        "position" => $i + 1

                    ]);
                }
            }
            //mark loan as restructured
            $loan->update(['loan_type_id' => 1, 'restructured' =>true]);

            //send SMS to user?
            return redirect()->back()->with('success', 'Loan has successfully been restructured to be paid in '. $request->get('days'). ' days');
        }
    }

    public function installments_data($id)
    {
        $loan = Loan::find($id);
        $lo = DB::table('installments')
                ->select('*')
                ->where('loan_id', '=' , $loan->id);

        return Datatables::of($lo)
            ->editColumn('completed', function ($lo){
                if ($lo->completed) {
                    return '<span class="badge badge-success" style="font-size: small">Complete</span>';
                }
                return '<span class="badge badge-warning" style="font-size: small">Incomplete</span>';
            })
            ->editColumn('being_paid', function ($lo){
                if ($lo->being_paid) {
                    return '<span class="badge badge-primary" style="font-size: small">being paid</span>';
                }
                return '--';
            })
            ->editColumn('current', function ($lo){
                if ($lo->current) {
                    return '<span class="badge badge-secondary" style="font-size: small">current</span>';
                }
                return '--';
            })
            ->rawColumns(['completed', 'being_paid', 'current', 'in_arrear'])
            //->make(true);
            ->toJson();
    }

    public function add_collateral($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item' => 'required',
            'market_value'=> 'required',
            'image_url' => ['nullable', 'mimes:png,jpg,jpeg']
        ]);

        $loan = Loan::find(decrypt($id));
        if (!$loan){
            abort(404);
        }
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $image = $request->file('image_url');
        $profile_photo_path = null;
        if ($image){
            $profile_photo_file_name = $loan->id.'_'.time();
            $profile_photo_extension = $image->extension();
            $profile_photo_file_name = $profile_photo_file_name .".". $profile_photo_extension;
            $image->move(public_path('assets/collaterals/'.$loan->id), $profile_photo_file_name);

            $profile_photo_path = 'assets/collaterals/'.$loan->id.'/'.$profile_photo_file_name;
        }

        Collateral::insert([
            'item' => $request->item,
            'loan_id' => $loan->id,
            'description' => $request->description,
            'serial_no' => $request->serial_no,
            'market_value' => $request->market_value,
            'image_url' => $profile_photo_path
        ]);

        return back()->with('success', 'Successfully added the collateral ');
    }

    public function uploadDisbursement(Request $request)
    {
        Excel::import(new DisbursementImport, $request->file('file')->store('public'));
    }

    public function updateInstallments()
    {
        $installments = Installment::all();

        foreach ($installments as $installment) {
            $loan = Loan::find($installment->loan_id);
            $product = Product::find($loan->product_id);

            $principle_amount = ceil($loan->total_amount / $product->installments);
            $interest_payable = ceil(($loan->total_amount - $loan->loan_amount) / $product->installments);

            $installment->update([
                'principle_amount' => $principle_amount,
                "total" => $principle_amount,
                "interest" => $interest_payable,
            ]);
        }
    }

    public function reconcileTransactions(Request $request)
    {
        Excel::import(new ReconcileTransactions, $request->file('file')->store('public'));
    }

    public function restructure_loan(Request $request)
    {
        $loan_id = $request->loan_id;
        $loan_amount = $request->loan_amount;
        $disbursed_amount = $request->disbursed_amount;
        $product_id = $request->product_id;

        // Get loan
        $loan = Loan::find($loan_id);
        if ($loan) {
            try {
                DB::beginTransaction();

                $first_installment = Installment::where('loan_id', $loan->id)->first()->due_date;
                $start_date = Carbon::parse($first_installment);

                // Move all payments to reg payment
                $payments = Payment::where('loan_id', $loan->id)->whereIn('payment_type_id', [1, 3])->get();
                $reg_payment = Regpayment::where('customer_id', $loan->customer->id)->first();
                // $amount_paid = $loan->total_amount_paid;
                $amount_paid = 0;
                foreach ($payments as $payment) {
                    $amount_paid += $payment->amount;
                }

                $reg_payment->update([
                    'amount' => $reg_payment->amount + $amount_paid
                ]);

                // Delete all installments
                Installment::where('loan_id', $loan->id)->delete();
                Payment::where('loan_id', $loan->id)->delete();

                // Restructure Loan
                $loan->update([
                    'loan_amount' => $disbursed_amount,
                    'product_id' => $product_id,
                    'total_amount' => $loan_amount,
                    'total_amount_paid' => 0
                ]);

                // Create Installments
                $loan = Loan::find($loan_id);

                $settings = Setting::first();

                //create installments
                $product = Product::find($loan->product_id);

                $lp_fee = 0;

                if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                {
                    //break down loans to daily installments based on product duration
                    $principle_amount = round($loan->total_amount / $product->duration);
                    $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                    if ($settings->lp_fee){
                        $lp_fee = $settings->lp_fee / $product->duration;
                    }
                }
                else //WEEKLY REPAYMENTS
                {
                    $principle_amount = round($loan->total_amount / $product->installments);
                    $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                    if ($settings->lp_fee){
                        $lp_fee = $settings->lp_fee / $product->installments;
                    }
                }
                $amountPayable = $principle_amount;
                $days = 0;

                if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                {
                    for ($i = 0; $i < $product->duration; $i++) {
                        $days = $days + 1;
                        if ($i == 0) {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date ? (Carbon::parse($start_date)->addDays($days)->equalTo(now()->format('Y-m-d')) ? true : false) : (Carbon::now()->addDays(1)->addDays($days)->equalTo(now()) ? true : false),
                                "being_paid" => true,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        } else {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date ? (Carbon::parse($start_date)->addDays($days)->equalTo(now()->format('Y-m-d')) ? true : false) : (Carbon::now()->addDays(1)->addDays($days)->equalTo(now()) ? true : false),
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        }
                    }
                } else {
                    for ($i = 0; $i < $product->installments; $i++) {
                        $days = $days + 7;
                        if ($i == 0) {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => true,
                                "being_paid" => true,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        } else {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => false,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        }
                    }
                }

                Payment::create([
                    'loan_id' => $loan->id,
                    'amount' => $loan->loan_amount,
                    'transaction_id' => Str::random(10),
                    'date_payed' => $start_date ? Carbon::parse($start_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                    'channel' => "MPESA",
                    'payment_type_id' => 2,
                ]);

                //check if registration payment is more than required
                $reg = Regpayment::where('customer_id', $loan->customer_id)->first();

                if ($reg) {
                    //meaning the registration is greater than required so put the extra in loan processing fee
                    if ($reg->amount > $settings->registration_fee) {
                        //balance after registration
                        $bal = $reg->amount - $settings->registration_fee;
                        $loans = Loan::where(['customer_id' => $loan->customer_id, 'settled' => true])->count();
                        if ($loans <= 0) {
                            if ($bal >= $loan->balance){
                                $remainda = $bal - $loan->balance;
                                Payment::create([
                                    'payment_type_id' => 1,
                                    'loan_id' => $loan->id,
                                    'date_payed' => Carbon::now(),
                                    'transaction_id' => $reg->transaction_id,
                                    'channel' => 'MPESA',
                                    'amount' => $loan->balance
                                ]);
                                $loan->update(['settled' => true]);
                            } else {
                                Payment::create([
                                    'payment_type_id' => 1,
                                    'loan_id' => $loan->id,
                                    'date_payed' => Carbon::now(),
                                    'transaction_id' => $reg->transaction_id,
                                    'channel' => 'MPESA',
                                    'amount' => $bal
                                ]);
                                $remainda = 0;
                            }
                            //add the amount to current installment being paid
                            $handle_installments = new MpesaPaymentController();
                            $handle_installments->handle_installments($loan, $bal);

                            $reg->update([
                                'amount' => $settings->registration_fee + $remainda
                            ]);
                        } else {
                            if ($settings->loan_processing_fee > 0) {
                                //meaning the remaining balance is greater than loan processing fee
                                if ($bal > $settings->loan_processing_fee) {
                                    Payment::create([
                                        'payment_type_id' => 3,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::parse($reg_payment->updated_at),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $settings->loan_processing_fee
                                    ]);
                                    $rem = $bal - $settings->loan_processing_fee;

                                    //then add the remaining to the loan settlement
                                    if ($rem >= $loan->balance){
                                        $remainda = $rem - $loan->balance;
                                        Payment::create([
                                            'payment_type_id' => 1,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::parse($reg_payment->updated_at),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $loan->balance
                                        ]);
                                        $loan->update(['settled' => true]);
                                    } else {
                                        Payment::create([
                                            'payment_type_id' => 1,
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::parse($reg_payment->updated_at),
                                            'transaction_id' => $reg->transaction_id,
                                            'channel' => 'MPESA',
                                            'amount' => $rem
                                        ]);
                                        $remainda = 0;
                                    }
                                    //add the amount to current installment being paid
                                    $handle_installments = new MpesaPaymentController();
                                    $handle_installments->handle_installments($loan, $rem);
                                } //amount remaining is not greater than loan processing fee
                                else {
                                    Payment::create([
                                        'payment_type_id' => 3,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::parse($reg_payment->updated_at),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $bal
                                    ]);
                                    $remainda = 0;
                                }
                                $reg->update([
                                    'amount' => $settings->registration_fee + $remainda
                                ]);
                            } else {
                                if ($bal >= $loan->balance){
                                    $remainda = $bal - $loan->balance;
                                    Payment::create([
                                        'payment_type_id' => 1,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now(),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $loan->balance
                                    ]);
                                    $loan->update(['settled' => true]);
                                } else {
                                    Payment::create([
                                        'payment_type_id' => 1,
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now(),
                                        'transaction_id' => $reg->transaction_id,
                                        'channel' => 'MPESA',
                                        'amount' => $bal
                                    ]);
                                    $remainda = 0;
                                }
                                //add the amount to current installment being paid
                                $handle_installments = new MpesaPaymentController();
                                $handle_installments->handle_installments($loan, $bal);

                                $reg->update([
                                    'amount' => $settings->registration_fee + $remainda
                                ]);
                            }
                        }

                        $loan->update(['total_amount_paid' => $bal]);
                    }
                }

                DB::commit();

                return response()->json('Restructured Loan');
            } catch (\Throwable $th) {
                info ($th);
                DB::rollBack();
                return response()->json('Failed to updated Loan', 500);
            }
        }
    }

    public function removePayment(Request $request)
    {
        $request->validate([
            'transaction_id' => ['required']
        ]);

        $payment = Payment::where('transaction_id', $request->transaction_id)->where('payment_type_id', 1)->first();

        if ($payment) {
            try {
                DB::beginTransaction();

                // Delete payment
                $payment->delete();

                $due_date = Installment::where('loan_id', $payment->loan_id)->first()->due_date;

                $start_date = Carbon::parse($due_date);

                // Redo the installments with correct amount paid
                Installment::where('loan_id', $payment->loan_id)->delete();

                // Create Installments
                $loan = Loan::find($payment->loan_id);

                $settings = Setting::first();

                //create installments
                $product = Product::find($loan->product_id);

                $lp_fee = 0;

                if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                {
                    //break down loans to daily installments based on product duration
                    $principle_amount = round($loan->total_amount / $product->duration);
                    $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                    if ($settings->lp_fee){
                        $lp_fee = $settings->lp_fee / $product->duration;
                    }
                }
                else //WEEKLY REPAYMENTS
                {
                    $principle_amount = round($loan->total_amount / $product->installments);
                    $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                    if ($settings->lp_fee){
                        $lp_fee = $settings->lp_fee / $product->installments;
                    }
                }
                $amountPayable = $principle_amount;
                $days = 0;

                if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                {
                    for ($i = 0; $i < $product->duration; $i++) {
                        $days = $days + 1;
                        $start_date_formatted = Carbon::parse($start_date)->subDays(1)->addDays($days);
                        if ($i == 0) {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->subDays(1)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                "being_paid" => true,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        } else {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->subDays(1)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        }
                    }
                } else {
                    for ($i = 0; $i < $product->installments; $i++) {
                        $days = $days + 7;
                        $start_date_formatted = Carbon::parse($start_date)->subDays(1)->addDays($days);
                        if ($i == 0) {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                "being_paid" => true,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        } else {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        }
                    }
                }

                $total_amount_paid = Payment::where('loan_id', $loan->id)->where('payment_type_id', 1)->sum('amount');

                //add the amount to current installment being paid
                $handle_installments = new MpesaPaymentController();
                $handle_installments->handle_installments($loan, $total_amount_paid);

                $loan->update(['total_amount_paid' => $total_amount_paid]);

                DB::table('reconsiliation_transactions')->where('transaction_id', $request->transaction_id)->delete();

                DB::commit();

                return response()->json('Payment Deleted');
            } catch (\Throwable $th) {
                info($th);
                DB::rollBack();
                return response()->json('Something went wrong', 500);
            }
        }
    }

    public function resolvePayments()
    {
        $settings = Setting::first();
        $reg_payments = Regpayment::where('amount', '>', $settings->registration_fee)->get();

        foreach ($reg_payments as $reg_payment) {
            $loan = Loan::where('customer_id', $reg_payment->customer_id)->where('approved', true)->where('settled', false)->first();

            if ($loan) {
                //Registration amount is more than set registration
                $remaining_reg = (int)$reg_payment->amount - (int)$settings->registration_fee;

                $reg_payment->update([
                    "amount" => (int)$settings->registration_fee,
                ]);

                $this->rem_after_reg($reg_payment, $loan->customer, $remaining_reg);
            }
        }
    }

    //remaider after reg
    public function rem_after_reg($reg_payment, $customer, $remaiderafter_reg)
    {
        $setting = Setting::first();

        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

        /********************if balance is less or equal to loan balance**************/
        if ((int)$remaiderafter_reg < $loan->balance) {
            //amount remaining is less than or equal to loan balance
            Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => $reg_payment->created_at->format('Y-m-d'),
                'transaction_id' => $reg_payment->transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
        } elseif ((int)$remaiderafter_reg == $loan->balance) {
            Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => $reg_payment->created_at->format('Y-m-d'),
                'transaction_id' => $reg_payment->transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
            Loan::find($loan->id)->update(['settled' => true]);
        } else {
            //amount remaining is greator than loan amount so put the remaining in reg fee account
            Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => $reg_payment->created_at->format('Y-m-d'),
                'transaction_id' => $reg_payment->transaction_id,
                'amount' => (int)$loan->balance,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);

            //set loan as paid
            Loan::find($loan->id)->update(['settled' => true]);

            $over_pay = $remaiderafter_reg - $loan->balance;

            $reg2 = Regpayment::where('customer_id', $customer->id)->first();

            $reg2->update([
                "amount" => $reg2->amount + $over_pay,
                "transaction_id" => $reg_payment->transaction_id,
            ]);
        }

        $this->handle_installments($loan, $remaiderafter_reg);
    }

    /*************************************handle installments***************************/
    public function handle_installments($loan, $rem)
    {
        $installment = Installment::where(['loan_id' => $loan->id, 'being_paid' => true])->first();
        $product = Product::where('id', $loan->product_id)->first();
        if ($installment->for_rollover){
            if ($loan->loan_type_id == 1) {
                $total_installments = $product->duration * 2;
            } else {
                $total_installments = $product->installments * 2;
            }
        } else {
            if ($loan->loan_type_id == 1){
                $total_installments = $product->duration;
            } else {
                $total_installments = $product->installments;
            }
        }

        //put into consideration restructured loans
        if ($loan->restructured == true){
            $total_installments = Installment::where('loan_id', '=', $loan->id)->count();
        }

        for ($i=$installment->position; $i<=$total_installments; $i++)
        {
            $instal = Installment::where(['position' => $i, 'loan_id' => $loan->id])->first();

            //additional check if installment exists
            if ($instal) {
                $insta_id = $instal->id;

                $balance = $instal->total - $instal->amount_paid;
                //$rem -= $balance;
                $rem2 = $rem - $balance;
                $arrear_id = null;

                if ($rem >= $balance){
                    //check installments is in arrears
                    if ($instal->in_arrear){
                        $arrear = Arrear::where(['installment_id' => $instal->id])->first();
                        if ($arrear){
                            $arrear_id = $arrear->id;
                            $arrear->delete();
                        }
                    }
                    if ($instal->interest_payment_date == null) {
                        $interest_payment_date = Carbon::now();
                    } else {
                        //  $interest_payment_date = $instal->interest_payment_date;
                        if ($instal->amount_paid >= $instal->interest){
                            $interest_payment_date = $instal->interest_payment_date;
                        } else {
                            $interest_payment_date = Carbon::now();
                        }
                    }

                    $instal->update([
                        'last_payment_date' => Carbon::now(),
                        'amount_paid' => $instal->total,
                        'in_arrear' => false,
                        'being_paid' => false,
                        'completed' => true,
                        'interest_payment_date' => $interest_payment_date
                    ]);

                    $next = Installment::where(['position' => $i + 1, 'loan_id' => $loan->id])->first();

                    if ($next){
                        $next->update([
                            'being_paid' => true
                        ]);
                    }

                    $rem -= $balance;

                    //delete pre interaction if active
                    try {
                        $this->handle_preinteraction($insta_id, $arrear_id);
                    } catch (Exception $e) {
                        info('some error happened on handling preinteraction');
                    }
                } elseif ($rem != 0 && $rem < $balance) {
                    if ($instal->in_arrear) {
                        $arrear = Arrear::where('installment_id', $instal->id)->first();
                        $arrear_id = $arrear->id;
                        //check if amount paid is greater than arrear
                        // $check = $arrear->amount - $rem;
                        $arrear->update([
                            'amount' => $arrear->amount - $rem
                        ]);
                    }
                    $rm = $instal->amount_paid + $rem;
                    // $interest_payment_date = Carbon::now();
                    $interest_payment_date = $instal->interest_payment_date;

                    if ($rm >= $instal->interest){
                        if ($instal->interest_payment_date == null){
                            $interest_payment_date = Carbon::now();
                        } else {
                            // if ($instal->amount_paid >= $instal->interest){
                            //     $interest_payment_date = $instal->interest_payment_date;
                            // }
                            // else{
                            //     $interest_payment_date = Carbon::now();
                            // }
                            $interest_payment_date = $instal->amount_paid >= $instal->interest ? $instal->interest_payment_date : Carbon::now();
                        }
                    }

                    $instal->update([
                        'last_payment_date' => Carbon::now(),
                        'amount_paid' => $instal->amount_paid + $rem,
                        'interest_payment_date' => $interest_payment_date
                    ]);
                    $rem -= $rem;

                    //check if loan has been completed
                    if ($loan->settled){
                        //delete pre interaction if active
                        try {
                            $this->handle_preinteraction($insta_id, $arrear_id);
                        } catch (Exception $e) {
                            info('some error happened on handling preinteraction on installment balance');
                        }
                    }
                }
            }
        }
    }

    public function handle_preinteraction($inst_id, $arrear_id)
    {
        $cat = CustomerInteractionCategory::where(['name' => 'Prepayment'])->first();
        $due_cat = CustomerInteractionCategory::where(['name' => 'Due Collection'])->first();

        $pre = Pre_interaction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])->whereDate('due_date', '>=', Carbon::now())->first();
        if ($pre){

            $pre->delete();
        }

        $cat1 = CustomerInteractionCategory::where(['name' => 'Arrear Collection'])->first();
        if ($arrear_id != null){
            $pre1 = Pre_interaction::where(['model_id' => $arrear_id, 'interaction_category_id' => $cat1->id])->first();
            if ($pre1){

                $pre1->delete();
            }
        }

        //check if we have any interaction under prepayment with this model id
        // $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])->first();
        $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat->id])
            ->orWhere(function ($query) use ($due_cat, $inst_id) {
                $query->where('model_id', $inst_id)->where('interaction_category_id', $due_cat->id);
            })
            ->first();
        if ($int){
            //check installment due date if has been passed
            $installment = Installment::where(['id' => $inst_id])->whereDate('due_date', '>=', Carbon::now())->first();
            if ($installment) {
                //Means it was paid on time so mark as a success
                $int->update(['target' => 1, 'closed_by' => 19, 'closed_date'=>Carbon::now(), 'status' => 2]);
            } else {
                $int->update(['target' => 2, 'closed_by' => 19, 'closed_date'=>Carbon::now(), 'status' => 2]);
            }
        }
        //check if we have any interaction under arrear payment with this model id
        if ($arrear_id != null) {
            $int = CustomerInteraction::where(['model_id' => $inst_id, 'interaction_category_id' => $cat1->id])->first();
            if ($int){
                //mark the interaction as success
                $int->update(['target' => 1, 'closed_by' => 19, 'closed_date'=>Carbon::now(), 'status' => 2]);
            }
        }
    }

    public function unapproveLoan($phone)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::where('phone', $phone)->first();

            if ($customer) {
                $loan = Loan::where('customer_id', $customer->id)->first();

                if ($loan) {
                    $loan->update([
                        'approved' => false,
                        'approved_by' => NULL,
                        'disbursed' => false,
                        'disbursed_by' => NULL,
                        'total_amount_paid' => 0
                    ]);

                    Installment::where('loan_id', $loan->id)->delete();
                    Payment::where('loan_id', $loan->id)->delete();
                    Arrear::where('loan_id', $loan->id)->delete();
                    Pre_interaction::where('customer_id', $customer->id)->delete();
                    Regpayment::where('customer_id', $customer->id)->delete();
                }
            }

            DB::commit();

            return response()->json('Loan updated');
        } catch (\Throwable $th) {
            info($th);
            DB::rollBack();

            return response()->json('Something went wrong', 500);
        }
    }

    public function removeAmount(Request $request)
    {
        $request->validate([
            'transaction_id' => ['required'],
            'amount' => ['required']
        ]);

        $payment = Payment::where('transaction_id', $request->transaction_id)->where('payment_type_id', 1)->first();

        if ($payment) {
            try {
                DB::beginTransaction();

                $payment->update([
                    'amount' => $payment->amount - $request->amount,
                ]);

                $due_date = Installment::where('loan_id', $payment->loan_id)->first()->due_date;

                $start_date = Carbon::parse($due_date);

                // Redo the installments with correct amount paid
                Installment::where('loan_id', $payment->loan_id)->delete();

                // Create Installments
                $loan = Loan::find($payment->loan_id);

                $settings = Setting::first();

                //create installments
                $product = Product::find($loan->product_id);

                $lp_fee = 0;

                if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                {
                    //break down loans to daily installments based on product duration
                    $principle_amount = round($loan->total_amount / $product->duration);
                    $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                    if ($settings->lp_fee){
                        $lp_fee = $settings->lp_fee / $product->duration;
                    }
                }
                else //WEEKLY REPAYMENTS
                {
                    $principle_amount = round($loan->total_amount / $product->installments);
                    $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                    if ($settings->lp_fee){
                        $lp_fee = $settings->lp_fee / $product->installments;
                    }
                }
                $amountPayable = $principle_amount;
                $days = 0;

                if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                {
                    for ($i = 0; $i < $product->duration; $i++) {
                        $days = $days + 1;
                        $start_date_formatted = Carbon::parse($start_date)->subDays(1)->addDays($days);
                        if ($i == 0) {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->subDays(1)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                "being_paid" => true,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        } else {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->subDays(1)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        }
                    }
                } else {
                    for ($i = 0; $i < $product->installments; $i++) {
                        $days = $days + 7;
                        $start_date_formatted = Carbon::parse($start_date)->subDays(1)->addDays($days);
                        if ($i == 0) {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                "being_paid" => true,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        } else {
                            Installment::create([
                                "loan_id" => $loan->id,
                                "principal_amount" => $principle_amount,
                                "total" => $amountPayable,
                                "interest" => $interest_payable,
                                "lp_fee" => $lp_fee,
                                "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        }
                    }
                }

                $total_amount_paid = Payment::where('loan_id', $loan->id)->where('payment_type_id', 1)->sum('amount');

                //add the amount to current installment being paid
                $handle_installments = new MpesaPaymentController();
                $handle_installments->handle_installments($loan, $total_amount_paid);

                $loan->update(['total_amount_paid' => $total_amount_paid]);

                DB::commit();

                return response()->json('Amount Deleted');
            } catch (\Throwable $th) {
                info($th);
                DB::rollBack();
                return response()->json('Something went wrong', 500);
            }
        }
    }

    public function removePayments(Request $request)
    {
        $request->validate([
            'start_date' => ['required']
        ]);

        $loans = Loan::whereDate('disbursement_date', Carbon::parse($request->start_date)->format('Y-m-d'))->where('settled', false)->where('total_amount_paid', '!=', 0)->get();

        // return response()->json($loans);

        foreach ($loans as $loan) {
            if ($loan) {
                try {
                    DB::beginTransaction();

                    $installment = Installment::where('loan_id', $loan->id)->first();

                    // Delete payment
                    Payment::where('loan_id', $loan->id)->where('payment_type_id', 1)->delete();

                    $start_date = Carbon::parse($installment->due_date);

                    // Redo the installments with correct amount paid
                    Installment::where('loan_id', $loan->id)->delete();

                    $settings = Setting::first();

                    //create installments
                    $product = Product::find($loan->product_id);

                    $lp_fee = 0;

                    if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                    {
                        //break down loans to daily installments based on product duration
                        $principle_amount = round($loan->total_amount / $product->duration);
                        $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                        if ($settings->lp_fee){
                            $lp_fee = $settings->lp_fee / $product->duration;
                        }
                    }
                    else //WEEKLY REPAYMENTS
                    {
                        $principle_amount = round($loan->total_amount / $product->installments);
                        $interest_payable = round(($loan->total_amount - $loan->loan_amount) / $product->installments);
                        if ($settings->lp_fee){
                            $lp_fee = $settings->lp_fee / $product->installments;
                        }
                    }
                    $amountPayable = $principle_amount;
                    $days = 0;

                    if ($loan->loan_type_id == 1) //DAILY REPAYMENTS
                    {
                        for ($i = 0; $i < $product->duration; $i++) {
                            $days = $days + 1;
                            $start_date_formatted = Carbon::parse($start_date)->subDays(1)->addDays($days);
                            if ($i == 0) {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->subDays(1)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                    "being_paid" => true,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            } else {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->subDays(1)->addDays($days) : Carbon::now()->addDays(1)->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            }
                        }
                    } else {
                        for ($i = 0; $i < $product->installments; $i++) {
                            $days = $days + 7;
                            $start_date_formatted = Carbon::parse($start_date)->subDays(1)->addDays($days);
                            if ($i == 0) {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                    "being_paid" => true,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            } else {
                                Installment::create([
                                    "loan_id" => $loan->id,
                                    "principal_amount" => $principle_amount,
                                    "total" => $amountPayable,
                                    "interest" => $interest_payable,
                                    "lp_fee" => $lp_fee,
                                    "due_date" => $start_date ? Carbon::parse($start_date)->addDays($days) : Carbon::now()->addDays($days),
                                    "start_date" => $start_date ? Carbon::parse($start_date) : Carbon::now(),
                                    "current" => $start_date_formatted->equalTo(now()->format('Y-m-d')) ? true : false,
                                    "amount_paid" => 0,
                                    "position" => $i + 1
                                ]);
                            }
                        }
                    }

                    $total_amount_paid = Payment::where('loan_id', $loan->id)->where('payment_type_id', 1)->sum('amount');

                    // //add the amount to current installment being paid
                    // $handle_installments = new MpesaPaymentController();
                    // $handle_installments->handle_installments($loan, $total_amount_paid);

                    $loan->update(['total_amount_paid' => $total_amount_paid]);

                    // DB::table('reconsiliation_transactions')->where('customer_id', $loan->customer_id)->delete();

                    DB::commit();
                } catch (\Throwable $th) {
                    info($th);
                    DB::rollBack();
                    return response()->json('Something went wrong', 500);
                }
            }
        }

        return response()->json('Loans updated');

        // $customers = Customer::whereIn('id', $loans)->pluck('phone');

        // return response()->json($customers);
    }

    public function resolveApplicationFee(Request $request)
    {
        set_time_limit(30000);

        $request->validate([
            'due_date' => ['required']
        ]);

        $setting = Setting::first();

        $loans = Loan::whereDate('disbursement_date', Carbon::parse($request->due_date)->subDays(2)->format('Y-m-d'))->where('approved', true)->where('disbursed', true)->where('settled', false)->pluck('customer_id');

        // Get customers who have previous loans
        $customers = Customer::withCount('loans')->whereIn('id', $loans)->get()->filter(fn ($customer) => $customer->loans_count > 1);

        foreach ($customers as $customer) {
            // Get registration fee payment
            $reg_payment = Regpayment::where('customer_id', $customer->id)->first();

            // Get latest loan
            $loan = Loan::where('customer_id', $customer->id)->latest()->first();

            // Get application fee payment
            $fee_payment = Payment::where('loan_id', $loan->id)->where('payment_type_id', 3)->first();
            $loan_payment = Payment::where('transaction_id', $fee_payment->transaction_id)->where('payment_type_id', 1)->first();

            if ($fee_payment) {
                if ($setting->loan_processing_fee >= $fee_payment->amount) {
                    Payment::where('loan_id', $loan->id)->update(['payment_type_id' => 1]);
                    Payment::create([
                        'loan_id' => $loan->id,
                        'date_payed' => $reg_payment->date_payed,
                        'amount' => 1000,
                        'channel' => 'MPESA',
                        'payment_type_id' => 3,
                        'transaction_id' => $reg_payment->transaction_id
                    ]);
                }
            } else {
                Payment::create([
                    'loan_id' => $loan->id,
                    'date_payed' => $reg_payment->date_payed,
                    'amount' => 1000,
                    'channel' => 'MPESA',
                    'payment_type_id' => 3,
                    'transaction_id' => $reg_payment->transaction_id
                ]);
            }

            $total_amount_paid = Payment::where('loan_id', $loan->id)->where('payment_type_id', 1)->sum('amount');

            $loan->update([
                'total_amount_paid' => $total_amount_paid
            ]);

            Installment::where('loan_id', $loan->id)->update([
                'amount_paid' => 0,
                'completed' => false,
                'in_arrear' => false,
                'being_paid' => false,
            ]);

            Installment::where('loan_id', $loan->id)->first()->update([
                'being_paid' => true,
            ]);

            $this->handle_installments($loan, $total_amount_paid);
        }

        return response()->json($customers);
    }

    public function resolveEndDates(Request $request)
    {
        $loans = Loan::whereDate('disbursement_date', $request->disb_date)->get();

        foreach ($loans as $loan) {
            $loan->update([
                'end_date' => Carbon::parse($loan->end_date)->subDays(1)
            ]);
        }

        return response()->json('Loans updated');
    }

    public function postApproveMultiple(Request $request, $id)
    {
        $loanId = decrypt($id);

        $loan = Loan::findOrFail($loanId);

        $loan->source = "main";
        $loan->save();

        return redirect()->route('loans.index')->with('success', 'Loan Accepted');
    }



}
