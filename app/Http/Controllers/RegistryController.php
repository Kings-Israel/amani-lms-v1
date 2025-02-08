<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\Branch;
use App\models\Guarantor;
use App\models\PrequalifiedAmountAdjustment;
use App\models\Referee;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\models\Product;
use App\models\LoanType;
use Illuminate\Support\Facades\Auth;
use App\models\Customer;
use App\models\Customer_location;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\models\Loan;
use App\models\Payment;
use App\models\Raw_payment;
use App\models\Regpayment;
use App\models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Imports\CustomersImport;
use Maatwebsite\Excel\Facades\Excel;

class RegistryController extends Controller
{
    public function index()
    {
        $title = "Customers";
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

        return view("pages.registry.customer.index", [
            'title' => $title,
            'sub_title' => $sub_title,
            'branches' => $branches,
            'lfs' => $lfs,
            'check_role' => $check_role
        ]);
    }

    public function blocked()
    {
        $title = "Blocked Customers";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $branches = Branch::query()->where('status', '=', true)->get();
            $lfs = User::role('field_agent')->where('status', true)->get();
            $sub_title = "List of All blocked registered Customers";
            $check_role = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant');
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $branches = Branch::where('id', '=', $branch->id)->get();
            $lfs = User::role('field_agent')->where('status', true)->where('branch_id', '=', $branch->id)->get();
            $sub_title = "List of All blocked registered Customers in " . $branch->bname;
            $check_role = false;
        }

