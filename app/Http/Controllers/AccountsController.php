<?php

namespace App\Http\Controllers;

use App\Jobs\Settlement_disburse;
use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\Expense;
use App\models\Expense_type;
use App\models\Installment;
use App\models\Loan;
use App\models\Msetting;
use App\models\Payment;
use App\models\Product;
use App\models\Regpayment;
use App\models\Setting;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use App\models\Raw_payment;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\Sms;


class AccountsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|accountant|agent_care|customer_informant|sector_manager|manager');
        // Alternativly
    }

    public function ro_salary_settlement()
    {
        $this->data['title'] = "Salaries settlement";

        return view('pages.accounts.operations.ro_salary_settlement', $this->data);
    }

    //ro salaries data
    public function ro_salary_settlement_data()
    {
        $Ro = User::whereHas('roles', function ($query) {
            return $query->where([['name', '!=', 'admin'], ['name', '!=', 'investor']]);
        })
        ->whereDoesntHave('user_payments', function ($q) {
                return $q->whereMonth('date_payed', '=', Carbon::now());
        })->get();

        return DataTables::of($Ro)
            ->editColumn('role', function ($Ro) {
                return $Ro->roles()->first()->name;
            })
            ->addColumn('action', function ($Ro) {
                return '<a href="' . route('loans.post_disburse', ['id' => encrypt($Ro->id)]) . '"    class="sel-btn btn btn-xs btn-primary"><i class="feather icon-eye text-info"></i> PAY</a>';
            })
            ->addColumn('checkbox', function ($Ro) {
                return '<input type="checkbox" name="id[]" value="' . encrypt($Ro->id) . '" >';
            })
            ->rawColumns(['action', 'checkbox'])
            ->toJson();
    }

    //others settlement
    public function other_settlement()
    {
        $etype = Expense_type::all();
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant')) {
            $branch = Branch::query()->where('status', '=', true)->get();
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
        }

        $this->data['title'] = "Other settlement";
        $this->data['etype'] = $etype;
        $this->data['branches'] = $branch;

        return view('pages.accounts.operations.other_settlement', $this->data);
    }

    public function other_settlement_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'branch_id' => 'required|exists:branches,id',
            'expense_type_id' => 'required|exists:expense_types,id',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $request->merge([
            'date_payed' => Carbon::now(),
            'paid_by' => Auth::user()->id,
        ]);

        $expense = Expense::create($request->input());

        return back()->with('success', 'Successfully added expense');
    }

    //pay multiple salary
    public function post_disburse_salary_disable(Request $request)
    {
        abort(403);

        return 0;

        /*******************************************************connect with the MPesa B2C api************************************************/
        $environment = env("MPESA_ENV");

        if ($environment == "live") {
            $token = self::generateLiveToken();
        } elseif ($environment == "sandbox") {
            $token = self::generateSandBoxToken();
        } else {
            return json_encode(["Message" => "invalid application status"]);
        }

        $environment = env("MPESA_ENV");

        if ($environment == "live") {
            $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
           /* $phone = '254' . substr($this->user->phone, -9); ;*/
            $msetting = Msetting::first();
            $InitiatorName = decrypt($msetting->InitiatorName);
            $PartyA = decrypt($msetting->paybill);
            $SecurityCredential = decrypt($msetting->SecurityCredential);
           // $PartyB = $phone;
            $token = self::generateLiveToken();
        } elseif ($environment == "sandbox") {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';

            $phone = "254708374149";
            $InitiatorName = "testapi0321";
            // $SecurityCredential = self::setSecurityCredentials();
            $SecurityCredential = "Yb63VpkEaLbrtJaR3IhElVENIcJ9k1k7PaMa+T91wBymrzNHK4zllt5cNSn/Pgz89ZqHUbU1EIqrVlKiffBQlR8YAgvJFsfDN0VdzWtPu/0YpVFfbWBKkPj+mBqUgVZ01X4goa0vGi7YjaysniMG5b1zvMFN2rhJr03A0TN1pK0+c9/k3pJzG0Vpur0/gK/dmHjrVo2wZbaTdtU0FmRza3NMNpf/B2u0HT7mXsZkWsnr9C1Rqo+6aJX2Dd7LUkSpkebAC010sGvrEaa3SCMXgc6TOCIZjJPgg9jzT70tqQ5SZ84HPjdKl9/MxGhS7n+lyAkFFlQGxSJH380QRfmRyA==";

            // $PartyA = "600506";
            $PartyA = "600321";
            $PartyB = "254708374149";
            $token = self::generateSandBoxToken();
        }

        $CommandID = "BusinessPayment";
        $Remarks = 'some info';
        //$Remarks = "Disperse loan to " . $customer->fullname;
        $QueueTimeOutURL = route('settlement_timeout');
        $ResultURL = route('settlement_result');
        $Occasion = 'Settlement Dispersal';
        $requestedby = Auth::user()->id;

        foreach ($request->id as $id) {
            $loan = User::find(decrypt($id));
            $amount = $loan->salary;
            if ($environment == "live"){
                $phone = '254' . substr($loan->phone, -9);
                $PartyB = $phone;
            }

            $fnd = dispatch(new Settlement_disburse(
                $token, $loan->id, $amount, $url, $InitiatorName, $SecurityCredential, $CommandID, $PartyA, $PartyB, $Remarks,
                $QueueTimeOutURL, $ResultURL, $Occasion,$requestedby
            ));
        }

        if ($fnd) {
            return back()->with('success', 'Successfully requested the payment. We will inform you when done');
        } else {
            return back()->with('error', 'We seem to be having a problem. Please try again later');
        }
    }


    public static function generateLiveToken()
    {
        $settings = Msetting::first();

        $consumer_key = decrypt($settings->Consumer_Key);
        $consumer_secret = decrypt($settings->Consumer_Secret);


        if (!isset($consumer_key) || !isset($consumer_secret)) {
            die("please declare the consumer key and consumer secret as defined in the documentation");
        }
        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';


        if (!isset($consumer_key) || !isset($consumer_secret)) {
            die("please declare the consumer key and consumer secret as defined in the documentation");
        }

        $client = new Client();
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);

        $res = $client->request('get', $url, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
            ]
        ]);

        //$res2 = $res->getBody()->getContents();
        $res2 = (string)$res->getBody();
        $obj = json_decode((string)$res->getBody());
        $token = $obj->access_token;
        return $token;
    }

    /**
     * use this function to generate a sandbox token
     * @return mixed
     */
    public static function generateSandBoxToken()
    {
        $consumer_key = 'G7bIMYpKOqhJYnuRmj8fRvhdVuAybrF7';
        $consumer_secret = 'myjxz2uKOlGXNkvb';

        if (!isset($consumer_key) || !isset($consumer_secret)) {
            die("please declare the consumer key and consumer secret as defined in the documentation");
        }

        $client = new Client();
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);

        //$credentials2 = base64_encode('G7bIMYpKOqhJYnuRmj8fRvhdVuAybrF7:myjxz2uKOlGXNkvb');

        $res = $client->request('get', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
            ]
        ]);
        $res2 = (string)$res->getBody();
        $obj = json_decode((string)$res->getBody());
        $token = $obj->access_token;
        return $token;
    }

    /******************************finish mpesa transaction*****************/
    public function finishTransaction($status = true)
    {
        if ($status === true) {
            $resultArray = [
                "ResultDesc" => "Confirmation Service request accepted successfully",
                "ResultCode" => "0"
            ];
        } else {
            $resultArray = [
                "ResultDesc" => "Confirmation Service not accepted",
                "ResultCode" => "1"
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($resultArray);
    }

    //investor with drawals
    public function investors_withdrawal()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant')) {
            $investors = User::role(['investor'])->get();
            $this->data['title'] = "Investor Withdrawals";
            $branches = Branch::query()->where('status', '=', true)->get();
        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $investors = User::role(['investor'])->where('branch_id', $branch->id)->get();
            $this->data['title'] = "Investor Withdrawals in ";
            $branches = $branch;
        }
        $this->data['investors'] = $investors;
        $this->data['branches'] = $branches;

        return view('pages.accounts.operations.investors_withdrawal', $this->data);

    }

    /************************investor interest settlement*******************/
    public function investors_interest()
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant')) {
            $investors = User::role(['investor'])->get();
            $this->data['title'] = "Investor Interest Settlement";
            $branches = Branch::query()->where('status', '=', true)->get();


        } else {
            $branch = Branch::find(Auth::user()->branch_id);
            $investors = User::role(['investor'])->where('branch_id', $branch->id)->get();
            $this->data['title'] = "Investor Withdrawals in ";
            $branches = $branch;
        }

        $this->data['investors'] = $investors;
        $this->data['branches'] = $branches;

        return view('pages.accounts.operations.investors_interest', $this->data);
    }

    /*************************************reconcile*******************************************/
    public function reconcile()
    {
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['title'] = "Transaction Reconciliation";
        $this->data['customers'] = Customer::all();
        if (Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant')) {
            $this->data['customers'] = Customer::all();
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $customers = $branch->customers()->get();
            $this->data['customers'] = $customers;
        }

        return view('pages.admin.reconcile', $this->data);
    }

    public function reconcile_bulk_post()
    {
        $this->data['branches'] = Branch::query()->where('status', '=', true)->get();
        $this->data['title'] = "Bulk Transactions Reconciliation";
        $this->data['customers'] = Customer::all();
        if (Auth::user()->hasRole('admin') or Auth::user()->hasRole('accountant')) {
            $this->data['customers'] = Customer::all();
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $customers = $branch->customers()->get();
            $this->data['customers'] = $customers;
        }

        return view('pages.admin.bulk_reconcile', $this->data);
    }


    public function reconcile_bulk_data(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls',
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file);

        $totalRecords = 0;
        $updatedRecords = 0;
        $nonExistingCustomers = 0;
        $alreadyFoundRecords = 0;
        $parsingErrors = 0;
        $typeErrors = 0;

        foreach ($data[0] as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $totalRecords++;

            $transaction_id = $row[0];
            $completion_time = $row[1];
            $details = $row[2];
            $amount = $row[3];
            $phone_number = $row[4];

            $normalized_phone_number = '254' . $phone_number;

            try {
                if (!is_numeric($completion_time)) {
                    throw new \TypeError("Invalid date format");
                }
                $date_payed = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($completion_time)->format('Y-m-d H:i:s');
            } catch (\TypeError $e) {
                $typeErrors++;
                continue;
            } catch (\Exception $e) {
                $parsingErrors++;
                continue;
            }

            $existing_transaction = DB::table('reconsiliation_transactions')
                ->where('transaction_id', $transaction_id)
                ->first();

            if ($existing_transaction) {
                $alreadyFoundRecords++;
                continue;
            }

            $customer = Customer::where('phone', $normalized_phone_number)->first();

            if ($customer) {
                $request_data = new Request([
                    'transaction_id' => $transaction_id,
                    'customer_id' => $customer->id,
                    'amount' => $amount,
                    'date_payed' => $date_payed,
                    'channel' => 'MPESA',
                ]);

                $this->reconcile_post($request_data);
                $updatedRecords++;
            } else {
                $nonExistingCustomers++;
            }
        }

        $errorMessages = [];
        if ($parsingErrors > 0) {
            $errorMessages[] = 'There was an issue parsing the date in some records. Please check the date format.';
        }
        if ($typeErrors > 0) {
            $errorMessages[] = 'There was a type error in some records. Please ensure all numeric values are properly formatted.';
        }

        $summary = [
            'total_records' => $totalRecords,
            'updated_records' => $updatedRecords,
            'found_records' => $alreadyFoundRecords,
            'non_existing_customers' => $nonExistingCustomers,
            'parsing_errors' => $parsingErrors,
            'type_errors' => $typeErrors,
            'error_messages' => $errorMessages,
        ];

        return back()->with('success', 'Bulk reconciliation completed successfully')
            ->with('summary', $summary);
    }

    public function reconcile_post(Request $request)
    {
        // Validate input
        $request->validate([
            'transaction_id' => ['required'],
            'channel' => ['required'],
            'date_payed' => ['required'],
            'amount' => ['required'],
        ]);

        // Check for duplicate transaction
        $reg = Regpayment::where('transaction_id', $request->transaction_id)->first();
        $pay = Payment::where('transaction_id', $request->transaction_id)->first();
        $rec = DB::table('reconsiliation_transactions')->where(['transaction_id' => $request->transaction_id])->first();

        if ($rec || $reg || $pay) {
            // Remove duplicate raw payment if exists
            $raw_payment = Raw_payment::where('mpesaReceiptNumber', Str::upper($request->transaction_id))->first();
            if ($raw_payment) {
                $raw_payment->delete();
            }
            return back()->with('error', 'This transaction has already been added in the system')->withInput();
        }

        // Find customer
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return back()->with('error', 'No Customer found')->withInput();
        }

        // Add transaction to reconciliation table
        DB::table('reconsiliation_transactions')->insert([
            'customer_id' => $request->customer_id,
            'reconsiled_by' => \auth()->id(),
            'amount' => $request->amount,
            'transaction_id' => $request->transaction_id,
            'phone_number' => $customer->phone,
            'channel' => $request->channel,
            'date_paid' => $request->date_payed,
            'created_at' => Carbon::now(),
        ]);

        // Remove raw payment if exists
        $raw_payment = Raw_payment::where('mpesaReceiptNumber', Str::upper($request->transaction_id))->first();
        if ($raw_payment) {
            $raw_payment->delete();
        }

        // Check if customer has an active loan
        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();
        if ($loan) {
            // Check for registration payment
            $reg = Regpayment::where('customer_id', $customer->id)->first();
            $setting = Setting::first();

            if ($reg) {
                // Case 1: Registration amount is more than set registration fee
                if ($reg->amount > $setting->registration_fee) {
                    $remaining_reg = (int)$reg->amount - (int)$setting->registration_fee;
                    $reg->update([
                        'date_payed' => Carbon::now('Africa/Nairobi'),
                        'amount' => (int)$setting->registration_fee,
                        'transaction_id' => $request->transaction_id,
                    ]);
                    $remainder_after_reg = (int)$request->amount + $remaining_reg;

                    // Apply remaining amount after registration fee
                    if ($remainder_after_reg < $loan->balance) {
                        Payment::create([
                            'loan_id' => $loan->id,
                            'date_payed' => $request->date_payed,
                            'transaction_id' => $request->transaction_id,
                            'amount' => $remainder_after_reg,
                            'channel' => $request->channel,
                            'payment_type_id' => 1,
                        ]);
                    } else {
                        $over_pay = $remainder_after_reg - $loan->balance;
                        Payment::create([
                            'loan_id' => $loan->id,
                            'date_payed' => $request->date_payed,
                            'transaction_id' => $request->transaction_id,
                            'amount' => $loan->balance,
                            'channel' => $request->channel,
                            'payment_type_id' => 1,
                        ]);
                        $loan->update(['settled' => true]);

                        $reg->update([
                            'amount' => $reg->amount + $over_pay,
                            'transaction_id' => $request->transaction_id,
                        ]);
                    }
                } elseif ($reg->amount == $setting->registration_fee) {
                    // Case 2: Registration amount equals the set registration fee
                    if ($request->amount <= $loan->balance) {
                        Payment::create([
                            'loan_id' => $loan->id,
                            'date_payed' => $request->date_payed,
                            'transaction_id' => $request->transaction_id,
                            'amount' => $request->amount,
                            'channel' => $request->channel,
                            'payment_type_id' => 1,
                        ]);
                    } else {
                        $over_pay = $request->amount - $loan->balance;
                        Payment::create([
                            'loan_id' => $loan->id,
                            'date_payed' => $request->date_payed,
                            'transaction_id' => $request->transaction_id,
                            'amount' => $loan->balance,
                            'channel' => $request->channel,
                            'payment_type_id' => 1,
                        ]);
                        $loan->update(['settled' => true]);

                        $reg->update([
                            'amount' => $reg->amount + $over_pay,
                            'transaction_id' => $request->transaction_id,
                        ]);
                    }
                } else {
                    // Case 3: Registration amount is less than the set registration fee
                    $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;
                    if ($request->amount <= $remaining_reg) {
                        $reg->update([
                            'date_payed' => Carbon::now('Africa/Nairobi'),
                            'amount' => (int)$reg->amount + $request->amount,
                            'transaction_id' => $request->transaction_id,
                        ]);
                    } else {
                        $reg->update([
                            'date_payed' => Carbon::now('Africa/Nairobi'),
                            'amount' => (int)$setting->registration_fee,
                            'transaction_id' => $request->transaction_id,
                        ]);
                        $remainder_after_reg = (int)$request->amount - (int)$remaining_reg;

                        if ($remainder_after_reg < $loan->balance) {
                            Payment::create([
                                'loan_id' => $loan->id,
                                'date_payed' => $request->date_payed,
                                'transaction_id' => $request->transaction_id,
                                'amount' => $remainder_after_reg,
                                'channel' => $request->channel,
                                'payment_type_id' => 1,
                            ]);
                        } else {
                            $over_pay = $remainder_after_reg - $loan->balance;
                            Payment::create([
                                'loan_id' => $loan->id,
                                'date_payed' => $request->date_payed,
                                'transaction_id' => $request->transaction_id,
                                'amount' => $loan->balance,
                                'channel' => $request->channel,
                                'payment_type_id' => 1,
                            ]);
                            $loan->update(['settled' => true]);

                            $reg->update([
                                'amount' => $reg->amount + $over_pay,
                                'transaction_id' => $request->transaction_id,
                            ]);
                        }
                    }
                }
            } else {
                // Case 4: No registration payment found
                if ($request->amount <= (int)$setting->registration_fee) {
                    Regpayment::create([
                        'customer_id' => $customer->id,
                        'date_payed' => $request->date_payed,
                        'amount' => $request->amount,
                        'transaction_id' => $request->transaction_id,
                        'channel' => $request->channel,
                    ]);
                } else {
                    $remaining_reg = (int)$request->amount - (int)$setting->registration_fee;
                    Regpayment::create([
                        'customer_id' => $customer->id,
                        'date_payed' => $request->date_payed,
                        'amount' => (int)$setting->registration_fee,
                        'transaction_id' => $request->transaction_id,
                        'channel' => $request->channel,
                    ]);
                    // Apply the remaining amount after registration fee
                    if ($remaining_reg < $loan->balance) {
                        Payment::create([
                            'loan_id' => $loan->id,
                            'date_payed' => $request->date_payed,
                            'transaction_id' => $request->transaction_id,
                            'amount' => $remaining_reg,
                            'channel' => $request->channel,
                            'payment_type_id' => 1,
                        ]);
                    } else {
                        $over_pay = $remaining_reg - $loan->balance;
                        Payment::create([
                            'loan_id' => $loan->id,
                            'date_payed' => $request->date_payed,
                            'transaction_id' => $request->transaction_id,
                            'amount' => $loan->balance,
                            'channel' => $request->channel,
                            'payment_type_id' => 1,
                        ]);
                        $loan->update(['settled' => true]);

                        Regpayment::where('customer_id', $customer->id)->update([
                            'amount' => (int)$setting->registration_fee + $over_pay,
                            'transaction_id' => $request->transaction_id,
                        ]);
                    }
                }
            }

            // Send payment confirmation SMS for loan payment
            $phone = '+254' . substr($customer->phone, -9);
            $message = "Dear {$customer->fname}, your payment of KES {$request->amount} has been received. Your outstanding balance is KES {$loan->balance}. Thank you.";
            dispatch(new Sms($phone, $message, $customer, false));
        } else {
            // Case 5: Customer has no active loan
            $reg = Regpayment::where('customer_id', $customer->id)->first();
            if ($reg) {
                $reg->update([
                    'date_payed' => $request->date_payed,
                    'amount' => $request->amount + $reg->amount,
                    'transaction_id' => $request->transaction_id,
                ]);
            } else {
                Regpayment::create([
                    'customer_id' => $customer->id,
                    'date_payed' => $request->date_payed,
                    'amount' => $request->amount,
                    'transaction_id' => $request->transaction_id,
                    'channel' => $request->channel,
                ]);
            }

            // Send registration payment confirmation SMS
            $phone = '+254' . substr($customer->phone, -9);
            $message = "Dear {$customer->fname}, your payment of KES {$request->amount} for registration has been received. Thank you.";
            dispatch(new Sms($phone, $message, $customer, false));
        }

        return back()->with('success', 'Successfully reconciled the amount');
    }


    // public function reconcile_post(Request $request)
    // {
    //     $request->validate([
    //         'transaction_id' => ['required'],
    //         'channel' => ['required'],
    //         'date_payed' => ['required'],
    //         'amount' => ['required'],
    //     ]);

    //     //check if there is another transaction with the same transaction id
    //     $reg = Regpayment::where('transaction_id', $request->transaction_id)->first();
    //     $pay = Payment::where('transaction_id', $request->transaction_id)->first();
    //     $rec = DB::table('reconsiliation_transactions')->where(['transaction_id' => $request->transaction_id])->first();


    //     if ($rec || $reg || $pay) {
    //         $raw_payment = Raw_payment::where('mpesaReceiptNumber', Str::upper($request->transaction_id))->first();

    //         if ($raw_payment) {
    //             $raw_payment->delete();
    //         }

    //         return back()->with('error', 'This transaction has already been added in the system')->withInput();
    //     }

    //     $customer = Customer::find($request->customer_id);

    //     if (!$customer){
    //         return back()->with('error', 'No Customer found')->withInput();
    //     }

    //     // add transaction in reconsiation table
    //     DB::table('reconsiliation_transactions')->insert([
    //         'customer_id' => $request->customer_id,
    //         'reconsiled_by' => \auth()->id(),
    //         'amount' => $request->amount,
    //         'transaction_id' => $request->transaction_id,
    //         'phone_number' => $customer->phone,
    //         'channel' => $request->channel,
    //         'date_paid' => $request->date_payed,
    //         'created_at' => Carbon::now()
    //     ]);

    //     $raw_payment = Raw_payment::where('mpesaReceiptNumber', Str::upper($request->transaction_id))->first();

    //     if ($raw_payment) {
    //         $raw_payment->delete();
    //     }

    //     $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

    //     if ($loan) {
    //         //meaning has an active loan so first if he has paid reg fee
    //         $reg = Regpayment::where('customer_id', $customer->id)->first();

    //         $setting = Setting::first();
    //         if ($reg) {
    //             if ($reg->amount > $setting->registration_fee) {
    //                 //Registration amount is more than set registration
    //                 $remaining_reg = (int)$reg->amount - (int)$setting->registration_fee;
    //                 $reg->update([
    //                     'date_payed' => Carbon::now('Africa/Nairobi'),
    //                     "amount" => (int)$setting->registration_fee,
    //                     "transaction_id" => $request->transaction_id,
    //                 ]);

    //                 //remaider after am
    //                 $remaiderafter_reg = (int)$request->amount + $remaining_reg;
    //                 $this->rem_after_reg($request->transaction_id, $customer, $remaiderafter_reg);
    //             } elseif ($reg->amount == $setting->registration_fee) {
    //                 $reg_fee = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 3])->sum('amount');
    //                 // Check if customer has another settled loan
    //                 $loans = Loan::where(['customer_id' => $customer->id, 'settled' => true])->count();
    //                 if ($loans <= 0) {
    //                     if ($request->amount < $loan->balance) {
    //                         //amount remaining is less than or equal to loan amount
    //                         Payment::create([
    //                             'loan_id' => $loan->id,
    //                             'date_payed' => $request->date_payed,
    //                             'transaction_id' => $request->transaction_id,
    //                             'amount' => $request->amount,
    //                             'channel' => $request->channel,
    //                             'payment_type_id' => 1,
    //                         ]);
    //                     } else {
    //                         //amount remaining is greator than loan balance so put the remaining in reg fee account
    //                         $over_pay = $request->amount - $loan->balance;

    //                         Payment::create([
    //                             'loan_id' => $loan->id,
    //                             'date_payed' => $request->date_payed,
    //                             'transaction_id' => $request->transaction_id,
    //                             'amount' => $loan->balance,
    //                             'channel' => $request->channel,
    //                             'payment_type_id' => 1,
    //                         ]);

    //                         //set loan as paid
    //                         Loan::find($loan->id)->update(['settled' => true]);
    //                         $reg2 = Regpayment::where('customer_id', $customer->id)->first();
    //                         $add_to_reg = $reg2->update([
    //                             'date_payed' => $request->date_payed,
    //                             "amount" => $reg2->amount + $over_pay,
    //                             "transaction_id" => $request->transaction_id,
    //                         ]);
    //                     }
    //                     $mpesa = new MpesaPaymentController();
    //                     $mpesa->handle_installments($loan, $request->amount);
    //                 } else {
    //                     // Put processing fee into consideration
    //                     if ($reg_fee >= (int)$setting->loan_processing_fee) {
    //                         //meaning the loan processing fee has bee paid so continue in settling the loan

    //                         if ($request->amount < $loan->balance) {
    //                             //amount remaining is less than or equal to loan amount
    //                             Payment::create([
    //                                 'loan_id' => $loan->id,
    //                                 'date_payed' => Carbon::now("Africa/Nairobi"),
    //                                 'transaction_id' => $request->transaction_id,
    //                                 'amount' => $request->amount,
    //                                 'channel' => "MPESA",
    //                                 'payment_type_id' => 1,
    //                             ]);
    //                         } else {
    //                             //amount remaining is greator than loan balance so put the remaining in reg fee account
    //                             $over_pay = $request->amount - $loan->balance;

    //                             Payment::create([
    //                                 'loan_id' => $loan->id,
    //                                 'date_payed' => Carbon::now("Africa/Nairobi"),
    //                                 'transaction_id' => $request->transaction_id,
    //                                 'amount' => $loan->balance,
    //                                 'channel' => "MPESA",
    //                                 'payment_type_id' => 1,
    //                             ]);

    //                             //set loan as paid
    //                             Loan::find($loan->id)->update(['settled' => true]);
    //                             $reg2 = Regpayment::where('customer_id', $customer->id)->first();
    //                             $reg2->update([
    //                                 "amount" => $reg2->amount + $over_pay,
    //                                 "transaction_id" => $request->transaction_id,
    //                             ]);
    //                         }

    //                         $mpesa = new MpesaPaymentController();
    //                         $mpesa->handle_installments($loan, $request->amount);
    //                     } else {
    //                         // Pay the loan processing fee
    //                         Payment::create([
    //                             'loan_id' => $loan->id,
    //                             'date_payed' => Carbon::now("Africa/Nairobi"),
    //                             'transaction_id' => $request->transaction_id,
    //                             'amount' => (int) $setting->loan_processing_fee,
    //                             'channel' => "MPESA",
    //                             'payment_type_id' => 3,
    //                         ]);
    //                     }
    //                 }
    //             } else {
    //                 /*************************if paid registration amount is less than set registration fee*****************/
    //                 $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;

    //                 if ($request->amount <= $remaining_reg) {
    //                     $reg->update([
    //                         'date_payed' => Carbon::now('Africa/Nairobi'),
    //                         "amount" => (int)$reg->amount + $request->amount,
    //                         "transaction_id" => $request->transaction_id,
    //                     ]);
    //                 } else {
    //                     //more amount than registration
    //                     $reg->update([
    //                         'date_payed' => $request->date_payed,
    //                         "amount" => (int)$setting->registration_fee,
    //                         "transaction_id" => $request->transaction_id,
    //                     ]);

    //                     //remaider after reg
    //                     $remaiderafter_reg = (int)$request->amount - (int)$remaining_reg;
    //                     $this->rem_after_reg($request->transaction_id, $customer, $remaiderafter_reg);
    //                 }
    //             }
    //         } else {
    //             if ($request->amount <= (int)$setting->registration_fee) {
    //                 Regpayment::create([
    //                     'customer_id' => $customer->id,
    //                     'date_payed' => $request->date_payed,
    //                     "amount" => $request->amount,
    //                     "transaction_id" => $request->transaction_id,
    //                     "channel" => $request->channel,
    //                 ]);
    //             } else {
    //                 //more amount than registration
    //                 $remaining_reg = (int)$request->amount - (int)$setting->registration_fee;
    //                 Regpayment::create([
    //                     'customer_id' => $customer->id,
    //                     'date_payed' => $request->date_payed,
    //                     "amount" => (int)$setting->registration_fee,
    //                     "transaction_id" => $request->transaction_id,
    //                     "channel" => $request->channel,
    //                 ]);

    //                 //remaider after reg
    //                 $remaiderafter_reg = $remaining_reg;
    //                 $this->rem_after_reg($request->transaction_id, $customer, $remaiderafter_reg);
    //             }
    //         }
    //     } else {
    //         //meaning he has no active loan so check if registration fee is paid
    //         $reg = Regpayment::where('customer_id', $customer->id)->first();
    //         if ($reg) {
    //             $reg->update([
    //                 'date_payed' => $request->date_payed,
    //                 "amount" => $request->amount + $reg->amount,
    //                 "transaction_id" => $request->transaction_id,
    //             ]);
    //         } else {
    //             Regpayment::create([
    //                 'customer_id' => $customer->id,
    //                 'date_payed' => $request->date_payed,
    //                 "amount" => $request->amount,
    //                 "transaction_id" => $request->transaction_id,
    //                 "channel" => $request->channel,
    //             ]);
    //         }
    //     }

    //     return back()->with('success', 'Successfully reconciled the amount');
    // }

    public function reconciled_transactions()
    {
        $this->data['title'] = "Reconciled Transactions";
        return view('pages.payments.reconciled_transactions', $this->data);
    }

    // public function reconciled_transactions_data()
    // {
    //     $payments = DB::table('reconsiliation_transactions')
    //         ->join('customers', 'reconsiliation_transactions.customer_id','=', 'customers.id')
    //         ->join('users', 'reconsiliation_transactions.reconsiled_by','=', 'users.id')
    //         ->select('reconsiliation_transactions.*', 'customers.fname', 'users.name')
    //         ->get();

    //     return DataTables::of($payments)
    //         ->toJson();
    // }


    public function reconciled_transactions_data(Request $request)
    {
        $query = DB::table('reconsiliation_transactions')
            ->join('customers', 'reconsiliation_transactions.customer_id', '=', 'customers.id')
            ->join('users', 'reconsiliation_transactions.reconsiled_by', '=', 'users.id')
            ->select('reconsiliation_transactions.*', 'customers.fname', 'users.name');

        if ($request->has('start_date') && $request->start_date != null) {
            $query->whereDate('reconsiliation_transactions.date_paid', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != null) {
            $query->whereDate('reconsiliation_transactions.date_paid', '<=', $request->end_date);
        }

        if ($request->has('reconciled_by') && $request->reconciled_by != 'all') {
            $query->where('reconsiliation_transactions.reconsiled_by', $request->reconciled_by);
        }

        $payments = $query->orderBy('reconsiliation_transactions.date_paid', 'desc')->get();

        return DataTables::of($payments)->toJson();
    }


    public function unreconciled_transactions()
    {
        $this->data['title'] = "Payments that have not been reconciled";
        $this->data['branches'] = Branch::all();
        return view('pages.payments.unreconciled_transactions', $this->data);
    }

    // public function unreconciled_transactions_data()
    // {
    //     $payments = Raw_payment::orderBy('created_at', 'DESC')->get();

    //     return DataTables::of($payments)
    //     ->editColumn('transaction_id', function ($payment) {
    //         return $payment->mpesaReceiptNumber;
    //     })
    //     ->editColumn('first_name', function ($payment) {
    //         return $payment->customer;
    //     })
    //     ->editColumn('account_number', function ($payment) {
    //         return $payment->account_number;
    //     })
    //     ->editColumn('amount', function ($payment) {
    //         return $payment->amount;
    //     })
    //     ->editColumn('created_at', function ($payment) {
    //         return $payment->created_at->format('Y-m-d H:i:s');
    //     })
    //     ->toJson();
    // }
    public function unreconciled_transactions_data(Request $request)
    {
        $query = Raw_payment::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('branch')) {
            $query->where('BusinessShortCode', $request->branch);
        }

        $payments = $query->orderBy('created_at', 'DESC')->get();

        return DataTables::of($payments)
            ->editColumn('transaction_id', function ($payment) {
                return $payment->mpesaReceiptNumber;
            })
            ->editColumn('first_name', function ($payment) {
                return $payment->customer;
            })
            ->editColumn('account_number', function ($payment) {
                return $payment->account_number;
            })
            ->editColumn('amount', function ($payment) {
                return $payment->amount;
            })
            ->editColumn('created_at', function ($payment) {
                return $payment->created_at->format('Y-m-d H:i:s');
            })
            ->toJson();
    }



    //remaider after reg
    public function rem_after_reg($transaction_id, $customer, $remaiderafter_reg)
    {
        $setting = Setting::first();

        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

        /********************if balance is less or equal to loan balance**************/
        if ($remaiderafter_reg < $loan->balance) {
            //amount remaining is less than or equal to loan balance
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::now("Africa/Nairobi"),
                'transaction_id' => $transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
        } elseif ($remaiderafter_reg == $loan->balance) {
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::now("Africa/Nairobi"),
                'transaction_id' => $transaction_id,
                'amount' => $remaiderafter_reg,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);
            Loan::find($loan->id)->update(['settled' => true]);
        } else {
            //amount remaining is greator than loan amount so put the remaining in reg fee account
            $pay_loan = Payment::create([
                'loan_id' => $loan->id,
                'date_payed' => Carbon::now("Africa/Nairobi"),
                'transaction_id' => $transaction_id,
                'amount' => (int)$loan->balance,
                'channel' => "MPESA",
                'payment_type_id' => 1,
            ]);

            //set loan as paid
            Loan::find($loan->id)->update(['settled' => true]);
            $over_pay = $remaiderafter_reg - $loan->balance;
            $reg2 = Regpayment::where('customer_id', $customer->id)->first();
            $add_to_reg = $reg2->update([
                //'date_payed' => Carbon::now('Africa/Nairobi'),
                "amount" => $reg2->amount + $over_pay,
                "transaction_id" => $transaction_id,
            ]);
        }

        $mpesa = new MpesaPaymentController();
        $mpesa->handle_installments($loan, $remaiderafter_reg);
    }
}
