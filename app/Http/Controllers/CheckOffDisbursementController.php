<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\CheckOffEmployeeSms;
use App\models\CheckOffLoan;
use App\models\CheckOffMpesaDisbursementRequest;
use App\models\CheckOffMpesaDisbursementResponse;
use App\models\CheckOffMpesaDisbursementTransaction;
use App\models\Msetting;
use App\models\Setting;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CheckOffDisbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['title'] = "Check Off Loan Disbursement";
        $this->data['sub_title'] = "List of all Advance Loans yet to be disbursed";
        return view('pages.check-off.loans.disburse', $this->data);
    }

    /**
     * use this function to generate a live token
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function generateLiveToken()
    {
        $settings = Msetting::first();

        $consumer_key = decrypt($settings->Consumer_Key);
        $consumer_secret = decrypt($settings->Consumer_Secret);
        //  dd($consumer_key, $consumer_secret);


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
        //dd($res);

        //$res2 = $res->getBody()->getContents();
        $res2 = (string)$res->getBody();
        $obj = json_decode((string)$res->getBody());
        $token = $obj->access_token;
        return $token;


    }

    /**
     * use this function to generate a sandbox token
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'loan_id'=>'required|exists:check_off_loans,id',
            ]);
        if ($validator->fails()) {
            return Redirect::back()->with('error', $validator->errors()->first());
        }
        $loan = CheckOffLoan::query()->with('employee')->find($request->get('loan_id'));
        $loan->update([
            "disbursed_by" => auth()->id(),
        ]);
        $employee = $loan->employee;
        $processing_fee = Setting::first()->lp_fee ?? 500;
        if ($loan->loan_amount > $processing_fee){
            $disbursed_amount = $loan->loan_amount - $processing_fee;
        } else {
            $disbursed_amount = $loan->loan_amount;
        }
        if ($loan->disbursed){
            return Redirect::back()->with('error', 'The selected Loan has already been disbursed');
        }
        if ($loan->mpesa_disbursement_request()->exists()) {
            return Redirect::back()->with('error', 'The selected Loan already has a disbursement request');
        }
        $environment = config('app.mpesa_environment');
        if ($environment == "live") {
            $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
            $token = self::generateLiveToken();
            $phone = '254' . substr($employee->phone_number, -9);;
            $msetting = Msetting::first();
            $InitiatorName = decrypt($msetting->InitiatorName);
            $PartyA = decrypt($msetting->paybill);
            $SecurityCredential = decrypt($msetting->SecurityCredential);
            $CommandID = "BusinessPayment";
            $Amount = $disbursed_amount;
            $PartyB = $phone;
        } elseif ($environment == "sandbox") {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
            $token = self::generateSandBoxToken();
            $phone = "254725730055";
            $InitiatorName = "testapi0321";
            $SecurityCredential = "Yb63VpkEaLbrtJaR3IhElVENIcJ9k1k7PaMa+T91wBymrzNHK4zllt5cNSn/Pgz89ZqHUbU1EIqrVlKiffBQlR8YAgvJFsfDN0VdzWtPu/0YpVFfbWBKkPj+mBqUgVZ01X4goa0vGi7YjaysniMG5b1zvMFN2rhJr03A0TN1pK0+c9/k3pJzG0Vpur0/gK/dmHjrVo2wZbaTdtU0FmRza3NMNpf/B2u0HT7mXsZkWsnr9C1Rqo+6aJX2Dd7LUkSpkebAC010sGvrEaa3SCMXgc6TOCIZjJPgg9jzT70tqQ5SZ84HPjdKl9/MxGhS7n+lyAkFFlQGxSJH380QRfmRyA==";
            $CommandID = "BusinessPayment";
            $Amount = $disbursed_amount;
            $PartyA = "600321";
            $PartyB = "254708374149";
        } else {
            return Redirect::back()->with('error', 'Invalid MPESA Environment');
        }
        $remarks = "CHECKOFF LOAN: $loan->id";
        $queue_timeout_url = route('check-off-disbursement.timeout');
        $result_url = route('check-off-disbursement.result');
        $occasion = $remarks;
        try {
            $client = new Client();
            $send_request = $client->request('post', $url, [
                'verify'=>false,
                'http_errors' => false,
                'headers'=>[
                    'Authorization'=> 'Bearer '.$token,
                    'Content-Type'=> 'application/json'
                ],
                'body' => json_encode([
                    'InitiatorName'=>$InitiatorName,
                    'SecurityCredential'=>$SecurityCredential,
                    'CommandID'=>$CommandID,
                    'Amount'=>$Amount,
                    'PartyA'=>$PartyA,
                    'PartyB'=>$PartyB,
                    'Remarks'=>$remarks,
                    'QueueTimeOutURL'=>$queue_timeout_url,
                    'ResultURL'=>$result_url,
                    'Occasion'=>$occasion
                ])
            ]);
            $obj = json_decode((string)$send_request->getBody());
            if (isset($obj->ResponseCode)){
                $disbursement_request = new CheckOffMpesaDisbursementRequest();
                $disbursement_request->loan_id = $loan->id;
                $disbursement_request->requested_by = Auth::id();
                if ($obj->ResponseCode == 0) {
                    $disbursement_request->ResponseDescription = $obj->ResponseDescription;
                    $disbursement_request->ResponseCode = $obj->ResponseCode;
                    $disbursement_request->OriginatorConversationID = $obj->OriginatorConversationID;
                    $disbursement_request->ConversationID = $obj->ConversationID;
                    $disbursement_request->issued = false;
                    $disbursement_request->response = $send_request->getBody();
                    $disbursement_request->save();
                    //Log::info("response received from disbursement post =>".(string)$send_request->getBody());
                    return Redirect::back()->with('success', 'Successfully initiated payment request. Notification SMS will be sent once complete');
                }
                else {
                    $disbursement_request->ResponseDescription = $obj->ResponseDescription ?? null;
                    $disbursement_request->ResponseCode = $obj->ResponseCode ?? null;
                    $disbursement_request->OriginatorConversationID = $obj->OriginatorConversationID ?? null;
                    $disbursement_request->ConversationID = $obj->ConversationID ?? null;
                    $disbursement_request->issued = false;
                    $disbursement_request->response = $send_request->getBody();
                    $disbursement_request->save();
                    Log::info("failed response received from disbursement post =>".(string)$send_request->getBody());
                    return Redirect::back()->with('warning', 'Could not complete request at this time. Please again later');
                }
            } else {
                Log::info("failed response received from disbursement post =>".(string)$send_request->getBody());
                return Redirect::back()->with('error', 'Could not complete request at this time. Please again later');
            }

        } catch (BadResponseException $exception) {
            Log::error("guzzle exception => ". (string)$exception->getResponse()->getBody()->getContents());
            return Redirect::back()->with('error', 'There seems to be an error connecting to the MPESA API, Try again later');
        }
    }

    public function data(){
        $lo = CheckOffLoan::query()
            ->with('employee.employer')
            ->with('product:id,name')
            ->where(['approved' => true, 'disbursed'=>false, 'settled' => false , 'rejected' => false])
            ->select('check_off_loans.*');
        return DataTables::of($lo)
            ->addColumn('full_name', function ($lo) {
                return $lo->employee->first_name . ' ' . $lo->employee->last_name;
            })
            ->addColumn('action', function ($lo) {
                return '<div class="btn-group text-center">
                                                <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                            <li><a href="#" onclick="event.preventDefault(); document.getElementById('. $lo->id .').submit();"><i class="feather icon-briefcase text-success"></i> Disburse </a></li>
                                                        </ul>
                                        </div>
                                        <form id="'. $lo->id .'" action="'.route('check-off-loans-disbursement.store').'"method="POST" style="display: none;">
                                        <input type="hidden" value="'. $lo->id .'" name="loan_id"/>
                                            '.csrf_field().'
                                        </form>
                                        ';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     *
     * Callback url that receives responses from Safaricom
     *
     * @param \Illuminate\Http\Request $request
     *
     */
    public function result(Request $request)
    {
        $callbackJsonData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJsonData);
        $resultCode = $callbackData->Result->ResultCode;
        $resultDesc = $callbackData->Result->ResultDesc;
        $originatorConversationID = $callbackData->Result->OriginatorConversationID;
        $conversationID = $callbackData->Result->ConversationID;
        $transactionID = $callbackData->Result->TransactionID;
        $mpesa_disbursement_request = CheckOffMpesaDisbursementRequest::query()
            ->where('OriginatorConversationID','=', $originatorConversationID)
            ->first();
        $loan = CheckOffLoan::query()->with(['employee'])->find($mpesa_disbursement_request->loan_id);
        $employee = $loan->employee;
        //if the disbursement was successful
        if ($resultCode == 0) {
            $TransactionAmount = $callbackData->Result->ResultParameters->ResultParameter[0]->Value;
            $TransactionReceipt = $callbackData->Result->ResultParameters->ResultParameter[1]->Value;
            $B2CRecipientIsRegisteredCustomer = $callbackData->Result->ResultParameters->ResultParameter[2]->Value;
            $B2CChargesPaidAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[3]->Value;
            $ReceiverPartyPublicName = $callbackData->Result->ResultParameters->ResultParameter[4]->Value;
            $TransactionCompletedDateTime = $callbackData->Result->ResultParameters->ResultParameter[5]->Value;
            $B2CUtilityAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[6]->Value;
            $B2CWorkingAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[7]->Value;
            $TransactionCompletedDateTime = now(); //Carbon::parse($TransactionCompletedDateTime)->format('Y-m-d H:i:s');

            $result = [
                'ResultCode' => $resultCode,
                'ResultDesc' => $resultDesc,
                'OriginatorConversationID' => $originatorConversationID,
                'ConversationID' => $conversationID,
                'TransactionID' => $transactionID,
                'TransactionAmount' => $TransactionAmount,
                'TransactionReceipt' => $TransactionReceipt,
                'B2CRecipientIsRegisteredCustomer' => $B2CRecipientIsRegisteredCustomer,
                'B2CChargesPaidAccountAvailableFunds' => $B2CChargesPaidAccountAvailableFunds,
                'ReceiverPartyPublicName' => $ReceiverPartyPublicName,
                'TransactionCompletedDateTime' => $TransactionCompletedDateTime,
                'B2CUtilityAccountAvailableFunds' => $B2CUtilityAccountAvailableFunds,
                'B2CWorkingAccountAvailableFunds' => $B2CWorkingAccountAvailableFunds,
                'issued' => true,
                'loan_id' => $loan->id,
                'response'=>$callbackJsonData
            ];
        }
        //else the disbursement failed
        else {
            $result = [
                'ResultCode' => $resultCode,
                'ResultDesc' => $resultDesc,
                'OriginatorConversationID' => $originatorConversationID,
                'ConversationID' => $conversationID,
                'TransactionID' => $transactionID,
                'issued' => false,
                'loan_id' => $loan->id,
                'response'=>$callbackJsonData
            ];
        }

        $mpesa_disbursement_response = CheckOffMpesaDisbursementResponse::query()
            ->where('OriginatorConversationID', '=', $originatorConversationID)
            ->first();
        if (!$mpesa_disbursement_response) {
            //mark request as complete
            $mpesa_disbursement_request->update(['issued' => true]);
            //store entire response
            $disbursement_response = CheckOffMpesaDisbursementResponse::query()
                ->create($result);

            if ($disbursement_response->issued) {
                $transaction = new CheckOffMpesaDisbursementTransaction();
                $transaction->loan_id = $loan->id;
                $transaction->transaction_receipt = $TransactionReceipt;
                $transaction->amount = $TransactionAmount;
                $transaction->channel = "MPESA-B2C";
                $transaction->disbursed_at = now();
                $transaction->save();

                $loan->update([
                    "disbursed" => true,
                ]);

                //notify employee
                $message = "Dear ".$employee->full_name.", your LITSA CREDIT Advance loan of Ksh. ".number_format($transaction->amount, 2)." has been processed successfully. You will receive an MPESA confirmation message shortly. MPESA REF: $transaction->transaction_receipt";

                dispatch(new Sms(
                    $employee->phone_number, $message, null, false
                ));
                CheckOffEmployeeSms::query()->create([
                    'employee_id' => $employee->id,
                    'sms' => $message,
                    'phone_number' => $employee->phone_number
                ]);

                //update admin and accountant
                $admin_users = ["+254711591065"];
                $user = User::first();
                foreach ($admin_users as $admin_user)
                {
                    $message = "Checkoff Loan Disbursement Notification".PHP_EOL."Ksh. ".number_format($transaction->amount, 2)." has successfully been disbursed to $employee->full_name - $employee->phone_number at ".now()->format('d-m-Y H:i:s').". MPESA REF: $transaction->transaction_receipt";
                    dispatch(new Sms(
                        $admin_user, $message, $user, true
                    ));
                }

            }
        }
        //terminate transaction
        return $this->finishTransaction($mpesa_disbursement_response->issued);
    }
    /**
     *
     * Timeout url that receives responses from Safaricom
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function timeout(Request $request)
    {
        $callbackJsonData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJsonData);
        $resultCode = $callbackData->Result->ResultCode;
        $resultDesc = $callbackData->Result->ResultDesc;
        $originatorConversationID = $callbackData->Result->OriginatorConversationID;
        $conversationID = $callbackData->Result->ConversationID;
        $transactionID = $callbackData->Result->TransactionID;
        $mpesa_disbursement_request = CheckOffMpesaDisbursementRequest::query()
            ->where('OriginatorConversationID','=', $originatorConversationID)
            ->first();
        $loan = CheckOffLoan::query()->with(['employee'])->find($mpesa_disbursement_request->loan_id);
        $employee = $loan->employee;
        $result = [
            'ResultCode' => $resultCode,
            'ResultDesc' => $resultDesc,
            'OriginatorConversationID' => $originatorConversationID,
            'ConversationID' => $conversationID,
            'TransactionID' => $transactionID,
            'issued' => false,
            'loan_id' => $loan->id,
            'json'=>$callbackJsonData
        ];
        CheckOffMpesaDisbursementResponse::query()->create($result);
        Log::info("timeout response received on mpesa timeout url => ".(string)$callbackJsonData);
        return $this->finishTransaction(false);
    }

    public function finishTransaction($issued)
    {
        if ($issued === true) {
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
}