        return view("pages.registry.customer.blocked", [
            'title' => $title,
            'sub_title' => $sub_title,
            'branches' => $branches,
            'lfs' => $lfs,
            'check_role' => $check_role
        ]);
    }

    public function pending()
    {
        $title = "Customers without Registration Fee Payment";
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $branches = Branch::query()->where('status', '=', true)->get();
            $lfs = User::role('field_agent')->where('status', true)->get();
            $sub_title = "List of All registered Customers without registration fee payment";
            $check_role = Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant');
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $branches = Branch::where('id', '=', $branch->id)->get();
            $lfs = User::role('field_agent')->where('status', true)->where('branch_id', '=', $branch->id)->get();
            $sub_title = "List of All registered Customers in " . $branch->bname . " without registration fee payment";
            $check_role = false;
        }

        return view("pages.registry.customer.pending", [
            'title' => $title,
            'sub_title' => $sub_title,
            'branches' => $branches,
            'lfs' => $lfs,
            'check_role' => $check_role
        ]);
    }

    public function create()
    {
        if (!Auth::user()->hasRole(['admin', 'accountant', 'field_agent', 'customer_informant', 'sector_manager'])) {
            return back()->with('error', 'Unauthorized');
        }

        $data['title'] = "Add New Customer";

        return view("pages.registry.customer.register", $data);
    }

    public function registryImports()
    {
        $users = User::all();
        return view('pages.registry.customer.import', compact('users'));
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'relationship_officer' => 'required|exists:users,id',
        ]);

        $import = new CustomersImport($request->relationship_officer);

        Excel::import($import, $request->file('file'));

        if (!empty($import->errors)) {
            session()->flash('error', 'Some records were not inserted due to errors.');
            session()->flash('errorDetails', $import->errors);
        } else {
            session()->flash('success', "Customers imported successfully. Total inserted: {$import->insertedCount}");
        }

        return redirect()->back();
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer_type' => ['required'],
            'customer_title' => ['required'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'relationship_officer' => ['required'],
            'supervisor' => ['required'],
            'supervisor' => ['required'],
            'mobile_line' => ['required'],
            'identity_number' => ['required'],
            'country' => ['required'],
            'county' => ['required'],
            'constituency' => ['required'],
            'ward' => ['required'],
            'loan_amount' => ['required'],
            'product_id' => ['required'],
            'installments' => ['required'],
            'loan_type' => ['required'],
            'loan_applications_number' => ['min:0'],
        ], [
            'product_id.required' => 'Select a loan product',
            'installments.required' => 'Select a loan product',
            'loan_type.required' => 'Select a loan repayment type',
            'loan_amount.required' => 'Select a loan amount',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $phone_number = '254' . substr($request->mobile_line, -9);

        $customer = Customer::where('phone', $phone_number)->first();

        if ($customer) {
            return response()->json(['customer' => 'The customer already exists in the system'], 422);
        }

        // personal
        $customer = [
            "type" => $request->customer_type,
            "title" => $request->customer_title,
            "fname" => $request->first_name,
            "mname" => $request->middle_name,
            "lname" => $request->last_name,
            "field_agent_id" => $request->relationship_officer['value'],
            "supervisor_id" => $request->supervisor['value'],
            "supervisor" => $request->supervisor['value'],
            "guarantor_id" => $request->guarantor['value'] ?? null,
            "phone" => '254' . substr($request->mobile_line, -9),//changed this line remove issues when doing loan payment
            "email" => $request->email,
            "document_id" => 1,
            "id_no" => $request->identity_number,
            "branch_id" => User::find($request->relationship_officer['value'])->branch_id ?? Auth::user()->branch_id,
            "prequalified_amount" => $request->loan_amount['label'],
            "alternate_phone" => '254' . substr($request->alternate_mobile_line, -9),
            "times_loan_applied" => $request->has('loan_applications_number') ? $request->loan_applications_number : 0,
            // "business_type_id" => $request->business_type != '' ? $request->business_type['value'] : NULL,
            "business_type_id" => isset($request->business_type['value']) ? $request->business_type['value'] : NULL,

        ];

        $customerLocation = [
            "country" => $request->country,
            "county_id" => $request->county['value'],
            "constituency" => $request->constituency,
            "ward" => $request->ward,
        ];

        $referee_ids = [];
        if (count($request->referees) > 0) {
            foreach ($request->referees as $referee) {
                $full_name = $referee['full_name'];
                $id_number = $referee['id_number'];
                $phone_number = '254' . substr($referee['phone_number'], -9);
                $created_referee = Referee::query()->updateOrCreate([
                    "id_number" => $id_number, "phone_number" => $phone_number
                ], ["full_name" => $full_name]
                );
                $referee_ids[] = $created_referee->id;
            }
        }

        DB::transaction(function () use ($customer, $customerLocation, $referee_ids, $request) {
            $customer = Customer::create($customer);

            if (!empty($referee_ids)) {
                $customer->referees()->sync($referee_ids);
            }

            $customer->location()->create($customerLocation);

            if ($request->has('product_id') && $request->has('loan_type') && $request->has('installments') && $request->has('loan_amount') && !empty($request->product_id) && !empty($request->loan_type) && !empty($request->installments) && !empty($request->loan_amount)) {
                $product = Product::find($request->product_id['value']);
                $enddate = Carbon::now()->addDays($product->duration);

                $loan_type = LoanType::find($request->loan_type['value']);

                $branch = Branch::find(User::find($request->relationship_officer['value'])->branch_id ?? Auth::user()->branch_id);
                if ($request->product_id['value'] == 6) {
                    // Deduct Interest
                    $loan_amount = floor($request->loan_amount['label'] / ((int) $product->interest + 100)) * 100;
                } else {
                    $loan_amount = $request->loan_amount['label'] - ($request->loan_amount['label'] * ($product->interest / 100));
                }

                $create_loan = Loan::create([
                    'loan_amount' => $loan_amount,
                    'total_amount' => $request->loan_amount['label'],
                    'product_id' => $product->id,
                    'customer_id' => $customer->id,
                    'loan_type_id' => $loan_type->id,
                    'date_created' => Carbon::now('Africa/Nairobi'),
                    'purpose' => $request->purpose ? $request->purpose['value'] : 'Business Expense',
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
            }

            $raw_payment = Raw_payment::where('account_number', $customer->phone)->first();

            if ($raw_payment) {
                $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

                if ($loan) {
                    $setting = Setting::first();

                    if ($raw_payment->amount <= (int) $setting->registration_fee) {
                        Regpayment::create([
                            'customer_id' => $customer->id,
                            'date_payed' => Carbon::now('Africa/Nairobi'),
                            "amount" => $raw_payment->amount,
                            "transaction_id" => $raw_payment->mpesaReceiptNumber,
                            "channel" => 'MPESA',
                        ]);
                    } else {
                        //more amount than registration
                        $remaining_reg = (int)$raw_payment->amount - (int)$setting->registration_fee;
                        Regpayment::create([
                            'customer_id' => $customer->id,
                            'date_payed' => Carbon::now('Africa/Nairobi'),
                            "amount" => (int) $setting->registration_fee,
                            "transaction_id" => $raw_payment->mpesaReceiptNumber,
                            "channel" => 'MPESA',
                        ]);

                        //remaider after reg
                        $remaiderafter_reg = $remaining_reg;
                        $accounts = new AccountsController();
                        $accounts->rem_after_reg($raw_payment->mpesaReceiptNumber, $customer, $remaiderafter_reg);
                    }
                } else {
                    //meaning he has no active loan so check if registration fee is paid
                    $reg = Regpayment::where('customer_id', $customer->id)->first();
                    if ($reg) {
                        $reg->update([
                            'date_payed' => Carbon::now('Africa/Nairobi'),
                            "amount" => $raw_payment->amount + $reg->amount,
                            "transaction_id" => $raw_payment->mpesaReceiptNumber,
                        ]);
                    } else {
                        Regpayment::create([
                            'customer_id' => $customer->id,
                            'date_payed' => Carbon::now('Africa/Nairobi'),
                            "amount" => $raw_payment->amount,
                            "transaction_id" => $raw_payment->mpesaReceiptNumber,
                            "channel" => 'MPESA',
                        ]);
                    }
                }

                $raw_payment->delete();
            }
        });

        return ["message" => "successful"];
    }

    // public function show($id)
    // {
    //     $customer = Customer::query()->with('customer_document', 'loans.payment')->findOrFail(decrypt($id));

    //     $data['customer'] = $customer;

    //     $data['title'] = $customer->fullNameUpper;
    //     $data['sub_title'] = "Customer details";
    //     $data['customer_id'] = encrypt($customer->id);

    //     return view("pages.registry.customer.show", $data);
    // }

    public function show($id)
    {
        $customer = Customer::query()
            ->with('customer_document', 'loans.payment')
            ->findOrFail(decrypt($id));

        $data['customer'] = $customer;
        $data['title'] = $customer->fullNameUpper;
        $data['sub_title'] = "Customer details";
        $data['customer_id'] = encrypt($customer->id);

        $totalAmount = 0;
        $paidAmount = 0;
        $balance = 0;
        $principalAmount = 0;
        $interestAmount = 0;

        foreach ($customer->loans as $loan) {
            $totalAmount += $loan->getTotalAttribute();
            $paidAmount += $loan->getAmountPaidAttribute();
            $balance += $loan->getBalanceAttribute();
            $principalAmount += $loan->loan_amount;

            $interestAmount += $loan->rolled_over
                ? $loan->loan_amount * ($loan->product()->first()->interest / 100) + $loan->rollover()->first()->rollover_interest
                : $loan->loan_amount * ($loan->product()->first()->interest / 100);
        }

        $registrationFees = $customer->regpayments()->sum('amount');

        $data['totalAmount'] = $totalAmount;
        $data['paidAmount'] = $paidAmount;
        $data['balance'] = $balance;
        $data['principalAmount'] = $principalAmount;
        $data['interestAmount'] = $interestAmount;
        $data['registrationFees'] = $registrationFees;

        if ($customer->customer_document) {
            $data['profilePhoto'] = $customer->customer_document->profile_photo_path
                ? Storage::url($customer->customer_document->profile_photo_path)
                : null;
            $data['idFront'] = $customer->customer_document->id_front_path
                ? Storage::url($customer->customer_document->id_front_path)
                : null;
            $data['idBack'] = $customer->customer_document->id_back_path
                ? Storage::url($customer->customer_document->id_back_path)
                : null;
        } else {
            $data['profilePhoto'] = null;
            $data['idFront'] = null;
            $data['idBack'] = null;
        }

        return view("pages.registry.customer.show", $data);
    }



    public function edit(string $id)
    {
        //disable LO from editing customer details
         if (Auth::user()->hasRole(['admin', 'agent_care', 'customer_informant', 'sector_manager'])) {

            $customer = Customer::query()->find(decrypt($id));
            if ($customer) {
                $data['customer'] = $customer;
                $data['title'] = $customer->fullNameUpper;
                $data['sub_title'] = "Edit Customer Details";
                return view("pages.registry.customer.edit", $data);
            } else {
                return Redirect::back()->with('error', 'Customer Not Found.');
            }
        } else {
            return Redirect::back()->with('error', 'Unauthorized, contact admin for assistance.');
        }
    }

    public function edits(int $id)
    {
        // Disable LO from editing customer details for certain roles
        if (Auth::user()->hasRole(['admin', 'agent_care', 'customer_informant', 'sector_manager'])) {

            // Find the customer directly by ID without decryption
            $customer = Customer::find($id);

            if ($customer) {
                $data['customer'] = $customer;
                $data['title'] = $customer->fullNameUpper;
                $data['sub_title'] = "Edit Customer Details";

                return view("pages.registry.customer.edit", $data);
            } else {
                return Redirect::back()->with('error', 'Customer Not Found.');
            }
        } else {
            return Redirect::back()->with('error', 'Unauthorized, contact admin for assistance.');
        }
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasRole(['admin', 'agent_care', 'customer_informant', 'sector_manager'])) {
            return ["message" => "Unauthorized"];
        }

        $validator = Validator::make($request->all(), [
            'customer_type' => ['required'],
            'customer_title' => ['required'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'relationship_officer' => ['required'],
            'mobile_line' => ['required'],
            'identity_number' => ['required'],
            'country' => ['required'],
            'county' => ['required'],
            'constituency' => ['required'],
            'ward' => ['required'],
            'loan_applications_number' => ['min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $customer = Customer::find($id);

        if (isset($request->prequalified_amount['label'])) {
            $request_prequalified_amount = $request->prequalified_amount['label'];
        } else {
            $request_prequalified_amount = $request->prequalified_amount;
        }
        if (Auth::user()->hasRole(['admin', 'agent_care', 'customer_informant', 'sector_manager'])) {
            //create prequalified loan amount request
            if ($request_prequalified_amount != null) {
                PrequalifiedAmountAdjustment::create([
                    'customer_id' => $customer->id,
                    'initial_amount' => $customer->prequalified_amount,
                    'proposed_amount' => $request_prequalified_amount,
                    'status' => true,
                    'initiated_by' => Auth::id(),
                    'approved_by' => Auth::id(),
                    'approved_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            $prequalified_amount = $request_prequalified_amount ?? $customer->prequalified_amount; //admin can update
        } else {
            if ($request_prequalified_amount != null) {
                PrequalifiedAmountAdjustment::create([
                    'customer_id' => $customer->id,
                    'initial_amount' => $customer->prequalified_amount,
                    'proposed_amount' => $request_prequalified_amount,
                    'initiated_by' => Auth::id(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            //create prequalified loan amount request

            $prequalified_amount = $customer->prequalified_amount; //remains as it was
        }

        $customerData = [
            "type" => $request->customer_type,
            "title" => $request->customer_title,
            "fname" => $request->first_name,
            "mname" => $request->middle_name,
            "lname" => $request->last_name,
            "field_agent_id" => $request->relationship_officer['value'] ?? $customer->field_agent_id,
            "phone" => '254' . substr($request->mobile_line, -9),//changed this line remove issues when doing loan payment
            "email" => $request->email,
            "document_id" => $request->identity_type['value'] ?? $customer->document_id,
            "branch_id" => User::find($request->relationship_officer['value'] ?? $customer->field_agent_id)->branch_id ?? Auth::user()->branch_id,
            "prequalified_amount" => $prequalified_amount,
            "alternate_phone" => '254' . substr($request->alternate_mobile_line, -9),
            "times_loan_applied" => $request->has('loan_applications_number') ? $request->loan_applications_number : 0,
            "business_type_id" => $request->business_type != '' ? $request->business_type['value'] : NULL,
        ];

        $customerLocationData = [
            "country" => $request->country,
            "county_id" => $request->county['value'] ?? $customer->location->county_id,
            "constituency" => $request->constituency['value'] ?? $customer->location->constituency,
            "ward" => $request->ward['value'] ?? $customer->location->ward,
        ];

        $referee_ids = [];
        if (count($request->referees) > 0) {
            foreach ($request->referees as $referee) {
                $full_name = $referee['full_name'];
                $id_number = $referee['id_number'];
                $phone_number = '254' . substr($referee['phone_number'], -9);
                $created_referee = Referee::query()->updateOrCreate([
                    "id_number" => $id_number, "phone_number" => $phone_number
                ], ["full_name" => $full_name]
                );
                $referee_ids[] = $created_referee->id;
            }
        }

        DB::transaction(function () use ($customer, $customerData, $customerLocationData, $referee_ids) {
            $customer->update($customerData);
            if (!empty($referee_ids)) {
                $customer->referees()->sync($referee_ids);
            }
            $customer->location()->update($customerLocationData);
        });

        return ["message" => "successful"];
    }

    /**
     * Block customer (change status to 0).
     *
     */
    public function block($id)
    {
        $customer = Customer::find(decrypt($id));
        $customer->update(['status' => 0]);
        return $customer;
    }

    /**
     * Unblock customer (change status to 1).
     *
     */
    public function unblock($id)
    {
        $customer = Customer::find(decrypt($id));
        $customer->update(['status' => 1]);
        return $customer;
    }

    public function ajaxData(Request $request)
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');

        $customers = Customer::with(['loans' => function ($query) {
            $query->where('approved', true);
        }])
        ->when($request->has('registration_fee_paid') && $request->registration_fee_paid != 'all', function ($query) use ($request) {
            if ($request->registration_fee_paid == 1) {
                $query->whereHas('regpayments');
            } else {
                $query->whereDoesntHave('regpayments');
            }
        })
        ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
            $query->whereDate('created_at', Carbon::parse($request->date));
        })
        ->when($request->has('classification') && $request->classification != 'all', function ($query) use ($request) {
            $classification = $request->classification;
            $query->where('classification', $classification);
        })
        ->select('*')
        ->whereIn('branch_id', $activeBranches)
        ->orderBy('id', 'asc');

        if ($request->lf != 'all') {
            $customers = $customers->where('field_agent_id', $request->lf);
        } elseif ($request->branch != 'all') {
            $customers = $customers->where('branch_id', $request->branch);
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $customers = Customer::when($request->has('registration_fee_paid') && $request->registration_fee_paid != 'all', function ($query) use ($request) {
                if ($request->registration_fee_paid == 1) {
                    $query->whereHas('regpayments');
                } else {
                    $query->whereDoesntHave('regpayments');
                }
            })
            ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                $query->whereDate('created_at', Carbon::parse($request->date));
            })
            ->select('*')
            ->whereIn('branch_id', $activeBranches);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $customers = $customers->where('field_agent_id', Auth::id());
        } else {
            $customers = $customers->where('branch_id', Auth::user()->branch_id);
        }

        return DataTables::eloquent($customers)
            ->addColumn('branchName', function (Customer $customer) {
                return $customer->branch ? $customer->branch->bname : '';
            })
            ->addColumn('businessType', function (Customer $customer) {
                return $customer->business_type_id ? $customer->businessType->bname : '';
            })
            ->addColumn('loanOfficer', function (Customer $customer) {
                if ($customer->Officer) {
                    return $customer->Officer->name;
                } else {
                    return 'No officer assigned';
                }
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
            ->addColumn('regPayment', function (Customer $customer) {
                $setting = Setting::first();
                $reg_payment = $customer->regpayments()->sum('amount') >= $setting->registration_fee ? true : false;
                if ($reg_payment) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
            ->addColumn('activeLoanAmount', function (Customer $customer) {
                $loan_amount = $customer->loans()->where('approved', true)->where('disbursed', true)->orderBy('created_at', 'DESC')->first();
                if ($loan_amount) {
                    return $loan_amount->loan_amount;
                } else {
                    return '-';
                }
            })
            ->addColumn('activeLoanDisbursementDate', function (Customer $customer) {
                $loan = $customer->loans()->where('approved', true)->where('disbursed', true)->orderBy('created_at', 'DESC')->first();
                if ($loan) {
                    return $loan->disbursement_date;
                } else {
                    return '-';
                }
            })
            ->addColumn('loansApplied', function (Customer $customer) {
                return $customer->times_loan_applied;
            })
            ->addColumn('createdDate', function (Customer $customer) {
                $dateTime = Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('location', function (Customer $customer) {
                $location = Customer_location::where('customer_id', $customer->id)->first();

                return Str::title($location->ward).', '.Str::title($location->constituency).', '.Str::title($location->county->cname);
            })
            ->addColumn('referee', function (Customer $customer) {
                $customer_referee = DB::table('customer_referee')->where('customer_id', $customer->id)->first();

                if ($customer_referee) {
                    $referee = Referee::find($customer_referee->referee_id);

                    return Str::headline($referee->full_name).' ('.$referee->phone_number.')';
                }
                return '--';
            })
            ->addColumn('action', function (Customer $customer) {
                $html = '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="feather icon-settings"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" x-placement="top-end" style="position: absolute; transform: translate3d(87px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                            <a class="dropdown-item" href="' . route('registry.show', encrypt($customer->id)) . '"><i class="feather icon-eye"></i> View</a>
                            <a class="dropdown-item" href="' . route('registry.edit', encrypt($customer->id)) . '"><i class="feather icon-edit"></i> Edit</a>
                            <a class="dropdown-item" href="' . route('list_customer_thread', encrypt($customer->id)) . '"><i class="feather icon-clock"></i> View History</a>
                            <a class="dropdown-item" href="' . route('customer-interactions.list_customer_interactions', $customer->id) . '"><i class="feather icon-clock"></i> View Interactions</a>
                            <a class="dropdown-item" href="' . route('registry.classification', $customer->id) . '"><i class="feather icon-camera"></i> Update Classification</a>
                            ';
                if ($customer->customer_document()->exists()) {
                    $customer->load('customer_document');
                    $customer_document = $customer->customer_document;
                    $html .= '<a class="dropdown-item" href="' . route('customer-documents.edit', encrypt($customer_document->id)) . '"><i class="feather icon-camera"></i> Update Customer Photos</a>';
                } else {
                    $html .= '<a class="dropdown-item" href="' . route('customer-documents.create', encrypt($customer->id)) . '"><i class="feather icon-camera"></i> Upload Customer Documents</a>';
                    // $html .= '<a class="dropdown-item" href="' . route('customer-documents.create', encrypt($customer->id)) . '"><i class="feather icon-camera"></i> Upload Customer Photos</a>';
                }
                if ($customer->status) {
                    // active
                    $html .= '<a onclick="event.preventDefault()" data-fname="' . $customer->fname . '" data-lname="' . $customer->lname . '" data-customerid="' . encrypt($customer->id) . '" data-toggle="modal" data-target="#confirm-modal" class="dropdown-item" href="#"><i class="feather icon-slash"></i> Block</a>
                        </div>';
                } else {
                    // blocked
                    $html .= '<a onclick="event.preventDefault()" data-fname="' . $customer->fname . '" data-lname="' . $customer->lname . '" data-customerid="' . encrypt($customer->id) . '" data-toggle="modal" data-target="#unblock-confirm-modal" class="dropdown-item" href="#"><i class="feather icon-slash"></i> Unblock</a>
                        </div>';
                }

                return $html;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function blockedCustomers(Request $request)
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');

        $customers = Customer::with(['loans' => function ($query) {
                $query->where('approved', true);
            }])
            ->when($request->has('registration_fee_paid') && $request->registration_fee_paid != 'all', function ($query) use ($request) {
                if ($request->registration_fee_paid == 1) {
                    $query->whereHas('regpayments');
                } else {
                    $query->whereDoesntHave('regpayments');
                }
            })
            ->when($request->has('date') && $request->date != '', function ($query) use ($request) {
                $query->whereDate('created_at', Carbon::parse($request->date));
            })
            ->when($request->has('classification') && $request->classification != 'all', function ($query) use ($request) {
                $classification = $request->classification;
                $query->where('classification', $classification);
            })
            // Filter by branch
            ->when($request->has('branch_id') && $request->branch_id != 'all', function ($query) use ($request) {
                $query->where('branch_id', $request->branch_id);
            })
            // Filter by field agent
            ->when($request->has('field_agent_id') && $request->field_agent_id != 'all', function ($query) use ($request) {
                $query->where('field_agent_id', $request->field_agent_id);
            })
            ->select('*')
            ->whereIn('branch_id', $activeBranches)
            ->where('status', 0)
            ->orderBy('id', 'asc');

        return DataTables::eloquent($customers)
            ->addColumn('branchName', function (Customer $customer) {
                return $customer->branch ? $customer->branch->bname : '';
            })
            ->addColumn('businessType', function (Customer $customer) {
                return $customer->business_type_id ? $customer->businessType->bname : '';
            })
            ->addColumn('loanOfficer', function (Customer $customer) {
                if ($customer->Officer) {
                    return $customer->Officer->name;
                } else {
                    return 'No officer assigned';
                }
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
            ->addColumn('regPayment', function (Customer $customer) {
                $setting = Setting::first();
                $reg_payment = $customer->regpayments()->sum('amount') >= $setting->registration_fee ? true : false;
                return $reg_payment ? 'Yes' : 'No';
            })
            ->addColumn('activeLoanAmount', function (Customer $customer) {
                $loan_amount = $customer->loans()->where('approved', true)->where('disbursed', true)->orderBy('created_at', 'DESC')->first();
                return $loan_amount ? $loan_amount->loan_amount : '-';
            })
            ->addColumn('activeLoanDisbursementDate', function (Customer $customer) {
                $loan = $customer->loans()->where('approved', true)->where('disbursed', true)->orderBy('created_at', 'DESC')->first();
                return $loan ? $loan->disbursement_date : '-';
            })
            ->addColumn('loansApplied', function (Customer $customer) {
                return $customer->times_loan_applied;
            })
            ->addColumn('createdDate', function (Customer $customer) {
                $dateTime = Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('location', function (Customer $customer) {
                $location = Customer_location::where('customer_id', $customer->id)->first();
                return Str::title($location->ward) . ', ' . Str::title($location->constituency) . ', ' . Str::title($location->county->cname);
            })
            ->addColumn('referee', function (Customer $customer) {
                $customer_referee = DB::table('customer_referee')->where('customer_id', $customer->id)->first();
                if ($customer_referee) {
                    $referee = Referee::find($customer_referee->referee_id);
                    return Str::headline($referee->full_name) . ' (' . $referee->phone_number . ')';
                }
                return '--';
            })
            ->addColumn('action', function (Customer $customer) {
                $html = '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="feather icon-settings"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="' . route('registry.show', encrypt($customer->id)) . '"><i class="feather icon-eye"></i> View</a>
                            <a class="dropdown-item" href="' . route('registry.edit', encrypt($customer->id)) . '"><i class="feather icon-edit"></i> Edit</a>
                            ';
                $html .= '<a onclick="event.preventDefault()" data-fname="' . $customer->fname . '" data-lname="' . $customer->lname . '" data-customerid="' . encrypt($customer->id) . '" data-toggle="modal" data-target="#unblock-confirm-modal" class="dropdown-item" href="#"><i class="feather icon-slash"></i> Unblock</a></div>';

                return $html;
            })
            ->rawColumns(['action'])
            ->toJson();
    }



    public function classification($id)
    {
        $customer = Customer::findOrFail($id);
        $classifications = ['Good', 'Defaulter', 'Security Impounded', 'Cleared Share'];

        return view('pages.registry.customer.classification', compact('customer', 'classifications'));
    }

    public function updateClassification(Request $request, $id)
    {
        $request->validate([
            'classification' => 'required|string|in:Good,Defaulter,Security Impounded,Cleared Share',
        ]);

        $customer = Customer::findOrFail($id);

        if ($request->classification == 'Defaulter') {
            $customer->classification = $request->classification;
            $customer->status = 0;
        } else {
            $customer->classification = $request->classification;
        }

        $customer->save();

        return redirect()->route('registry.index')->with('success', 'Classification updated successfully!');
    }


    public function ajaxPendingData(Request $request)
    {
        $activeBranches = Branch::query()->where('status', '=', true)->pluck('id');

        $customers = Customer::with('loans')
                    ->whereIn('branch_id', $activeBranches)
                    ->whereDoesntHave('regpayments');

        if ($request->lf != 'all') {
            $customers = $customers->where('field_agent_id', $request->lf);
        } elseif ($request->branch != 'all') {
            $customers = $customers->where('branch_id', $request->branch);
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('collection_officer') || Auth::user()->hasRole('customer_informant')) {
            $customers = Customer::select('*')->whereDoesntHave('regpayments')->whereIn('branch_id', $activeBranches);
        } elseif (Auth::user()->hasRole('field_agent')) {
            $customers = $customers->where('field_agent_id', Auth::id());
        } else {
            $customers = $customers->where('branch_id', Auth::user()->branch_id);
        }

        if ($request->date != '') {
            $customers->whereDate('created_at', Carbon::parse($request->date));
        }

        return DataTables::eloquent($customers)
            ->addColumn('branchName', function (Customer $customer) {
                return $customer->branch ? $customer->branch->bname : '';
            })
            ->addColumn('loanOfficer', function (Customer $customer) {
                if ($customer->Officer) {
                    return $customer->Officer->name;
                } else {
                    return 'No officer assigned';
                }
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
            ->addColumn('regPayment', function (Customer $customer) {
                $setting = Setting::first();
                $reg_payment = $customer->regpayments()->sum('amount') >= $setting->registration_fee ? true : false;
                if ($reg_payment) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
            ->addColumn('activeLoanAmount', function (Customer $customer) {
                $loan_amount = $customer->loans()->where('approved', true)->where('disbursed', true)->orderBy('created_at', 'DESC')->first();
                if ($loan_amount) {
                    return $loan_amount->loan_amount;
                } else {
                    return '-';
                }
            })
            ->addColumn('activeLoanDisbursementDate', function (Customer $customer) {
                $loan = $customer->loans()->where('approved', true)->where('disbursed', true)->orderBy('created_at', 'DESC')->first();
                if ($loan) {
                    return $loan->disbursement_date;
                } else {
                    return '-';
                }
            })
            ->addColumn('createdDate', function (Customer $customer) {
                $dateTime = Carbon::parse($customer->created_at);
                return sprintf("%s/%s/%s", $dateTime->day, $dateTime->month, $dateTime->year);
            })
            ->addColumn('location', function (Customer $customer) {
                $location = Customer_location::where('customer_id', $customer->id)->first();

                return Str::title($location->ward).', '.Str::title($location->constituency).', '.Str::title($location->county->cname);
            })
            ->addColumn('referee', function (Customer $customer) {
                $customer_referee = DB::table('customer_referee')->where('customer_id', $customer->id)->first();

                $referee = Referee::find($customer_referee->referee_id);

                return Str::headline($referee->full_name).' ('.$referee->phone_number.')';
            })
            ->addColumn('action', function (Customer $customer) {
                $html = '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="feather icon-settings"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" x-placement="top-end" style="position: absolute; transform: translate3d(87px, -2px, 0px); top: 0px; left: 0px; will-change: transform;">
                            <a class="dropdown-item" href="' . route('registry.show', encrypt($customer->id)) . '"><i class="feather icon-eye"></i> View</a>
                            <a class="dropdown-item" href="' . route('registry.edit', encrypt($customer->id)) . '"><i class="feather icon-edit"></i> Edit</a>
                            <a class="dropdown-item" href="' . route('list_customer_thread', encrypt($customer->id)) . '"><i class="feather icon-clock"></i> View History</a>
                            <a class="dropdown-item" href="' . route('customer-interactions.list_customer_interactions', $customer->id) . '"><i class="feather icon-clock"></i> View Interactions</a>
                            ';
                if ($customer->customer_document()->exists()) {
                    $customer->load('customer_document');
                    $customer_document = $customer->customer_document;
                    $html .= '<a class="dropdown-item" href="' . route('customer-documents.edit', encrypt($customer_document->id)) . '"><i class="feather icon-camera"></i> Update Customer Photos</a>';
                } else {
                    $html .= '<a class="dropdown-item" href="' . route('customer-documents.create', encrypt($customer->id)) . '"><i class="feather icon-camera"></i> Upload Customer Photos</a>';
                }
                if ($customer->status) {
                    // active
                    $html .= '<a onclick="event.preventDefault()" data-fname="' . $customer->fname . '" data-lname="' . $customer->lname . '" data-customerid="' . encrypt($customer->id) . '" data-toggle="modal" data-target="#confirm-modal" class="dropdown-item" href="#"><i class="feather icon-slash"></i> Block</a>
                        </div>';
                } else {
                    // blocked
                    $html .= '<a onclick="event.preventDefault()" data-fname="' . $customer->fname . '" data-lname="' . $customer->lname . '" data-customerid="' . encrypt($customer->id) . '" data-toggle="modal" data-target="#unblock-confirm-modal" class="dropdown-item" href="#"><i class="feather icon-slash"></i> Unblock</a>
                        </div>';
                }

                return $html;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function relationshipOfficers()
    {
        //some update
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            return User::role('field_agent')->where('status', '=', true)->select(['id AS value', 'name AS label'])->get();
        } elseif (Auth::user()->hasRole('field_agent')) {
            return User::where('id', Auth::id())->select(['id AS value', 'name AS label'])->get();
        } else {
            return User::role('field_agent')->where('status', '=', true)->select(['id AS value', 'name AS label'])->get();
        }
    }

    public function supervisors()
    {
        //some update
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('agent_care') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            return User::role('supervisor')->where('status', '=', true)->select(['id AS value', 'name AS label'])->get();
        } elseif (Auth::user()->hasRole('field_agent')) {
            return User::role('supervisor')->where('status', '=', true)->select(['id AS value', 'name AS label'])->get();
        } else {
            return User::role('supervisor')->where('status', '=', true)->select(['id AS value', 'name AS label'])->get();
        }
    }

    public function idTypes()
    {

        return \App\models\Document::select(['id AS value', 'dname AS label'])->get();
    }

    public function kinRelations()
    {

        return \App\models\Relationship::select(['id AS value', 'rname AS label'])->get();
    }

    public function guarantors()
    {
        return DB::table('guarantors')->select(['id AS value', 'gname AS label', 'gphone', 'gid'])->get();
    }

    public function counties()
    {

        return DB::table('counties')->select(['id AS value', 'cname AS label'])->get();
    }

    public function industries()
    {

        return DB::table('industries')->select(['id AS value', 'iname AS label'])->get();
    }

    public function incomeRanges()
    {

        return DB::table('income_ranges')->select(['id AS value', 'name AS label'])->get();
    }

    public function prequalifiedAmount()
    {
        return DB::table('prequalified_loans')->select(['id AS value', 'amount AS label'])->get();
    }

    public function businessTypes()
    {
        return DB::table('business_types')
            ->select(['id AS value', 'bname AS label'])->get();
    }

    public function loanProducts()
    {
        return DB::table('products')
            ->select(['id AS value', 'product_name AS label', 'installments', 'interest'])->get();
    }

    public function loanTypes()
    {
        return DB::table('loan_types')
            ->select(['id AS value', 'name AS label'])->get();
    }

    public function accounts()
    {
        return DB::table('accounts')->select(['id AS value', 'aname AS label'])->get();
    }

    public function customerPersonalDetails($id)
    {
        $customer = Customer::with('referees')->find($id);

        $referees = $customer->referees;
        if (empty($referees)) {
            $referees = [
                ['full_name' => '', 'id_number' => '', 'phone_number' => '']
            ];
        } else {
            foreach ($referees as $referee) {
                $referee->phone_number = '0'.substr($referee->phone_number, -9);
            }
        }

        $data['customer_type'] = $customer->type;
        $data['customer_title'] = $customer->title;
        $data['first_name'] = $customer->fname;
        $data['middle_name'] = $customer->mname;
        $data['last_name'] = $customer->lname;
        $data['relationship_officer'] = $customer->Officer->name;
        $data['tax_pin'] = $customer->tax_pin;
        $data['gender'] = $customer->gender;
        $data['date_of_birth'] = $customer->dob;
        $data['mobile_line'] = '0'.substr($customer->phone, -9);
        $data['email'] = $customer->email;
        $data['identity_type'] = $customer->idDocument->dname;
        $data['identity_number'] = $customer->id_no;
        $data['prequalified_amount'] = $customer->prequalified_amount;
        $data['alternate_mobile_line'] = $customer->alternate_phone;
        $data['loan_applications_number'] = $customer->times_loan_applied;
        $data['referees'] = $referees->toArray();

        return $data;
    }

    public function customerLocationDetails($id)
    {
        $customer = Customer::find($id);

        $data['country'] = $customer->location->country;
        $data['county'] = $customer->location->county->cname;
        $data['constituency'] = $customer->location->constituency;
        $data['ward'] = $customer->location->ward;

        return $data;
    }

    public function customerProfessionDetails($id)
    {
        $customer = Customer::find($id);

        $data['industry_type'] = $customer->industry->iname;
        $data['business_type'] = $customer->businessType->bname;
        $data['is_employed'] = $customer->is_employed;
        $data['employment_status'] = $customer->employment_status;
        $data['employer'] = $customer->employer;
        $data['date_of_employment'] = $customer->employment_date;
        $data['income_range'] = $customer->incomeRange->name;

        return $data;
    }

    public function customerAccountDetails($id)
    {
        $customer = Customer::find($id);

        $data['savings_product'] = $customer->accountType->aname;

        return $data;
    }

    public function uniquePhoneNumber($phone_number)
    {
        $not_unique = Customer::where('phone', $phone_number)->exists();

        return response()->json(!$not_unique);
    }

    public function uniqueIdNumber($id_number)
    {
        $not_unique = Customer::where('id_no', $id_number)->exists();

        return response()->json(!$not_unique);
    }

    /************send sms form*******************/
    public function customers_sms(Request $request)
    {
        $this->data['title'] = "Send SMS to LITSA CREDIT Customers ";


        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant') || Auth::user()->hasRole('sector_manager') || Auth::user()->hasRole('customer_informant')) {
            $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
            $this->data['lfs'] = User::role('field_agent')->where(['status' => true])->get();
        } else {
            abort('403');
        }

        return view('pages.registry.customer.sms', $this->data);
    }

    public function customers_sms_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'lf' => 'required',
            'message' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Message could not be sent. Ensure all fields are filled');
        }

        if ($request->branch_id == 'all' and $request->lf == 'all') {
            $customers = Customer::all();
            $selection = 'All Customers.';
        } elseif ($request->branch_id != 'all' and $request->lf == 'all') {
            $branch = Branch::find($request->branch_id);
            $customers = Customer::where('branch_id', '=', $branch->id)->get();
            $selection = $branch->bname . ' customers';
        } elseif ($request->lf != 'all' and $request->branch_id == 'all') {
            $lf = User::find($request->lf);
            $customers = Customer::where('field_agent_id', '=', $lf->id)->get();
            $selection = ' customers under ' . $lf->name;
        } elseif ($request->lf != 'all' and $request->branch_id != 'all') {
            $branch = Branch::find($request->branch_id);
            $lf = User::find($request->lf);
            $customers = Customer::where(['field_agent_id' => $lf->id, 'branch_id' => $branch->id])->get();
            $selection = $branch->bname . ' customers under ' . $lf->name;
        } else {
            $customers = [];
        }

        if (count($customers) == 0) {
            return back()->withInput()->with('error', 'No customers have been found that match your filtered criteria, please check and try again.');
        } else {
            foreach ($customers as $customer) {
                $amessage = "Dear " . $customer->fname . ', ' . "\r\n"
                    . $request->message;
                $aphone = '+254' . substr($customer->phone, -9);
                $auser = $customer;
                $suser_type = false;
                $fnd = dispatch(new Sms(
                    $aphone, $amessage, $auser, $suser_type
                ));
            }
            return back()->withInput()->with('success', 'SMSes have been queued and will be sent to ' . $selection);
        }
    }

    public function single_customer_sms(Request $request)
    {
        $this->data['title'] = "Send SMS to Specific Customer ";
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['lfs'] = User::role('field_agent')->where(['status' => true])->get();

        return view('pages.registry.customer.sms_single_customer', $this->data);
    }

    public function single_customer_sms_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leader_id' => 'required',
            'message' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Message could not be sent. Ensure all fields are filled');
        }

        $customer = Customer::find($request->leader_id);

        if (!$customer) {
            return back()->withInput()->with('error', 'No customers has been found that match your filtered criteria, please check and try again.');
        } else {
            $amessage = "Dear " . $customer->fname . ', ' . "\r\n"
                . $request->message;
            $aphone = '+254' . substr($customer->phone, -9);
            $auser = $customer;
            $suser_type = false;
            $fnd = dispatch(new Sms(
                $aphone, $amessage, $auser, $suser_type
            ));
            return back()->withInput()->with('success', 'SMS has been queued and will be sent to ' . $customer->fname . ' ' . $customer->lname);
        }
    }

    /************change CO****************/
    public function getCreditOfficers()
    {
        $this->data['title'] = "Select Credit Officer.";
        $this->data['sub_title'] = "Select the Credit Officer whose customers are to be shifted.";
        return view('pages.registry.customer.change_credit_officer', $this->data);
    }

    public function changeCOData(Request $request)
    {
        $lo = User::role('field_agent')->get(['id', 'name', 'phone', 'branch_id'])->each->setAppends(['branch']);
        return DataTables::of($lo)
            ->addColumn('count', function ($lo) {
                $data = $lo->id;
                return Customer::where('field_agent_id', '=', $data)->count();
            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;
                return ' <a class="btn btn-primary" href="' . route('selectedCO', $data) . '"><i class="feather icon-eye"></i> Select </a>
                          ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function selectedCO($id)
    {
        $user = User::find($id);
        if ($user) {
            $customers = Customer::where('field_agent_id', $user->id)->count();
            if ($customers == 0) {
                return back()->with('warning', 'The Credit Officer ' . $user->name . ' has 0 customers under his portfolio.');
            }
            $this->data['title'] = "Change " . $user->name . "'s Customers to another Credit Officer ";
            $this->data['customers'] = $customers;
            $this->data['user'] = $user;
            $this->data['lfs'] = User::role('field_agent')->where('status', true)->where('id', '!=', $user->id)->get()->each->setAppends([]);;
            return view('pages.registry.customer.change_credit_officer_selected', $this->data);
        } else {
            return back()->with('warning', 'Credit Officer not found.');
        }
    }

    public function changeCOCustomerData($credOfficer)
    {
        $customers = Customer::select('*')->where('field_agent_id', $credOfficer);
        return DataTables::eloquent($customers)
            ->addColumn('checkbox', function (Customer $customer) {
                return '<input type="checkbox" name="id[]" value="' . $customer->id . '" id="' . $customer->id . '">';
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
            ->rawColumns(['checkbox'])
            ->toJson();
    }

    public function post_update_co(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'old_co' => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Message could not be sent. Ensure all fields are filled');
        }
        $newCO = User::find($request->get('name'));
        $oldCO = User::find($request->get('old_co'));
        if (!$newCO and !$oldCO) {
            return back()->with('warning', 'No Credit Officer has been selected.');
        }
        $selectAll = $request->get('select_all');
        $custIDs = $request->get('id');
        //individual customers selected
        if (isset($custIDs) and $selectAll == null) {
            foreach ($custIDs as $cust) {
                $customer = Customer::find($cust);
                $customer->update([
                    'field_agent_id' => $newCO->id,
                    'updated_at' => Carbon::now()
                ]);
            }
            return redirect()->route('registry.changeCreditOfficer')->with('success', count($custIDs) . ' customers have been removed from ' . $oldCO->name . "'s portfolio and added to " . $newCO->name . "'s portfolio.");
        } //select all selected
        elseif (!isset($custIDs) and $selectAll == "on") {
            $customers = Customer::where('field_agent_id', '=', $oldCO->id)->get();
            foreach ($customers as $customer) {
                $customer->update([
                    'field_agent_id' => $newCO->id,
                    'updated_at' => Carbon::now()
                ]);
            }
            return redirect()->route('registry.changeCreditOfficer')->with('success', 'All ' . count($customers) . ' customers have been removed from ' . $oldCO->name . "'s portfolio and added to " . $newCO->name . "'s portfolio.");
        } else {
            return redirect()->back()->with('warning', "Request Failed. You do not have to mark individual customers' and mark the 'Select all' checkboxes at once. Kindly pick one option.");
        }

    }

    /*****************LOAN PREQUALIFIED AMOUNT ADJUSTMENT*********************/
    public function preq_amt_adjustment()
    {
        $this->data['title'] = "Loan Prequalified Amount Adjustments";
        $this->data['sub_title'] = "List of All customer loan prequalified amount adjustments";
        return view("pages.registry.customer.preq_amt_adjustment", $this->data);
    }

    public function approve_preq_amt_adjustment($id)
    {
        $req = PrequalifiedAmountAdjustment::find($id);
        if (!$req) {
            return back()->with('error', 'Request not found!');
        }
        if ($req->status == true) {
            return back()->with('warning', 'Request has already been approved');
        }
        $customer = Customer::find($req->customer_id);
        $customer->update(['prequalified_amount' => $req->proposed_amount]);
        $req->update([
            'status' => true,
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return back()->with('success', $customer->fname . " " . $customer->lname . "'s prequalified loan amount has been updated to Ksh." . $customer->prequalified_amount);
    }

    public function preq_amt_adjustment_data()
    {
        $lo = PrequalifiedAmountAdjustment::all();
        return Datatables::of($lo)
            ->addColumn('customer', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                return $customer->fname . ' ' . $customer->lname;
            })
            ->addColumn('phone', function ($lo) {
                $customer = Customer::find($lo->customer_id);
                return $customer->phone;
            })
            ->addColumn('branch', function ($lo) {
                $branch = Branch::find(Customer::find($lo->customer_id)->branch_id);
                return $branch->bname;
            })
            ->editColumn('status', function ($lo) {
                if ($lo->status == false) {
                    return '<h6><span class="badge badge-danger"><b>Pending</b></span></h6>';
                } else {
                    return '<h6><span class="badge badge-success"><b>Approved</b></span></h6>';
                }
            })
            ->editColumn('approved_by', function ($lo) {
                if ($lo->approved_by != null) {
                    $user = User::find($lo->approved_by);
                    return $user->name;
                } else {
                    return '--';
                }
            })
            ->editColumn('initiated_by', function ($lo) {
                if ($lo->initiated_by != null) {
                    $user = User::find($lo->initiated_by);
                    return $user->name;
                } else {
                    return '--';
                }
            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;
                return '<a class="sel-btn btn btn-xs btn-primary"  href="' . route('approve_preq_amt_adjustment', ['id' => $data]) . '"><i class="feather icon-edit text-warning"></i> Approve</a>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function customer_location($customer_identifier)
    {
        try {
            $id = decrypt($customer_identifier);
            $customer = Customer::query()->with('location')->find($id);
            if ($customer) {
                $map_locations = [];
                if ($customer->location) {
                    $location = $customer->location;

                    $home = [
                        'name' => 'Home Physical Address',
                        'address' => $location->physical_address,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ];
                    $business = [
                        'name' => 'Business Physical Address',
                        'address' => $location->business_address,
                        'latitude' => $location->business_latitude,
                        'longitude' => $location->business_longitude,
                    ];
                    if ($location->latitude != null) {
                        $map_locations[] = $home;
                    }
                    if ($location->business_latitude != null) {
                        $map_locations[] = $business;
                    }
                }
                return view('pages.registry.customer.location-details', compact('customer', 'map_locations'));
            } else {
                return Redirect::back()->with('warning', 'Invalid Customer Identifier');
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return Redirect::back()->with('error', 'Sorry, something went wrong on our side, please try again later.');
        }
    }

    public function update_customer_location(Request $request, $customer_identifier)
    {
        try {
            $validator = Validator::make($request->all(), [
                'postal_address' => 'required',
                'postal_code' => 'required',
                'physical_address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'business_address' => 'required',
                'business_latitude' => 'required',
                'business_longitude' => 'required',
                'residence_type' => 'required',
                'years_lived' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
            }
            $id = decrypt($customer_identifier);
            $customer = Customer::query()->with('location')->find($id);
            if ($customer) {
                $customerLocationData = [
                    "postal_address" => $request->input('postal_address'),
                    "postal_code" => $request->input('postal_code'),
                    "physical_address" => $request->input('physical_address'),
                    "business_longitude" => $request->input('business_longitude'),
                    "business_latitude" => $request->input('business_latitude'),
                    "business_address" => $request->input('business_address'),
                    "longitude" => $request->input('longitude'),
                    "latitude" => $request->input('latitude'),
                    "residence_type" => $request->input('residence_type'),
                    "years_lived" => $request->input('years_lived'),
                ];
                $customer->location()->update($customerLocationData);

                return Redirect::back()->with('success', 'Customer Location details have been updated successfully');
            } else {
                return Redirect::back()->with('warning', 'Invalid Customer Identifier');
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return Redirect::back()->with('error', 'Sorry, something went wrong on our side, please try again later.');
        }
    }
}
