<?php

namespace App\Http\Controllers;

use AfricasTalking\SDK\AfricasTalking;
use App\Jobs\Disburse;
use App\Jobs\Sms;
use App\models\Arrear;
use App\models\Branch;
use App\models\Customer;
use App\models\Installment;
use App\models\Loan;
use App\models\MpesaTransaction;
use App\models\Mrequest;
use App\models\Msetting;
use App\models\Payment;
use App\models\Product;
use App\models\Regpayment;
use App\models\Setting;
use App\Services\Custom;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class DisbursementController extends Controller
{
    /***************************disbursement of loans***************************/
    public function disbursement_initial()
    {
        $this->data['title'] = "Loans Awaiting Disbursement";

        $this->data['sub_title'] = "List of All Loans Pending Disbursement.";

        return view('pages.loans.disburse', $this->data);
    }

    public function disbursement()
    {
        $this->data['title'] = "Loans Awaiting Disbursement";

        $this->data['sub_title'] = "List of All Loans Pending Disbursement.";
        $approval_token_session = encrypt("empty");

        if (Session::get("disburse_token_session")){
            $approval_token_session = Session::get("disburse_token_session");
        }

        $this->data['disburse_token_session'] = $approval_token_session;

        return view('pages.loans.disburse_revamped', $this->data);
    }

    public function disbursed()
    {
        $this->data['title'] = "Disbursed Loans";

        $this->data['sub_title'] = "List of All Disbursed Loans.";

        return view('pages.loans.disbursement-history', $this->data);
    }

    public function disburse_loans_data(Request $request)
    {
        if ($request->branch) {
            $branch = Branch::where('id', $request->branch)->first()->setAppends([]);
            if($request->branch == "all"){
                //$branch = Branch::where('id', $request->branch)->first();
                $lo = Loan::query()->whereDoesntHave('mrequests', function ($q) {
                    $q->where('settled', false)->orderBy('id', 'DESC');
                })->where(['approved' => true, 'disbursed' => false])
                    ->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
            } else {
                $lo = $branch->loans()->where(['approved' => true, 'disbursed' => false])->whereDoesntHave('mrequests', function ($q) {
                    $q->where('settled', false)->orderBy('id', 'DESC');
                })->get()->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
            }
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant')) {
            $lo = Loan::where(['approved' => true, 'disbursed' => false])->whereDoesntHave('mrequests', function ($q) {
                $q->where('settled', false)->orderBy('id', 'DESC');
            })->get()->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first()->setAppends([]);
            $lo = $branch->loans()->where(['approved' => true, 'disbursed' => false])->whereDoesntHave('mrequests', function ($q) {
                $q->where('settled', false)->orderBy('id', 'DESC');
            })->get()->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
        }

        return Datatables::of($lo)
            ->editColumn('owner', function ($lo){
                $customer = Customer::find($lo->customer_id)->setAppends([]);
                $group = $customer->group()->first();
                if ($group){
                    $group_name = $group->name;
                    return $customer->fname. ' '. $customer->lname. '<br>'. '<span class="badge badge-primary" style="font-size: small">'.$group_name.'</span>';
                }else{
                    return $customer->fname. ' '. $customer->lname;
                }
            })
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'Yes';
                } else {
                    return 'NO';
                }
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);
                //return '<a href="' . route('loans.post_disburse', ['id' => $data]) . '"    class="disburse btn  btn-primary"><i class="feather icon-eye"></i> Disburse</a>';
                return '<div class="btn-group text-center">
                                <a type="button" class="btn  btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                    <li><a href="' . route('loans.post_disburse', ['id' => $data]) . '"class="disburse btn btn-primary" style="margin-bottom: 10px;"><i class="feather icon-eye"></i> Disburse</a></li>
                                    <li><a class="ldelete btn btn-primary" href="' . route('loans.delete', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                                </ul>
                        </div>';
            })
            ->addColumn('checkbox', function ($lo) {
                return '<input type="checkbox" name="id[]" title="Check to Disburse" value="' . encrypt($lo->id) . '" id="' . $lo->id . '">';
            })
            // ->addColumn('branch', function ($lo) {
            //     $Customer = Customer::find($lo->customer_id);
            //     return Branch::find($Customer->branch_id)->bname;
            // })
            ->rawColumns(['action', 'checkbox', 'owner'])
            ->make(true);
    }

    public function disbursed_loans_data(Request $request)
    {
        if ($request->branch) {
            $branch = Branch::where('id', $request->branch)->first()->setAppends([]);
            if($request->branch == "all"){
                //$branch = Branch::where('id', $request->branch)->first();
                $lo = Loan::query()->whereDoesntHave('mrequests', function ($q) {
                    $q->where('settled', false)->orderBy('id', 'DESC');
                })->where(['approved' => true, 'disbursed' => true])
                    ->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
            } else {
                $lo = $branch->loans()->where(['approved' => true, 'disbursed' => true])->whereDoesntHave('mrequests', function ($q) {
                    $q->where('settled', false)->orderBy('id', 'DESC');
                })->get()->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
            }
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('accountant')) {
            $lo = Loan::where(['approved' => true, 'disbursed' => true])->whereDoesntHave('mrequests', function ($q) {
                $q->where('settled', false)->orderBy('id', 'DESC');
            })->get()->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first()->setAppends([]);
            $lo = $branch->loans()->where(['approved' => true, 'disbursed' => true])->whereDoesntHave('mrequests', function ($q) {
                $q->where('settled', false)->orderBy('id', 'DESC');
            })->get()->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
        }

        return Datatables::of($lo)
            ->editColumn('owner', function ($lo){
                $customer = Customer::find($lo->customer_id)->setAppends([]);
                $group = $customer->group()->first();
                if ($group){
                    $group_name = $group->name;
                    return $customer->fname. ' '. $customer->lname. '<br>'. '<span class="badge badge-primary" style="font-size: small">'.$group_name.'</span>';
                }else{
                    return $customer->fname. ' '. $customer->lname;
                }
            })
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'Yes';
                } else {
                    return 'NO';
                }
            })
            ->addColumn('branch', function ($lo) {
                $Customer = Customer::find($lo->customer_id);
                return Branch::find($Customer->branch_id)->bname;
            })
            ->rawColumns(['owner'])
            ->make(true);
    }

    public function post_disburse(Request $request, $id)
    {
        $token = decrypt(Session::get("disburse_token_session"));

        $service = new Custom();
        $tk = $service->check_token_validity($token, 'disburse');
        if ($tk == 0){
            return back()->with('error', 'Your activity token is invalid');
        }

        $loan = Loan::find(decrypt($id));
        //find if there are previous request which has not been settled
        $mrequest = Mrequest::where(['loan_id' => $loan->id, 'settled' => false])->orderBy('id', 'DESC')->first();
        if ($request->isMethod('post')) {

        } else {
            if ($mrequest) {
                return back()->with('error', 'You have some unsettled loan request');
            }
        }
        if ($loan->disbursed) {
            return back()->with('error', 'This loan has already been disbursed');
        }

        $customer = Customer::find($loan->customer_id);

        /******************************check if the branch has enough money for disbursement********************/
        //$branch = Branch::find($customer->branch_id);
        //$net_cashflow = $this->check_netcashinflow($branch->id);
        // if ($loan->loan_amount > $net_cashflow) {
        //     return back()->with('error', 'You have insufficient fund to make disbursement');
        // }
        /*******************************************************connect with the MPesa B2C api************************************************/
        //$environment = "live";
        $environment = env("MPESA_ENV");
        if ($environment == "live") {
            $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
            $token = self::generateLiveToken();

            $phone = '254' . substr($customer->phone, -9);;
            $msetting = Msetting::first();
            $InitiatorName = $msetting->InitiatorName;
            $PartyA = $msetting->paybill;
            $SecurityCredential = $msetting->SecurityCredential;
            $CommandID = "BusinessPayment";
            $Amount = $loan->loan_amount;
            $PartyB = $phone;
        } elseif ($environment == "sandbox") {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
            $token = self::generateSandBoxToken();

            $phone = "254708374149";
            $InitiatorName = "testapi0321";
            $SecurityCredential = "Yb63VpkEaLbrtJaR3IhElVENIcJ9k1k7PaMa+T91wBymrzNHK4zllt5cNSn/Pgz89ZqHUbU1EIqrVlKiffBQlR8YAgvJFsfDN0VdzWtPu/0YpVFfbWBKkPj+mBqUgVZ01X4goa0vGi7YjaysniMG5b1zvMFN2rhJr03A0TN1pK0+c9/k3pJzG0Vpur0/gK/dmHjrVo2wZbaTdtU0FmRza3NMNpf/B2u0HT7mXsZkWsnr9C1Rqo+6aJX2Dd7LUkSpkebAC010sGvrEaa3SCMXgc6TOCIZjJPgg9jzT70tqQ5SZ84HPjdKl9/MxGhS7n+lyAkFFlQGxSJH380QRfmRyA==";
            $CommandID = "BusinessPayment";
            $Amount = $loan->loan_amount;
            $PartyA = "600321";
            $PartyB = "254708374149";
        } else {
            return json_encode(["Message" => "invalid application status"]);
        }

        $Remarks = 'some info';
        $QueueTimeOutURL = route('api.mpesa_disbursement.timeout');
        $ResultURL = route('api.mpesa_disbursement.result');
        $Occasion = 'Loan Dispersal';
        $client = new Client();

        try {
            $check = $client->request('post', $url, [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(
                    [
                        'InitiatorName' => $InitiatorName,
                        'SecurityCredential' => $SecurityCredential,
                        'CommandID' => $CommandID,
                        'Amount' => $Amount,
                        'PartyA' => $PartyA,
                        'PartyB' => $PartyB,
                        'Remarks' => $Remarks,
                        'QueueTimeOutURL' => $QueueTimeOutURL,
                        'ResultURL' => $ResultURL,
                        'Occasion' => $Occasion,
                    ]
                )
            ]);

            $obj = json_decode((string)$check->getBody());

            if ($obj->ResponseCode == 0) {
                $mreq = Mrequest::create([
                    'ConversationID' => $obj->ConversationID,
                    'loan_id' => $loan->id,
                    'OriginatorConversationID' => $obj->OriginatorConversationID,
                    'ResponseCode' => $obj->ResponseCode,
                    'ResponseDescription' => $obj->ResponseDescription,
                    'requested_by' => Auth::user()->id,
                    'amount' => $Amount,
                    'disburse_loan_ip' =>$request->ip()
                ]);
                return back()->with('success', 'Successfully requested the payment. We will inform you when done');
                // return redirect('loans')->with('success', 'Successfully requested the payment. We will inform you when done');
            }
            return back()->with('error', 'Could not complete request at this time. Please again later');
            // return redirect('loans')->with('error', 'Could not complete request at this time. Please again later');
        } catch (\Exception $e) {
            // $event=Event::where('id', $request->event)->first();
            /*return response()->json([
                'status' => 'error',
                'message' => 'We seem to be having a problem. Please try again later',
            ]);*/
            // return redirect('loans')->with('error', 'We seem to be having a problem. Please try again later');
            return back()->with('error', 'We seem to be having a problem. Please try again later');
        }
    }

    //post disburse multiple
    public function post_disburse_multiple(Request $request)
    {
        /****************************************check if the amount being disbursed if its greator than the available amount*******************/
        $am = 0;
        foreach ($request->id as $id) {
            $loan = Loan::find(decrypt($id));
            $am += $loan->loan_amount;
        }

        $tloan = Loan::find(decrypt($request->id[0]));
        $cus = Customer::find($tloan->customer_id);

        // $branch = Branch::find($cus->branch_id);
        // $available_amount = $this->check_netcashinflow($branch->id);

        // if ($am > $available_amount) {
        //     return back()->with('error', 'You do not have sufficient amount to perform these transactions');
        // }

        /*******************************************************connect with the MPesa B2C api************************************************/

        $environment = env("MPESA_ENV");

        if ($environment == "live") {
            $token = self::generateLiveToken();
            $msetting = Msetting::first();
            $InitiatorName = $msetting->InitiatorName;
            $PartyA = $msetting->paybill;
            $SecurityCredential = $msetting->SecurityCredential;
            $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
            $CommandID = "BusinessPayment";
            $Remarks = 'some info';
            $QueueTimeOutURL = route('api.mpesa_disbursement.timeout');
            $ResultURL = route('api.mpesa_disbursement.result');
            $Occasion = 'Loan Dispersal';
        } elseif ($environment == "sandbox") {
            $token = self::generateSandBoxToken();
            $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';

            $InitiatorName = "testapi0321";
            $SecurityCredential = "Yb63VpkEaLbrtJaR3IhElVENIcJ9k1k7PaMa+T91wBymrzNHK4zllt5cNSn/Pgz89ZqHUbU1EIqrVlKiffBQlR8YAgvJFsfDN0VdzWtPu/0YpVFfbWBKkPj+mBqUgVZ01X4goa0vGi7YjaysniMG5b1zvMFN2rhJr03A0TN1pK0+c9/k3pJzG0Vpur0/gK/dmHjrVo2wZbaTdtU0FmRza3NMNpf/B2u0HT7mXsZkWsnr9C1Rqo+6aJX2Dd7LUkSpkebAC010sGvrEaa3SCMXgc6TOCIZjJPgg9jzT70tqQ5SZ84HPjdKl9/MxGhS7n+lyAkFFlQGxSJH380QRfmRyA==";
            $CommandID = "BusinessPayment";

            // $PartyA = "600506";
            $PartyA = "600321";
            $Remarks = 'some info';

            //$Remarks = "Disperse loan to " . $customer->fullname;
            $QueueTimeOutURL = route('mpesa_disbursement.timeout');
            $ResultURL = route('mpesa_disbursement.result');
            $Occasion = 'Loan Dispersal';
        }

        $requestedby = Auth::user()->id;
        $ip = $request->ip();

        foreach ($request->id as $id) {
            $loan = Loan::find(decrypt($id));
            if ($loan->disbursed) {
                return back()->with('error', 'One of the loan has been disbursed');
            }

            $customer = Customer::where('id', $loan->customer_id)->first();
            if ($environment == "live") {
                $PartyB = '254' . substr($customer->phone, -9);
            } else {
                $PartyB = "254708374149";
            }

            $mrequest = Mrequest::where(['loan_id' => $loan->id, 'settled' => false])->orderBy('id', 'DESC')->first();

            if ($mrequest) {
                return back()->with('error', 'You have some unsettled loan request');
            }

            $fnd = dispatch(new Disburse(
                $token, $loan->id, $loan->loan_amount, $PartyB, $url, $InitiatorName, $PartyA, $Remarks, $QueueTimeOutURL, $ResultURL,
                $Occasion, $SecurityCredential, $CommandID, $requestedby, $ip
            ));
        }

        return back()->with('success', 'Successfully requested the payment. We will inform you when done');
        // return redirect('loans')->with('success', 'Successfully requested the payment. We will inform you when done');
    }

    /******************************result from safaricom*********************/
    public function mpesa_disbursement_result(Request $request)
    {
        $callbackJSONData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJSONData);

        $resultCode = $callbackData->Result->ResultCode;
        $resultDesc = $callbackData->Result->ResultDesc;
        $originatorConversationID = $callbackData->Result->OriginatorConversationID;
        $conversationID = $callbackData->Result->ConversationID;
        $transactionID = $callbackData->Result->TransactionID;
        $loan_id = Mrequest::where('OriginatorConversationID', $originatorConversationID)->first()->loan_id;
        $loan = Loan::find($loan_id);
        $phone = $loan->phone;
        //$user = Customer::find($loan->customer_id)->first();
        $cus = Customer::find($loan->customer_id)->first();
        $settings = Setting::first();
        /******************success parameters*************************/
        if ($resultCode == 0) {
            $TransactionAmount = $callbackData->Result->ResultParameters->ResultParameter[0]->Value;
            $TransactionReceipt = $callbackData->Result->ResultParameters->ResultParameter[1]->Value;
            $ReceiverPartyPublicName = $callbackData->Result->ResultParameters->ResultParameter[2]->Value;
            $TransactionCompletedDateTime2 = $callbackData->Result->ResultParameters->ResultParameter[3]->Value;
            $B2CUtilityAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[4]->Value;
            $B2CWorkingAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[5]->Value;
            $B2CRecipientIsRegisteredCustomer = $callbackData->Result->ResultParameters->ResultParameter[6]->Value;
            $B2CChargesPaidAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[7]->Value;

            $int = date_create($TransactionCompletedDateTime2);
            //$TransactionCompletedDateTime = Carbon::parse($TransactionCompletedDateTime2)->toDateTimeString();
            $TransactionCompletedDateTime = date("Y-m-d h:m:s");

            $result = [
                "ResultCode" => $resultCode,
                "ResultDesc" => $resultDesc,
                "OriginatorConversationID" => $originatorConversationID,
                "ConversationID" => $conversationID,
                "TransactionID" => $transactionID,
                "TransactionAmount" => $TransactionAmount,
                "TransactionReceipt" => $TransactionReceipt,
                "B2CRecipientIsRegisteredCustomer" => $B2CRecipientIsRegisteredCustomer,
                "B2CChargesPaidAccountAvailableFunds" => $B2CChargesPaidAccountAvailableFunds,
                "ReceiverPartyPublicName" => $ReceiverPartyPublicName,
                "TransactionCompletedDateTime" => $TransactionCompletedDateTime,
                "B2CUtilityAccountAvailableFunds" => $B2CUtilityAccountAvailableFunds,
                "B2CWorkingAccountAvailableFunds" => $B2CWorkingAccountAvailableFunds,
                "status" => true,
                'loan_id' => Mrequest::where('OriginatorConversationID', $originatorConversationID)->first()->loan_id
            ];
            //$this->data['success2'] = 'Your Request of redeeming Ksh.'.$TransactionAmount.' has been approved';
        } /*******************************transaction was not successfull************************************/
        else {
            $result = [
                "ResultCode" => $resultCode,
                "ResultDesc" => $resultDesc,
                "OriginatorConversationID" => $originatorConversationID,
                "ConversationID" => $conversationID,
                "TransactionID" => $transactionID,
                "status" => false,
                'loan_id' => Mrequest::where('OriginatorConversationID', $originatorConversationID)->first()->loan_id
            ];
            // $mrequest = Mrequest::where('OriginatorConversationID', $originatorConversationID)->first()->amount;
            // $this->data['failure'] = 'Your Request of redeeming Ksh.'.$mrequest.' has not been approved. Please try again later';
        }

        $tran = MpesaTransaction::where('OriginatorConversationID', $originatorConversationID)->first();
        //  $this->data = [];
        if (!$tran) {
            $mrequest = Mrequest::where('OriginatorConversationID', $originatorConversationID)->update(['settled' => true]);
            $Transaction = MpesaTransaction::create($result);
            //$mrequest = Mrequest::where('OriginatorConversationID', $originatorConversationID)->update(['settled' => true]);
            /*********************update payment about the success transaction**************************/
            if ($Transaction->status) {
                //create installments
                $product = Product::find($loan->product_id)/*->installments*/;
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
                                "due_date" => Carbon::now()->addDays(1)->addDays($days),
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
                                "lp_fee" => $lp_fee,
                                "due_date" => Carbon::now()->addDays(1)->addDays($days),
                                "start_date" => Carbon::now(),
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
                                "lp_fee" => $lp_fee,
                                "due_date" => Carbon::now()->addDays($days),
                                "start_date" => Carbon::now(),
                                "current" => false,
                                "amount_paid" => 0,
                                "position" => $i + 1
                            ]);
                        }
                    }
                }

                $mrq = Mrequest::where('OriginatorConversationID', $originatorConversationID)->first();

                $updateRedeem = Payment::create([
                    'loan_id' => $mrq->loan_id,
                    'amount' => $TransactionAmount,
                    'transaction_id' => $TransactionReceipt,
                    'date_payed' => $TransactionCompletedDateTime,
                    'channel' => "MPESA",
                    'payment_type_id' => "2",
                ]);

                $loan->update([
                    "has_lp_fee" => true,
                    "disbursed" => true,
                    "disbursement_date" => Carbon::now(),
                    "end_date" => Carbon::now()->addDays($loan->product()->first()->duration),
                    "disbursed_by" => $mrq->requested_by,
                    'disburse_loan_ip' => $mrq->disburse_loan_ip
                ]);

                $ln = Loan::find($loan_id);

                //check if registration payment is more than required
                $reg = Regpayment::where('customer_id', $ln->customer_id)->first();
                // $settings = Setting::first();
                if ($reg) {
                    //meaning the registration is greater than required so put the extra in loan processing fee
                    if ($reg->amount > $settings->registration_fee) {
                        //balance after registration
                        $bal = $reg->amount - $settings->registration_fee;
                        //meaning the remaining balance is greater than loan processing fee
                        if ($bal > $settings->loan_processing_fee) {
                            $processing = Payment::create([
                                'payment_type_id' => 3,
                                'loan_id' => $ln->id,
                                'date_payed' => Carbon::now(),//$reg->date_payed,
                                'transaction_id' => $reg->transaction_id,
                                'channel' => 'MPESA',
                                'amount' => $settings->loan_processing_fee
                            ]);
                            $rem = $bal - $settings->loan_processing_fee;

                            //then add the remaining to the loan settlement
                            if ($rem > $ln->balance){
                                $remainda = $rem - $ln->balance;
                                $settlement = Payment::create([
                                    'payment_type_id' => 1,
                                    'loan_id' => $ln->id,
                                    'date_payed' => Carbon::now(),//$reg->date_payed,
                                    'transaction_id' => $reg->transaction_id,
                                    'channel' => 'MPESA',
                                    'amount' => $ln->balance
                                ]);
                                $ln->update(['settled' => true]);
                            }
                            else{
                                $settlement = Payment::create([
                                    'payment_type_id' => 1,
                                    'loan_id' => $ln->id,
                                    'date_payed' => Carbon::now(),//$reg->date_payed,
                                    'transaction_id' => $reg->transaction_id,
                                    'channel' => 'MPESA',
                                    'amount' => $rem
                                ]);
                                $remainda = 0;
                            }
                            /* $settlement = Payment::create([
                                'payment_type_id' => 1,
                                'loan_id' => $ln->id,
                                'date_payed' => $reg->date_payed,
                                'transaction_id' => $reg->transaction_id,
                                'channel' => 'MPESA',
                                'amount' => $rem
                            ]);*/
                            //add the amount to current installment being paid
                            $handle_installments = new MpesaPaymentController();
                            $prices = $handle_installments->handle_installments($ln, $rem);


                        } //amount remaining is not greater than loan processing fee
                        else {
                            $processing = Payment::create([
                                'payment_type_id' => 3,
                                'loan_id' => $ln->id,
                                'date_payed' => Carbon::now(),//$reg->date_payed,
                                'transaction_id' => $reg->transaction_id,
                                'channel' => 'MPESA',
                                'amount' => $bal
                            ]);
                            $remainda = 0;
                        }
                        $reg->update([
                            'amount' => $settings->registration_fee + $remainda
                        ]);
                    }
                }
                $env = "live";
                //if (env('MPESA_ENV') == 'live') {
                if ($env == 'live') {
                    /***************************Send sms*********************************/
                    $phone = '+254' . substr($phone, -9);
                    $user_type = false;
                    $message = "Your Loan of Ksh " . $TransactionAmount . ' has been approved. You will receive an Mpesa notification';
                    $fnd = dispatch(new Sms(
                        $phone, $message, $cus, $user_type
                    ));

                    //update admin and accountant
                    $aphones = ["+25411591065"];
                    foreach ($aphones as $aphone)
                    {
                        $suser_type = true;
                        if ($ln->loan_type_id == 1){
                            $amessage = "Daily Repayment Loan of Ksh " . $TransactionAmount . ' has been approved and sent to ' . $phone;
                        }elseif ($ln->loan_type_id == 2){
                            $amessage = "Weekly Repayment Loan of Ksh " . $TransactionAmount . ' has been approved and sent to ' . $phone;
                        }else{
                            $amessage = "Loan of Ksh " . $TransactionAmount . ' has been approved and sent to ' . $phone;
                        }
                        $auser = User::first();
                        $fnd = dispatch(new Sms(
                            $aphone, $amessage, $auser, $suser_type
                        ));
                    }
                }
            } /*********************update user about the failed transaction**************************/
            else {
                $mrequest = Mrequest::where('OriginatorConversationID', $originatorConversationID)->first()->amount;
                $this->data['failure'] = 'Your Request of redeeming Ksh.' . $mrequest . ' has not been approved. Please try again later';
            }
        }
        //terminate transaction
        $this->finishTransaction();
    }

    /**********************Disbursement Time *******************************/
    public function mpesa_disbursement_result_timeout(Request $request)
    {
        info('Disbursement Timeout');
        $callbackJSONData = file_get_contents('php://input');

        info($callbackJSONData);
    }

    public static function generateLiveToken()
    {
        $settings = Msetting::first();

        $consumer_key = $settings->Consumer_Key;
        $consumer_secret = $settings->Consumer_Secret;

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

        // dd($credentials, $credentials2);

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

    //mpesa balance
    public function mpesa_balance()
    {
        $environment = config("services.mpesa.env");

        if ($environment == "live") {
            $url = 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query';
            $token = self::generateLiveToken();
            $msetting = Msetting::first();
            $InitiatorName = $msetting->InitiatorName;
            $PartyA = $msetting->paybill;
            $SecurityCredential = $msetting->SecurityCredential;
            $CommandID = "AccountBalance";
            $IdentifierType = "4";
            $QueueTimeOutURL = route('api.mpesa_balance.timeout');
            $ResultURL = route('api.mpesa_balance.result');
            $Remarks = 'ok';
        } elseif ($environment == "sandbox") {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query';
            $token = self::generateSandBoxToken();
            $InitiatorName = "testapi0321";
            $SecurityCredential = "Yb63VpkEaLbrtJaR3IhElVENIcJ9k1k7PaMa+T91wBymrzNHK4zllt5cNSn/Pgz89ZqHUbU1EIqrVlKiffBQlR8YAgvJFsfDN0VdzWtPu/0YpVFfbWBKkPj+mBqUgVZ01X4goa0vGi7YjaysniMG5b1zvMFN2rhJr03A0TN1pK0+c9/k3pJzG0Vpur0/gK/dmHjrVo2wZbaTdtU0FmRza3NMNpf/B2u0HT7mXsZkWsnr9C1Rqo+6aJX2Dd7LUkSpkebAC010sGvrEaa3SCMXgc6TOCIZjJPgg9jzT70tqQ5SZ84HPjdKl9/MxGhS7n+lyAkFFlQGxSJH380QRfmRyA==";
            $CommandID = "AccountBalance";
            $IdentifierType = "4";
            $PartyA = "600321";
            $QueueTimeOutURL = route('mpesa_balance.timeout');
            $ResultURL = 'https://98f5-105-163-0-250.ngrok-free.app/';
            $Remarks = 'MPESA Balance';
        } else {
            return json_encode(["Message" => "invalid application status"]);
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(
                array(
                    'Initiator' => $InitiatorName,
                    'SecurityCredential' => $SecurityCredential,
                    'CommandID' => $CommandID,
                    'PartyA' => $PartyA,
                    'Remarks' => $Remarks,
                    'QueueTimeOutURL' => $QueueTimeOutURL,
                    'ResultURL' => $ResultURL,
                    'IdentifierType' => $IdentifierType
                )
            ),

            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // $client = new Client();

        // try {
        //     $check = $client->request('post', $url, [
        //         'verify' => false,
        //         'headers' => [
        //             'Authorization' => 'Bearer ' . $token,
        //             'Content-Type' => 'application/json',
        //         ],
        //         'body' => json_encode(
        //             [
        //                 'Initiator' => $InitiatorName,
        //                 'SecurityCredential' => $SecurityCredential,
        //                 //'SecurityCredential' => 'nCctVVmOgmkigmtT3O9nKeGGKDCmNI5i+DWVt3PoT5wwjKg4Fql8rKt+tGied4Zo4f7LQbMzTYPeXpYubXR456oWbkVPQlgK9752Yqu4Xyjyjymw5jlpp8Di4m/zyK5JzOOYaCle15133AfACWvP8LwBPDPHjXTF/ySLTMiifJtLPqd1XXneHTDo6a34paZrdj9gWILcvqLRCp1n2lD8tKb55XvT9dO8nmlsUaNPjf/jJDYF5WzEHF7lh40RPLelXenco82D/rNvP+BHbwChenikCiVZPaVjpHAbYJTKiednj0Sso+zmloqiDLjVT7tXGHSrlkHTbvfwvlKi6a8BCA==',
        //                 'CommandID' => $CommandID,
        //                 'PartyA' => $PartyA,
        //                 'Remarks' => $Remarks,
        //                 'QueueTimeOutURL' => $QueueTimeOutURL,
        //                 'ResultURL' => $ResultURL,
        //                 'IdentifierType' => $IdentifierType
        //             ]
        //         )
        //     ]);

        //     $obj = json_decode((string)$check->getBody());

        //     if ($obj->ResponseCode == 0) {
        //         return true;
        //     }
        //     return false;

        // } catch (\Exception $e) {
        //     return false;
        // }
    }

    /*********************************balance result**************************/
    public function mpesa_balance_result(Request $request)
    {
        $callbackJSONData = file_get_contents('php://input');

        info($callbackJSONData);

        $callbackData = json_decode($callbackJSONData);

        $resultCode = $callbackData->Result->ResultCode;

        /******************success parameters*************************/
        if ($resultCode == 0) {
            $ar = explode('&', $callbackData->Result->ResultParameters->ResultParameter[1]->Value);
            $set = Msetting::first()->update([
                'MMF_balance' => $ar[0],
                'Utility_balance' => $ar[1],
                'last_updated' => Carbon::now()
            ]);
        }
    }

    /**********************************disbursed but the system has not caught the transaction**********************/
    public function disbursement_pending()
    {
        $this->data['sub_title'] = "Pending Disbursement";

        $this->data['sub_title'] = "Pending disbursement Loans";

        return view('pages.loans.pending_disbursement', $this->data);

    }

    public function disburse_loans_pending_data()
    {
        if (Auth::user()->hasRole('admin|accountant')) {
            $lo = Loan::where(['approved' => true, 'disbursed' => false])->WhereHas('mrequests', function ($q) {
                $q->where('settled', false)->orderBy('id', 'DESC');
            })->get()->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
        } else {
            $branch = Branch::where('id', Auth::user()->branch_id)->first()->setAppends([]);
            $lo = $branch->loans()->where(['approved' => true, 'disbursed' => false])->WhereHas('mrequests', function ($q) {
                $q->where('settled', false)->orderBy('id', 'DESC');
            })->get()->each->setAppends(['owner','product','installments','interest','phone','branch_name']);
        }

        return Datatables::of($lo)
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'Yes';
                } else {
                    return 'NO';
                }
            })
            ->addColumn('action', function ($lo) {
                $data = encrypt($lo->id);
                //return '<a href="' . route('loans.post_disburse', ['id' => $data]) . '"    class="sel-btn btn btn-xs btn-primary"><i class="feather icon-eye text-info"></i> Disburse</a>';
                    return '<div class="btn-group text-center">
                                <button type="button" class="sel-btn btn btn-xs btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </button>
                                <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                    <li><a href="' . route('re_disburse_post', ['id' => $data]) . '"   onclick="event.preventDefault();
                                        document.getElementById(\'re-form\').submit(); "><i class="feather icon-eye" ></i> Re Disburse</a></li>
                                    <li><a href="' . route('disburse_reconcile', ['id' => $data]) . '"><i class="feather icon-delete text-danger" ></i> Reconcile</a></li>
                                </ul>
                                <form id="re-form" action="' . route('re_disburse_post', ['id' => $data]) . '" method="POST" style="display: none;">
                                 <input type="hidden" name="_token" value="' . csrf_token() . '">
                                </form>
                             </div>';
            })
            ->addColumn('checkbox', function ($lo) {
                return '<input type="checkbox" name="id[]" value="' . encrypt($lo->id) . '" id="' . $lo->id . '">';
            })
            ->rawColumns(['action', 'checkbox'])
            ->make(true);

    }

    /***************************************reconcile disbursement*********************************/
    public function disburse_reconcile_form($id)
    {
        $this->data['sub_title'] = "Disbursement Reconciliation";
        $this->data['id'] = $id;


        return view('pages.loans.disburse_reconcile_form', $this->data);

    }

    public function post_disburse_reconcile(Request $request)
    {
        $loan = Loan::find(decrypt($request->id));
        $ln = $loan;
        /*********************update payment about the success transaction**************************/
        //create installments
        $product = Product::find($loan->product_id);
        $principle_amount = round($loan->loan_amount / $product->installments);
        $interest_payable = round(($loan->loan_amount * $product->interest / 100) / $product->installments);
        $settings = Setting::first();
        $lp_fee = 0;
        if ($settings->lp_fee){
            $lp_fee = $settings->lp_fee / $product->installments;
        }
        $amountPayable = $principle_amount;
        $days = 0;
        for ($i = 0; $i < $product->installments; $i++) {
            $days = $days + 7;
            if ($i == 0) {
                Installment::create([
                    "loan_id" => $loan->id,
                    "principal_amount" => $principle_amount,
                    "total" => $amountPayable,
                    "interest" => $interest_payable,
                    "lp_fee" => $lp_fee,
                    "due_date" => Carbon::parse($request->date_payed)->addDays($days),
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
                    "lp_fee" => $lp_fee,
                    "due_date" => Carbon::parse($request->date_payed)->addDays($days),
                    "start_date" => Carbon::now(),
                    "current" => false,
                    "amount_paid" => 0,
                    "position" => $i + 1

                ]);
            }
        }
        /************************************update the  */
        $updateRedeem = Payment::create([
            'loan_id' => $loan->id,
            'amount' => $loan->loan_amount,
            'transaction_id' => $request->transaction_id,
            'date_payed' => $request->date_payed,
            'channel' => $request->channel,
            'payment_type_id' => "2",
        ]);
        $loan->update([
            "has_lp_fee" => true,
            "disbursed" => true,
            "disbursement_date" => $request->date_payed,
            "end_date" => Carbon::parse($request->date_payed)->addDays($loan->product()->first()->duration),
            "disbursed_by" => Mrequest::where('loan_id', $loan->id)->first()->requested_by
        ]);


        //check if registration payment is more than required
        $reg = Regpayment::where('customer_id', $ln->customer_id)->first();
        //$settings = Setting::first();
        if ($reg) {
            //meaning the registration is greater than required so put the extra in loan processing fee
            if ($reg->amount > $settings->registration_fee) {
                //balance after registration
                $bal = $reg->amount - $settings->registration_fee;
                //meaning the remaining balance is greater than loan processing fee
                if ($bal > $settings->loan_processing_fee) {
                    $processing = Payment::create([
                        'payment_type_id' => 3,
                        'loan_id' => $ln->id,
                        'date_payed' => Carbon::now(),//$reg->date_payed,
                        'transaction_id' => $reg->transaction_id,
                        'channel' => 'MPESA',
                        'amount' => $settings->loan_processing_fee
                    ]);

                    //then add the remaining to the loan settlement
                    $rem = $bal - $settings->loan_processing_fee;

                    if ($rem > $ln->balance){
                        $remainda = $rem - $ln->balance;



                        $settlement = Payment::create([
                            'payment_type_id' => 1,
                            'loan_id' => $ln->id,
                            'date_payed' => Carbon::now(),//$reg->date_payed,
                            'transaction_id' => $reg->transaction_id,
                            'channel' => 'MPESA',
                            'amount' => $ln->balance
                        ]);

                        $ln->update(['settled' => true]);
                    }
                    else{
                        $settlement = Payment::create([
                            'payment_type_id' => 1,
                            'loan_id' => $ln->id,
                            'date_payed' => Carbon::now(),//$reg->date_payed,
                            'transaction_id' => $reg->transaction_id,
                            'channel' => 'MPESA',
                            'amount' => $rem
                        ]);
                        $remainda = 0;
                    }

                    //add the amount to current installment being paid
                    $handle_installments = new MpesaPaymentController();
                    $prices = $handle_installments->handle_installments($ln, $rem);


                } //amount remaining is not greater than loan processing fee
                else {
                    $processing = Payment::create([
                        'payment_type_id' => 3,
                        'loan_id' => $ln->id,
                        'date_payed' => Carbon::now(),//$reg->date_payed,
                        'transaction_id' => $reg->transaction_id,
                        'channel' => 'MPESA',
                        'amount' => $bal
                    ]);

                    $remainda = 0;

                }


                $reg->update([
                    'amount' => $settings->registration_fee + $remainda
                ]);

            }
        }

        $mrequest = Mrequest::where(['loan_id' => decrypt($request->id), 'settled' => false])->get();
        foreach ($mrequest as $M) {
            $M->update(['settled' => true]);

        }

        return back()->with('success', 'successfully reconciled the loan');


    }


    /*****************************************check net cash****************************/

    public function check_netcashinflow($branch)
    {


        $branch = Branch::find($branch);
        $total_loan_collections = $branch->total_loan_collections(date('Y'), date('m'));
        $total_expenses = $branch->total_expenses(date('Y'), date('m'));;
        $getTotalLoanDisbursement = $branch->getTotalLoanDisbursement(date('Y'), date('m'));
        $total_processing_fee = $branch->total_processing_fee(date('Y'), date('m'));
        $total_registration_fee = $branch->total_registration_fee(date('Y'), date('m'));
        $balance_bd = $branch->balance_bd(date('Y'), date('m'));
        $investments = $branch->investments()->whereYear('date_payed', Carbon::now())->whereMonth('date_payed', Carbon::now())->sum('amount');

        $this->data['total_cash_outflows'] = $total_expenses + $getTotalLoanDisbursement;


        $this->data['total_cash_inflows'] = $total_loan_collections + $total_processing_fee + $total_registration_fee + $balance_bd + $investments;
        $this->data['net_cash_inflows'] = $this->data['total_cash_inflows'] - $this->data['total_cash_outflows'];

        return $this->data['net_cash_inflows'];


    }


}
