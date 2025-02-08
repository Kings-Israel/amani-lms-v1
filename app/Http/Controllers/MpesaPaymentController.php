<?php

namespace App\Http\Controllers;

use AfricasTalking\SDK\AfricasTalking;
use App\CustomerInteractionCategory;
use App\Jobs\Sms;
use App\models\Arrear;
use App\models\Branch;
use App\models\CheckOffEmployee;
use App\models\CheckOffEmployer;
use App\models\CheckOffLoan;
use App\models\CheckOffPayment;
use App\models\Customer;
use App\models\CustomerInteraction;
use App\models\Group;
use App\models\Installment;
use App\models\Loan;
use App\models\Msetting;
use App\models\Payment;
use App\models\Pre_interaction;
use App\models\Product;
use App\models\Raw_payment;
use App\models\Regpayment;
use App\models\RepaymentMpesaTransaction;
use App\models\Setting;
use App\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\In;

class MpesaPaymentController extends Controller
{

    public static function generateSandBoxToken()
    {
        $consumer_key = 'UQ4YRfJMdnVSaYFPjbDMAcwIuoBF7vNh';
        $consumer_secret = 'qzXdK9F1tXmEA7AW';

        if (!isset($consumer_key) || !isset($consumer_secret)) {
            die("please declare the consumer key and consumer secret as defined in the documentation");
        }

        $client = new Client();
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);

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

    public static function generateLiveToken()
    {
        // $consumer_key = 'ZIeZff4mGVbtL3AACPcfQhPZ1rUZntDG';
        // $consumer_secret = '2aKmVLFyZnTxQlOU';
        $consumer_key = '98H46G7Ohz80hbGpG2hywsCehyq4CxTX';
        $consumer_secret = 'Ffi2iLNYh8cI6Z6L';

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

    /*******************************register urls*********************/
    public static function registerurl()
    {
        $environment = config("services.mpesa.env");

        if ($environment == "live") {
            $url = 'https://api.safaricom.co.ke/mpesa/c2b/v2/registerurl';
            $shortcode = '4123359';
            $token = self::generateLiveToken();
        } else if ($environment == "sandbox") {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v2/registerurl';
            $shortcode = '600999';
            $token = self::generateSandBoxToken();
        } else {
            return json_encode(["Message" => "invalid application status"]);
        }

        try {
            $client = new Client();

            $res = $client->request('POST', $url, [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(
                    [
                        'ShortCode' => $shortcode,
                        'ResponseType' => 'Cancelled',
                        'ConfirmationURL' => route('api.mpesa.confirmation'),
                        'ValidationURL' => route('api.mpesa.validation_url')
                    ]
                )
            ]);

            return response()->json($res, 200);
        } catch(Exception $e) {
            return response()->json($e, 400);
        }

    }

    public function simulate(Request $request)
    {
        $register = self::registerurl();

        $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';
        $token = self::generateSandBoxToken();
        $BusinessShortCode = "600485";
        if (isset($request->amount)) {
            $amount = $request->amount;
            $phone = $request->phone;
        } else {
            $amount = 200;
            $phone = 254708374149;
        }

        $LipaNaMpesaPasskey = env('lipa_mpesa_key');
        $client = new Client();

        // try {
        $res = $client->request('post', $url, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(
                [
                    //Fill in the request parameters with valid values
                    'ShortCode' => $BusinessShortCode,
                    'CommandID' => 'CustomerPayBillOnline',
                    'Amount' => $amount,
                    'Msisdn' => $phone,
                    'BillRefNumber' => '254708374149'
                    // 'Remark'=> $Remark
                ]
            )
        ]);

        $obj = json_decode((string)$res->getBody());
    }

    public function validation_url()
    {
        $callbackJSONData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJSONData);

        info($callbackJSONData);

        $message = "Dear Customer, Ensure You provide an account number when paying";

        if ($callbackData->BillRefNumber != null) {
            //checkoff paybill check
            if ($callbackData->BusinessShortCode == config('app.checkoff_paybill')) {
                return $this->checkoff_validation($callbackData);
            }

            $customer = Customer::where(['phone' => '254' . substr($callbackData->BillRefNumber, -9)])->first();
            if (!$customer){
                $customer = Customer::where(['phone' => '254' . substr($callbackData->MSISDN, -9)])->first();
            }
            if ($customer) {
                //check if it is a group payment
                $group = Group::where('unique_id', '=', $callbackData->BillRefNumber)->first();
                if ($group){
                    if ($group->status == false){
                        $message = "Dear Customer, the group that uses the account ". $callbackData->BillRefNumber." is suspended and therefore not accepting payments. Please lias with your Loan Officer for more details.";
                        $auser = User::first();
                        $fnd = dispatch(new Sms(
                            '+254' . substr($callbackData->MSISDN, -9), $message, $auser, false
                        ));
                        return response()->json([
                            "ResultCode" => 1,
                            "ResultDesc" => "Rejected",
                        ]);
                    }
                    if ($group->customers()->where('customer_id', $customer->id)->exists()){
                        $branch = Branch::find($customer->branch_id);
                        if ($callbackData->BusinessShortCode == $branch->paybill){
                            return response()->json([
                                "ResultCode" => 0,
                                "ResultDesc" => "Accepted",
                            ]);
                        } else {
                            $message = "Dear Customer. You have used a wrong paybill for payment. Contact your Loan Officer for details. Used Paybill is ".$callbackData->BusinessShortCode. ' Your Branch Paybill is '.$branch->paybill;
                            $auser = User::first();
                            $fnd = dispatch(new Sms(
                                '+254' . substr($callbackData->MSISDN, -9), $message, $auser, false
                            ));
                            return response()->json([
                                "ResultCode" => 1,
                                "ResultDesc" => "Rejected",
                            ]);
                        }
                    } else {
                        $message = "Dear Customer, you are not listed as a member of the group that uses the account ". $callbackData->BillRefNumber.". Please lias with your Loan Officer for more details.";
                        $auser = User::first();
                        $fnd = dispatch(new Sms(
                            '+254' . substr($callbackData->MSISDN, -9), $message, $auser, false
                        ));
                        return response()->json([
                            "ResultCode" => 1,
                            "ResultDesc" => "Rejected",
                        ]);
                    }
                }
                //not a group so back to default
                else{
                    $customer = Customer::where(['phone' => '254' . substr($callbackData->BillRefNumber, -9)])->first();
                    if (!$customer){
                        $message = "Dear Customer, this account ". $callbackData->BillRefNumber." does not exist in our system. Please lias with your Loan Officer for more details.";
                        $auser = User::first();
                        $fnd = dispatch(new Sms(
                            '+254' . substr($callbackData->MSISDN, -9), $message, $auser, false
                        ));
                        return response()->json([
                            "ResultCode" => 1,
                            "ResultDesc" => "Rejected",
                        ]);
                    }
                    $branch = Branch::find($customer->branch_id);
                    if ($callbackData->BusinessShortCode == $branch->paybill){
                        return response()->json([
                            "ResultCode" => 0,
                            "ResultDesc" => "Accepted",
                        ]);
                    } else {
                        $message = "Dear Customer. You have used a wrong paybill for payment. Contact your Loan Officer for details. Used Paybill is ".$callbackData->BusinessShortCode. ' Your Branch Paybill is '.$branch->paybill;
                        $auser = User::first();
                        $fnd = dispatch(new Sms(
                            '+254' . substr($callbackData->MSISDN, -9), $message, $auser, false
                        ));

                        return response()->json([
                            "ResultCode" => 1,
                            "ResultDesc" => "Rejected"
                        ]);
                    }
                }
            } else {
                $message = "Dear Customer, this account ". $callbackData->BillRefNumber." does not exist in our system. Please lias with your Loan Officer for more details.";

                $auser = User::first();

                $fnd = dispatch(new Sms(
                    '+254' . substr($callbackData->MSISDN, -9), $message, $auser, false
                ));

                return response()->json([
                    "ResultCode" => 1,
                    "ResultDesc" => "Rejected"
                ]);
            }
        }

        $auser = User::first();
        $fnd = dispatch(new Sms('+254' . substr($callbackData->MSISDN, -9), $message, $auser, false));

        return response()->json([
            "ResultCode" => 1,
            "ResultDesc" => "Rejected"
        ]);
    }

    private function checkoff_validation($callbackData)
    {
        $BillRefNumber = Str::upper($callbackData->BillRefNumber);
        $employer = CheckOffEmployer::query()->where('code', '=', $BillRefNumber)->first();
        if ($employer){
            $employee_phone_number = '254' . substr($callbackData->MSISDN, -9);
            $employee = CheckOffEmployee::query()->where([
                'employer_id' => $employer->id,
                'phone_number' => $employee_phone_number
            ])->first();
            if ($employee) {
                $unsettled_loan = CheckOffLoan::query()->where([
                    'employee_id' => $employee->id,
                    'approved' => true,
                    'settled' => false,
                    'disbursed' => true,
                ])->exists();
                if($unsettled_loan){
                    return response()->json([
                        "ResultCode" => 0,
                        "ResultDesc" => "Accepted",
                    ]);
                }
                //loan not found
                else {
                    $message = "Hello $callbackData->FirstName, we are unable to find an unsettled LITSA CREDIT Advance Loan attached to your account. If issue persists, kindly contact our customer care on " . config('app.customer_care_contact');
                }
            }
            //employee not found
            else {
                $message = "Hello $callbackData->FirstName, we are unable to find a LITSA CREDIT Advance Employee attached to the provided phone number $callbackData->MSISDN under the Institution $BillRefNumber. Kindly make the payment using a registered phone number and a valid corresponding Institution Code as the Account Number. If issue persists, kindly contact our customer care on " . config('app.customer_care_contact');
            }
        }
        //employer not found
        else {
            $message = "Hello $callbackData->FirstName, we are unable to find a LITSA CREDIT Advance Employer attached to the provided institution code $BillRefNumber. Kindly make the payment using a registered phone number and your assigned Institution Code as the Account Number. If issue persists, kindly contact our customer care on " . config('app.customer_care_contact');
        }

        if ($message != ''){
            $auser = User::first();
            $fnd = dispatch(new Sms(
                '+254' . substr($callbackData->MSISDN, -9), $message, $auser, false
            ));
        }

        return response()->json([
            "ResultCode" => 1,
            "ResultDesc" => "Rejected"
        ]);
    }

    //mpesa confirmation
    public function confirmation()
    {
        $callbackJSONData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJSONData);

        info($callbackJSONData);

        if (strlen(trim($callbackData->BillRefNumber)) == 9) {
            $customer = Customer::where('phone', '254'.trim($callbackData->BillRefNumber))->first();
            $cus = Customer::where('phone', '254'.trim($callbackData->BillRefNumber))->first();
        } else {
            $customer = Customer::where('phone', '254'.substr(trim($callbackData->BillRefNumber), -9))->first();
            $cus = Customer::where('phone', '254'.substr(trim($callbackData->BillRefNumber), -9))->first();
        }

        if ($customer) {
            $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();
            $lon = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

            $result = [
                "amount" => $callbackData->TransAmount,
                "mpesaReceiptNumber" => $callbackData->TransID,
                "customer_id" => $customer->id,
                "transactionDate" => Carbon::now('Africa/Nairobi'),
                "phoneNumber" => $callbackData->MSISDN
            ];

            $setting = Setting::first();
            if ($loan) {
                $reg = Regpayment::where('customer_id', $customer->id)->first();
                if ($reg) {
                    if ($reg->amount > $setting->registration_fee) {
                        //Registration amount is more than set registration
                        $remaining_reg = (int)$reg->amount - (int)$setting->registration_fee;

                        $reg->update([
                            "amount" => (int)$setting->registration_fee,
                            "transaction_id" => $callbackData->TransID,
                        ]);

                        //remaider after reg
                        $remaiderafter_reg = (int)$callbackData->TransAmount + $remaining_reg;

                        $this->rem_after_reg($callbackData, $customer, $remaiderafter_reg);
                    } else if ($reg->amount == $setting->registration_fee) {
                        // Check if customer has another settled loan
                        $loans = Loan::where(['customer_id' => $customer->id, 'settled' => true])->count();
                        if ($loans <= 0) {
                            if ((int) $callbackData->TransAmount < (int) $loan->balance) {
                                //amount remaining is less than or equal to loan amount
                                Payment::create([
                                    'loan_id' => $loan->id,
                                    'date_payed' => Carbon::now("Africa/Nairobi"),
                                    'transaction_id' => $callbackData->TransID,
                                    'amount' => $callbackData->TransAmount,
                                    'channel' => "MPESA",
                                    'payment_type_id' => 1,
                                ]);
                            } else {
                                //amount remaining is greator than loan balance so put the remaining in reg fee account
                                $over_pay = (int)$callbackData->TransAmount - (int)$loan->balance;

                                Payment::create([
                                    'loan_id' => $loan->id,
                                    'date_payed' => Carbon::now("Africa/Nairobi"),
                                    'transaction_id' => $callbackData->TransID,
                                    'amount' => $loan->balance,
                                    'channel' => "MPESA",
                                    'payment_type_id' => 1,
                                ]);

                                //set loan as paid
                                Loan::find($loan->id)->update(['settled' => true]);
                                $reg2 = Regpayment::where('customer_id', $customer->id)->first();
                                $reg2->update([
                                    "amount" => $reg2->amount + $over_pay,
                                    "transaction_id" => $callbackData->TransID,
                                ]);
                            }

                            $this->handle_installments($loan, $callbackData->TransAmount);
                        } else {
                            /*************************if paid registration amount equal to set registration fee*****************/
                            $reg_fee = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 3])->sum('amount');
                            // Put processing fee into consideration
                            if ($reg_fee >= (int)$setting->loan_processing_fee) {
                                //meaning the loan processing fee has bee paid so continue in settling the loan
                                if ($callbackData->TransAmount < $loan->balance) {
                                    //amount remaining is less than or equal to loan amount
                                    Payment::create([
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now("Africa/Nairobi"),
                                        'transaction_id' => $callbackData->TransID,
                                        'amount' => $callbackData->TransAmount,
                                        'channel' => "MPESA",
                                        'payment_type_id' => 1,
                                    ]);
                                } else {
                                    //amount remaining is greator than loan balance so put the remaining in reg fee account
                                    $over_pay = $callbackData->TransAmount - $loan->balance;

                                    Payment::create([
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now("Africa/Nairobi"),
                                        'transaction_id' => $callbackData->TransID,
                                        'amount' => $loan->balance,
                                        'channel' => "MPESA",
                                        'payment_type_id' => 1,
                                    ]);

                                    //set loan as paid
                                    Loan::find($loan->id)->update(['settled' => true]);
                                    $reg2 = Regpayment::where('customer_id', $customer->id)->first();
                                    $reg2->update([
                                       // 'date_payed' => Carbon::now('Africa/Nairobi'),
                                        "amount" => $reg2->amount + $over_pay,
                                        "transaction_id" => $callbackData->TransID,
                                    ]);
                                }

                                $this->handle_installments($loan, $callbackData->TransAmount);
                            } else {
                                if ((int) $callbackData->TransAmount <= ((int) $setting->loan_processing_fee - $reg_fee)) {
                                    Payment::create([
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now("Africa/Nairobi"),
                                        'transaction_id' => $callbackData->TransID,
                                        'amount' => (int) $callbackData->TransAmount,
                                        'channel' => "MPESA",
                                        'payment_type_id' => 3,
                                    ]);
                                } else {
                                    $over_processing_pay = $callbackData->TransAmount - ((int) $setting->loan_processing_fee - $reg_fee);

                                    Payment::create([
                                        'loan_id' => $loan->id,
                                        'date_payed' => Carbon::now("Africa/Nairobi"),
                                        'transaction_id' => $callbackData->TransID,
                                        'amount' => (int) $setting->loan_processing_fee - $reg_fee,
                                        'channel' => "MPESA",
                                        'payment_type_id' => 3,
                                    ]);

                                    if ($over_processing_pay < $loan->balance) {
                                        //amount remaining is less than or equal to loan amount
                                        Payment::create([
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now("Africa/Nairobi"),
                                            'transaction_id' => $callbackData->TransID,
                                            'amount' => $over_processing_pay,
                                            'channel' => "MPESA",
                                            'payment_type_id' => 1,
                                        ]);
                                    } else {
                                        //amount remaining is greator than loan balance so put the remaining in reg fee account
                                        $over_pay = $over_processing_pay - $loan->balance;

                                        Payment::create([
                                            'loan_id' => $loan->id,
                                            'date_payed' => Carbon::now("Africa/Nairobi"),
                                            'transaction_id' => $callbackData->TransID,
                                            'amount' => $loan->balance,
                                            'channel' => "MPESA",
                                            'payment_type_id' => 1,
                                        ]);

                                        //set loan as paid
                                        Loan::find($loan->id)->update(['settled' => true]);
                                        $reg2 = Regpayment::where('customer_id', $customer->id)->first();
                                        $reg2->update([
                                           // 'date_payed' => Carbon::now('Africa/Nairobi'),
                                            "amount" => $reg2->amount + $over_pay,
                                            "transaction_id" => $callbackData->TransID,
                                        ]);
                                    }

                                    $this->handle_installments($loan, $over_processing_pay);
                                }
                            }
                        }
                    } else {
                        /*************************if paid registration amount is less than set registration fee*****************/
                        $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;

                        if ((int) $callbackData->TransAmount <= $remaining_reg) {
                            $reg->update([
                                'date_payed' => Carbon::now('Africa/Nairobi'),
                                "amount" => (int)$reg->amount + $callbackData->TransAmount,
                                "transaction_id" => $callbackData->TransID,
                            ]);
                        } else {
                            //more amount than registration
                            $reg->update([
                                'date_payed' => Carbon::now('Africa/Nairobi'),
                                "amount" => (int)$setting->registration_fee,
                                "transaction_id" => $callbackData->TransID,
                            ]);

                            //remaider after reg
                            $remaiderafter_reg = (int)$callbackData->TransAmount - $remaining_reg;
                            $this->rem_after_reg($callbackData, $customer, $remaiderafter_reg);
                        }
                    }
                }
                /****************************************very unlickely to happen that customer have approved loan and he has not paid registration fee *********************/
                else {
                    if ($callbackData->TransAmount <= (int)$setting->registration_fee) {
                        Regpayment::create([
                            'customer_id' => $customer->id,
                            'date_payed' => Carbon::now('Africa/Nairobi'),
                            "amount" => $callbackData->TransAmount,
                            "transaction_id" => $callbackData->TransID,
                            "channel" => "MPESA",
                        ]);
                    } else {
                        //more amount than registration
                        $remaining_reg = (int)$callbackData->TransAmount - (int)$setting->registration_fee;

                        Regpayment::create([
                            'customer_id' => $customer->id,
                            'date_payed' => Carbon::now('Africa/Nairobi'),
                            "amount" => (int)$setting->registration_fee,
                            "transaction_id" => $callbackData->TransID,
                            "channel" => "MPESA",
                        ]);

                        //remaider after reg
                        $remaiderafter_reg = $remaining_reg;
                        $this->rem_after_reg($callbackData, $customer, $remaiderafter_reg);
                    }
                }

                if ($lon->balance > 0){
                    $days = now()->diffInDays(Carbon::parse($loan->end_date));
                    $end_date = Carbon::parse($loan->end_date)->format('d M Y');
                    // $amessage = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Payment of Ksh. ' . $callbackData->TransAmount . ' paid through ' . $callbackData->BillRefNumber . ' received. Outstanding loan balance Ksh. ' . number_format($lon->balance).', days Remaining on the loan is '.$days.' and final payment date is '.$end_date;
                    // $amessage = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Payment of Ksh. ' . $callbackData->TransAmount . ' paid through ' . $callbackData->BillRefNumber . ' received. Outstanding loan balance Ksh. ' . number_format($lon->balance).', days Remaining on the loan is '.$days;
                    $amessage = 'Dear ' . $cus->fname . ',' . "\r\n" .
                                'Payment of Ksh. ' . $callbackData->TransAmount .
                                ' paid through ' . $callbackData->BillRefNumber .
                                ' received. Outstanding loan balance Ksh. ' .
                                number_format($lon->balance) . '.';
                } else {
                    $amessage = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Payment of Ksh. ' . $callbackData->TransAmount . ' paid through ' . $callbackData->BillRefNumber . ' received. Outstanding loan balance Ksh. '. number_format($lon->balance);
                }
            } else {
                //get the message
                //meaning he has no active loan so check if registration fee is paid
                $reg = Regpayment::where('customer_id', $customer->id)->first();
                if ($reg) {
                    $reg->update([
                        "amount" => $callbackData->TransAmount + $reg->amount,
                        "transaction_id" => $callbackData->TransID,
                    ]);
                    // if ((int) $reg->amount < (int) $setting->registration_fee) {
                    // } else {
                    //     if (strlen($callbackData->BillRefNumber) == 9) {
                    //         $account_number = '254'.trim($callbackData->BillRefNumber);
                    //     } else {
                    //         $account_number = '254'.substr(trim($callbackData->BillRefNumber), -9);
                    //     }
                    //     $result2 = [
                    //         "amount" => $callbackData->TransAmount,
                    //         "mpesaReceiptNumber" => $callbackData->TransID,
                    //         "customer" => $callbackData->FirstName,
                    //         "phoneNumber" => $callbackData->MSISDN,
                    //         "BusinessShortCode" => $callbackData->BusinessShortCode,
                    //         "account_number" => $account_number,
                    //     ];
                    //     $fnd = Raw_payment::where('mpesaReceiptNumber', $callbackData->TransID)->first();
                    //     if (!$fnd){
                    //         Raw_payment::create($result2);
                    //     }
                    // }
                } else {
                    Regpayment::create([
                        'customer_id' => $customer->id,
                        'date_payed' => Carbon::now('Africa/Nairobi'),
                        "amount" => $callbackData->TransAmount,
                        "transaction_id" => $callbackData->TransID,
                        "channel" => "MPESA",
                    ]);
                }
                $amessage = 'Dear ' . $cus->fname . ',' . "\r\n" . 'Payment of Ksh. ' . $callbackData->TransAmount . ' paid through ' . $callbackData->MSISDN . ' received.';
            }

            RepaymentMpesaTransaction::create($result);

            $branch_id = Branch::whereIn('bname', ['Bungoma', 'Homabay', 'Siaya', 'Busia', 'Migori', 'Kakamega'])->pluck('id');

            // $field_agents = [221, 230];

            // $exclude_users = ['254705639862', '254718738033', '254748695270', '254721919438', '254798690411', '254705554325', '254748750941', '254723220302', '254743166101', '254794093162', '254729482251', '254797527218', '254748367338', '254704289993', '254759488549', '254757913019'];

            // $field_agents = [221, 230, 252, 263, 219, 271, 245, 273, 244, 276, 268, 279, 274, 280, 283, 287, 221, 269, 233, 253, 251, 272, 270, 186, 237, 224, 281];

            $exclude_users = [
                '254729657866',
                '254748004625',
                '254743997001',
                '254704887488',
                '254714199569',
                '254712696779',
                '254708312495',
                '254703333405',
                '254705639862',
                '254718738033',
                '254748695270',
                '254721919438',
                '254798690411',
                '254705554325',
                '254748750941',
                '254723220302',
                '254743166101',
                '254794093162',
                '254729482251',
                '254797527218',
                '254748367338',
                '254704289993',
                '254759488549',
                '254757913019',
                '254706067688',
                '254712647370',
                '254708119634',
                '254746299397',
                '254708729768',
                '254799922672',
                '254114321669',
                '254792286108',
                '254726568594',
                '254702984352',
                '254759448909',
                '254111664191',
                '254725584384',
                '254791835504',
                '254794093162',
                '254797527218',
                '254757913019',
                '254705552874',
                '254743166101',
                '254748750941',
                '254759716752',
                '254759783485',
                '254769609643',
                '254741618857',
                '254110277088',
                '254112458461',
                '254759249504',
                '254717639709',
                '254705534691',
                '254703404122',
                '254724693706',
                '254719632132',
                '254723893249',
                '254710991913',
                '254757195149',
                '254746274613',
                '254796795144',
                '254743081829',
                '254748440949',
                '254791937696',
                '254708767655',
                '254741634399',
                '254768740493',
                '254741115227',
                '254713674854'
            ];

            if (collect($branch_id)->contains($customer->branch_id) && !collect($exclude_users)->contains($customer->phone)) {
                $aphone = '+254' . substr($cus->phone, -9);
                $auser = $cus;
                $suser_type = false;
                $fnd = dispatch(new Sms(
                    $aphone, $amessage, $auser, $suser_type
                ));
            }
        } else {
            if (strlen($callbackData->BillRefNumber) == 9) {
                $account_number = '254'.trim($callbackData->BillRefNumber);
            } else {
                $account_number = '254'.substr(trim($callbackData->BillRefNumber), -9);
            }
            $result2 = [
                "amount" => $callbackData->TransAmount,
                "mpesaReceiptNumber" => $callbackData->TransID,
                "customer" => $callbackData->FirstName,
                "phoneNumber" => $callbackData->MSISDN,
                "BusinessShortCode" => $callbackData->BusinessShortCode,
                "account_number" => $account_number,
            ];

            $fnd = Raw_payment::where('mpesaReceiptNumber', $callbackData->TransID)->first();
            if (!$fnd){
                $transaction = Raw_payment::create($result2);
            }
        }

        return response()->json([
            'ResultCode' => '0',
            'ResultDesc' => 'Completed',
        ]);
    }

    //remaider after reg
    public function rem_after_reg($callbackData, $customer, $remaiderafter_reg)
    {
        $setting = Setting::first();

        $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

        // Check if customer has another settled loan
        $loans = Loan::where(['customer_id' => $customer->id, 'settled' => true])->count();
        if ($loans >= 1) {
            if ($remaiderafter_reg <= (int)$setting->loan_processing_fee) {
                //pay loan processing fee
                Payment::create([
                    'loan_id' => $loan->id,
                    'date_payed' => Carbon::now("Africa/Nairobi"),
                    'transaction_id' => $callbackData->TransID,
                    'amount' => $remaiderafter_reg,
                    'channel' => "MPESA",
                    'payment_type_id' => 3,
                ]);
            }
            /******************remainder is greater than loan processing fee*******************************/
            else {
                //pay processing fee and the remaining pay the loan
                Payment::create([
                    'loan_id' => $loan->id,
                    'date_payed' => Carbon::now("Africa/Nairobi"),
                    'transaction_id' => $callbackData->TransID,
                    'amount' => (int)$setting->loan_processing_fee,
                    'channel' => "MPESA",
                    'payment_type_id' => 3,
                ]);

                $loan_pay_amount = $remaiderafter_reg - (int)$setting->loan_processing_fee;

                /********************if balance after deducting loan processing fee is less or equal to loan balance**************/
                if ($loan_pay_amount < $loan->balance) {
                    //amount remaining is less than or equal to loan balance
                    Payment::create([
                        'loan_id' => $loan->id,
                        'date_payed' => Carbon::now("Africa/Nairobi"),
                        'transaction_id' => $callbackData->TransID,
                        'amount' => $loan_pay_amount,
                        'channel' => "MPESA",
                        'payment_type_id' => 1,
                    ]);
                }

                if ($loan_pay_amount == $loan->balance) {
                    Payment::create([
                        'loan_id' => $loan->id,
                        'date_payed' => Carbon::now("Africa/Nairobi"),
                        'transaction_id' => $callbackData->TransID,
                        'amount' => $loan_pay_amount,
                        'channel' => "MPESA",
                        'payment_type_id' => 1,
                    ]);
                    Loan::find($loan->id)->update(['settled' => true]);
                } else {
                    //amount remaining is greator than loan amount so put the remaining in reg fee account
                    Payment::create([
                        'loan_id' => $loan->id,
                        'date_payed' => Carbon::now("Africa/Nairobi"),
                        'transaction_id' => $callbackData->TransID,
                        'amount' => (int)$loan->balance,
                        'channel' => "MPESA",
                        'payment_type_id' => 1,
                    ]);

                    //set loan as paid
                    Loan::find($loan->id)->update(['settled' => true]);
                    $over_pay = $loan_pay_amount - $loan->balance;
                    $reg2 = Regpayment::where('customer_id', $customer->id)->first();
                    $reg2->update([
                        "amount" => $reg2->amount + $over_pay,
                        "transaction_id" => $callbackData->TransID,
                    ]);
                }

                $this->handle_installments($loan, $loan_pay_amount);
            }
        } else {
            /********************if balance is less or equal to loan balance**************/
            if ((int)$remaiderafter_reg < $loan->balance) {
                //amount remaining is less than or equal to loan balance
                Payment::create([
                    'loan_id' => $loan->id,
                    'date_payed' => Carbon::now("Africa/Nairobi"),
                    'transaction_id' => $callbackData->TransID,
                    'amount' => $remaiderafter_reg,
                    'channel' => "MPESA",
                    'payment_type_id' => 1,
                ]);
            } elseif ((int)$remaiderafter_reg == $loan->balance) {
                Payment::create([
                    'loan_id' => $loan->id,
                    'date_payed' => Carbon::now("Africa/Nairobi"),
                    'transaction_id' => $callbackData->TransID,
                    'amount' => $remaiderafter_reg,
                    'channel' => "MPESA",
                    'payment_type_id' => 1,
                ]);
                Loan::find($loan->id)->update(['settled' => true]);
            } else {
                //amount remaining is greator than loan amount so put the remaining in reg fee account
                Payment::create([
                    'loan_id' => $loan->id,
                    'date_payed' => Carbon::now("Africa/Nairobi"),
                    'transaction_id' => $callbackData->TransID,
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
                    "transaction_id" => $callbackData->TransID,
                ]);
            }

            $this->handle_installments($loan, $remaiderafter_reg);
        }
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

                if ($rem >= $balance) {
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
                        Log::info('some error happened on handling preinteraction');
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
                            Log::info('some error happened on handling preinteraction on installment balance');
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

    // public function group_confirmation($customer, $group, $callbackData)
    // {
    //     $leader = $customer;
    //     $group_loans = Loan::where(['group_id' => $group->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->get();
    //     $cust_ids = array();
    //     foreach ($group_loans as $loan){
    //         array_push($cust_ids, $loan->customer_id);
    //     }
    //     $members = Customer::whereIn('id', $cust_ids)->get();
    //     $member_names = Customer::whereIn('id', $cust_ids)->get();
    //     $amount = $callbackData->TransAmount;
    //     $divided_amount = $amount / count($members);
    //     $names = array();
    //     foreach ($member_names as $member_name){
    //         $name = $member_name->fname .' '. $member_name->lname;
    //         array_push($names, $name);
    //     }
    //     $reminder_pool = array();
    //     foreach ($members as $member){
    //         if ($member) {
    //             //get loan officer attached to a customer
    //             $lf = User::find($member->field_agent_id);
    //             $loan = Loan::where(['group_id' => $group->id, 'customer_id' => $member->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();
    //             $lon = Loan::where(['group_id' => $group->id, 'customer_id' => $member->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();

    //             $result = [
    //                 "amount" => $divided_amount,
    //                 "mpesaReceiptNumber" => $callbackData->TransID,
    //                 "customer_id" => $member->id,
    //                 "transactionDate" => Carbon::now('Africa/Nairobi'),
    //                 "phoneNumber" => $callbackData->MSISDN
    //             ];
    //             if ($loan) {
    //                 $loan_balance = $loan->balance;
    //                 if ($divided_amount > $loan_balance)
    //                 {
    //                     $reminder = $divided_amount - $loan_balance;
    //                     $divided_amount = $loan_balance;
    //                     array_push($reminder_pool, $reminder);
    //                 }

    //                 //message
    //                 //meaning has an active loan so first if he has paid reg fee
    //                 $reg = Regpayment::where('customer_id', $member->id)->first();
    //                 $setting = Setting::first();
    //                 if ($reg) {
    //                     if ($reg->amount > $setting->registration_fee) {
    //                         //Registration amount is more than set registration
    //                         $remaining_reg = (int)$reg->amount - (int)$setting->registration_fee;
    //                         $reg->update([
    //                             "amount" => (int)$setting->registration_fee,
    //                             "transaction_id" => $callbackData->TransID,
    //                         ]);

    //                         //remaider after reg
    //                         $remaiderafter_reg = (int)$divided_amount + $remaining_reg;
    //                         $this->group_rem_after_reg($callbackData, $member, $remaiderafter_reg, $group);


    //                     } elseif ($reg->amount = $setting->registration_fee) {
    //                         /*************************if paid registration amount equal to set registration fee*****************/
    //                         $reg_fee = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 3])->sum('amount');
    //                         if ($reg_fee >= (int)$setting->loan_processing_fee || $reg_fee == 400) {
    //                             //meaning the loan processing fee has bee paid so continue in settling the loan

    //                             if ($divided_amount < $loan->balance) {
    //                                 //amount remaining is less than or equal to loan amount
    //                                 $pay_loan = Payment::create([
    //                                     'loan_id' => $loan->id,
    //                                     'date_payed' => Carbon::now("Africa/Nairobi"),
    //                                     'transaction_id' => $callbackData->TransID,
    //                                     'amount' => $divided_amount,
    //                                     'channel' => "MPESA",
    //                                     'payment_type_id' => 1,


    //                                 ]);
    //                             } else {
    //                                 //amount remaining is greator than loan balance so put the remaining in reg fee account
    //                                 $over_pay = $divided_amount - $loan->balance;

    //                                 $pay_loan = Payment::create([
    //                                     'loan_id' => $loan->id,
    //                                     'date_payed' => Carbon::now("Africa/Nairobi"),
    //                                     'transaction_id' => $callbackData->TransID,
    //                                     'amount' => $loan->balance,
    //                                     'channel' => "MPESA",
    //                                     'payment_type_id' => 1,
    //                                 ]);

    //                                 //set loan as paid
    //                                 Loan::find($loan->id)->update(['settled' => true]);
    //                                 $reg2 = Regpayment::where('customer_id', $member->id)->first();
    //                                 $add_to_reg = $reg2->update([
    //                                     // 'date_payed' => Carbon::now('Africa/Nairobi'),
    //                                     "amount" => $reg2->amount + $over_pay,
    //                                     "transaction_id" => $callbackData->TransID,
    //                                 ]);
    //                             }
    //                             $this->handle_installments($loan, $divided_amount);
    //                         }
    //                     } else {
    //                         /*************************if paid registration amount is less than set registration fee*****************/
    //                         $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;

    //                         if ($divided_amount <= $remaining_reg) {
    //                             $reg->update([
    //                                 'date_payed' => Carbon::now('Africa/Nairobi'),
    //                                 "amount" => (int)$reg->amount + $divided_amount,
    //                                 "transaction_id" => $callbackData->TransID,
    //                             ]);
    //                         } else {
    //                             //more amount than registration
    //                             $reg->update([
    //                                 'date_payed' => Carbon::now('Africa/Nairobi'),
    //                                 "amount" => (int)$setting->registration_fee,
    //                                 "transaction_id" => $callbackData->TransID,
    //                             ]);

    //                             //remaider after reg
    //                             $remaiderafter_reg = (int)$callbackData->TransAmount - $remaining_reg;
    //                             $this->group_rem_after_reg($callbackData, $customer, $remaiderafter_reg, $group);

    //                         }
    //                     }
    //                 }

    //                 /****************************************very unlickely to happen that customer have approved loan and he has not paid registration fee *********************/
    //                 else {
    //                     if ($divided_amount <= (int)$setting->registration_fee) {
    //                         $regi = Regpayment::create([
    //                             'customer_id' => $customer->id,
    //                             'date_payed' => Carbon::now('Africa/Nairobi'),
    //                             "amount" => $divided_amount,
    //                             "transaction_id" => $callbackData->TransID,
    //                             "channel" => "MPESA",
    //                         ]);
    //                     } else {
    //                         //more amount than registration
    //                         $remaining_reg = (int)$divided_amount - (int)$setting->registration_fee;

    //                         $regi = Regpayment::create([
    //                             'customer_id' => $customer->id,
    //                             'date_payed' => Carbon::now('Africa/Nairobi'),
    //                             "amount" => (int)$setting->registration_fee,
    //                             "transaction_id" => $callbackData->TransID,
    //                             "channel" => "MPESA",
    //                         ]);
    //                         //remaider after reg
    //                         $remaiderafter_reg = $remaining_reg;
    //                         $this->group_rem_after_reg($callbackData, $customer, $remaiderafter_reg, $group);
    //                     }
    //                 }

    //                 if ($lon->balance > 0){
    //                     $amessage = 'Dear ' . $member->fname . ',' . "\r\n" . 'Payment of Ksh. ' . $callbackData->TransAmount . ' paid by your group leader ' . $callbackData->MSISDN . ' received. The amount divided to pay your loan was Ksh. '.$divided_amount.'. Outstanding loan balance is Ksh. ' . $lon->balance;
    //                 } else {
    //                     $amessage = 'Dear ' . $member->fname . ',' . "\r\n" . 'Payment of Ksh. ' . $callbackData->TransAmount . ' paid by your group leader ' . $callbackData->MSISDN . '. The amount divided to pay your loan was Ksh. '.$divided_amount.'. Outstanding loan balance is Ksh. ' . $lon->balance;
    //                 }

    //                 /*
    //                  * Send an update to the debt collector, developer and system admin
    //                  * Conditions: Narok branch, loan has an existing arrear.
    //                  *
    //                 */
    //                 // $arrears = $loan->arrears()->orderBy('amount', 'DESC')->first();
    //                 // if ($arrears){
    //                 //     if($member->branch_id == 1 and $arrears->amount > 0)
    //                 //     {
    //                 //         $contacts = [/*'+254725730055',*/ '+254713172914', '+254731438382'];
    //                 //         foreach ($contacts as $contact){
    //                 //             $suser_type = true;
    //                 //             $amessage = "Arrears Notification".PHP_EOL.$member->fname." ".$member->lname." ".$member->phone." has paid Ksh. ".$callbackData->TransAmount.". Total Loan balance Ksh. ".$loan->balance;
    //                 //             $auser = User::first();
    //                 //             $fnd = dispatch(new Sms(
    //                 //                 $contact, $amessage, $auser, $suser_type
    //                 //             ));
    //                 //         }
    //                 //     }
    //                 // }
    //             } else {
    //                 //get the message
    //                 //meaning he has no active loan so check if registration fee is paid
    //                 $reg = Regpayment::where('customer_id', $member->id)->first();
    //                 if ($reg) {
    //                     $reg->update([
    //                         //'date_payed' => Carbon::now('Africa/Nairobi'),
    //                         "amount" => $divided_amount + $reg->amount,
    //                         "transaction_id" => $callbackData->TransID,
    //                     ]);
    //                 } else {

    //                     $regi = Regpayment::create([
    //                         'customer_id' => $member->id,
    //                         'date_payed' => Carbon::now('Africa/Nairobi'),
    //                         "amount" => $divided_amount,
    //                         "transaction_id" => $callbackData->TransID,
    //                         "channel" => "MPESA",
    //                     ]);
    //                 }
    //                 $amessage = 'Dear ' . $member->fname . ',' . "\r\n" . 'Payment of Ksh. ' . $callbackData->TransAmount . ' paid by your group leader ' . $leader->fname . ' ' . $leader->lname . ' received. The amount divided and added to your account was Ksh. '.$divided_amount;
    //             }
    //             $transaction = RepaymentMpesaTransaction::create($result);

    //             $aphone = '+254' . substr($member->phone, -9);
    //             $auser = $member;
    //             $suser_type = false;
    //             $fnd = dispatch(new Sms(
    //                 $aphone, $amessage, $auser, $suser_type
    //             ));
    //         }
    //     }

    //     //checks reminder pool, finds loan with the biggest balance and makes payment.
    //     if (!empty($reminder_pool)){
    //         $amt = array_sum($reminder_pool);
    //         $arr = [];
    //         $group_loans = Loan::where(['group_id' => $group->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->get();
    //         foreach ($group_loans as $ln){
    //             $bal = $ln->balance;
    //             array_push($arr, array('id'=>$ln->id, 'balance'=>$bal));
    //         }
    //         $bal_value = array_column($arr, 'balance');
    //         array_multisort($bal_value, SORT_DESC, $arr);
    //         $settle = Loan::find($arr[0]['id']);
    //         $cust = Customer::find($settle->customer_id);

    //         $this->group_settle_using_reminder($settle, $cust, $amt, $callbackData, $group);
    //     }

    //     $names = implode(',' , $names);
    //     //notify group leader
    //     $amessage = 'Dear ' . $leader->fname . ',' . "\r\n" . 'Payment of Ksh. ' . $callbackData->TransAmount . ' paid to your group ' . $group->name . '. The group members to which the payment was divided are '.$names.'. ';
    //     $aphone = '+254' . substr($leader->phone, -9);
    //     $auser = $leader;
    //     $suser_type = false;
    //     $fnd = dispatch(new Sms(
    //         $aphone, $amessage, $auser, $suser_type
    //     ));

    //     //saves raw payment data
    //     $result2 = [
    //         "amount" => $callbackData->TransAmount,
    //         "mpesaReceiptNumber" => $callbackData->TransID,
    //         "customer" => $callbackData->FirstName,
    //         "phoneNumber" => $callbackData->MSISDN,
    //         "BusinessShortCode" => $callbackData->BusinessShortCode
    //     ];
    //     $fnd = Raw_payment::where('mpesaReceiptNumber', $callbackData->TransID)->first();
    //     if (!$fnd){
    //         $transaction = Raw_payment::create($result2);
    //     }
    //     return response()->json([
    //         'ResultCode' => '0',
    //         'ResultDesc' => 'Completed',
    //     ]);
    // }

    // public function group_rem_after_reg($callbackData, $member, $remaiderafter_reg, $group)
    // {
    //     $setting = Setting::first();

    //     if ($remaiderafter_reg <= (int)$setting->loan_processing_fee) {
    //         //pay loan processing fee
    //         $loan = Loan::where(['customer_id'=>$member->id, 'group_id'=>$group->id])->first();
    //         $pay_loan_processing_fee = Payment::create([
    //             'loan_id' => $loan->id,
    //             'date_payed' => Carbon::now("Africa/Nairobi"),
    //             'transaction_id' => $callbackData->TransID,
    //             'amount' => $remaiderafter_reg,
    //             'channel' => "MPESA",
    //             'payment_type_id' => 3,
    //         ]);

    //     }
    //     /******************remainder is greater than loan processing fee*******************************/
    //     else {
    //         $loan = Loan::where(['customer_id' => $member->id, 'group_id'=>$group->id, 'settled' => false, 'approved' => true, 'disbursed' => true])->first();
    //         $pay_loan_processing_fee = Payment::create([
    //             'loan_id' => $loan->id,
    //             'date_payed' => Carbon::now("Africa/Nairobi"),
    //             'transaction_id' => $callbackData->TransID,
    //             'amount' => (int)$setting->loan_processing_fee,
    //             'channel' => "MPESA",
    //             'payment_type_id' => 3,
    //         ]);

    //         $loan_pay_amount = $remaiderafter_reg - (int)$setting->loan_processing_fee;

    //         /********************if balance after deducting loan processing fee is less or equal to loan balance**************/
    //         if ($loan_pay_amount < $loan->balance) {
    //             //amount remaining is less than or equal to loan balance
    //             $pay_loan = Payment::create([
    //                 'loan_id' => $loan->id,
    //                 'date_payed' => Carbon::now("Africa/Nairobi"),
    //                 'transaction_id' => $callbackData->TransID,
    //                 'amount' => $loan_pay_amount,
    //                 'channel' => "MPESA",
    //                 'payment_type_id' => 1,
    //             ]);
    //         }
    //         if ($loan_pay_amount == $loan->balance) {
    //             $pay_loan = Payment::create([
    //                 'loan_id' => $loan->id,
    //                 'date_payed' => Carbon::now("Africa/Nairobi"),
    //                 'transaction_id' => $callbackData->TransID,
    //                 'amount' => $loan_pay_amount,
    //                 'channel' => "MPESA",
    //                 'payment_type_id' => 1,
    //             ]);
    //             Loan::find($loan->id)->update(['settled' => true]);
    //         } else {
    //             //amount remaining is greater than loan amount so put the remaining in reg fee account
    //             $pay_loan = Payment::create([
    //                 'loan_id' => $loan->id,
    //                 'date_payed' => Carbon::now("Africa/Nairobi"),
    //                 'transaction_id' => $callbackData->TransID,
    //                 'amount' => (int)$loan->balance,
    //                 'channel' => "MPESA",
    //                 'payment_type_id' => 1,
    //             ]);

    //             //set loan as paid
    //             Loan::find($loan->id)->update(['settled' => true]);

    //             //add excess amount to customer account
    //             $over_pay = $loan_pay_amount - $loan->balance;
    //             $reg2 = Regpayment::where('customer_id', $member->id)->first();
    //             $add_to_reg = $reg2->update([
    //                 //'date_payed' => Carbon::now('Africa/Nairobi'),
    //                 "amount" => $reg2->amount + $over_pay,
    //                 "transaction_id" => $callbackData->TransID,
    //             ]);
    //         }
    //         $this->handle_installments($loan, $loan_pay_amount);
    //     }
    // }

    // public function group_settle_using_reminder($settle, $cust, $amt, $callbackData, $group)
    // {
    //         //get loan officer attached to a customer
    //         $lf = User::find($cust->field_agent_id);
    //         $loan = $settle;

    //         $result = [
    //             "amount" => $amt,
    //             "mpesaReceiptNumber" => $callbackData->TransID,
    //             "customer_id" => $cust->id,
    //             "transactionDate" => Carbon::now('Africa/Nairobi'),
    //             "phoneNumber" => $callbackData->MSISDN
    //         ];
    //         if ($loan) {
    //             //meaning has an active loan so first if he has paid reg fee
    //             $reg = Regpayment::where('customer_id', $cust->id)->first();
    //             $setting = Setting::first();
    //             if ($reg) {
    //                 if ($reg->amount > $setting->registration_fee) {
    //                     //Registration amount is more than set registration
    //                     $remaining_reg = (int)$reg->amount - (int)$setting->registration_fee;
    //                     $reg->update([
    //                         "amount" => (int)$setting->registration_fee,
    //                         "transaction_id" => $callbackData->TransID,
    //                     ]);
    //                     //remaider after reg
    //                     $remaiderafter_reg = (int)$amt + $remaining_reg;
    //                     $this->group_rem_after_reg($callbackData, $cust, $remaiderafter_reg, $group);
    //                 } else if ($reg->amount = $setting->registration_fee) {
    //                     /*************************if paid registration amount equal to set registration fee*****************/
    //                     $reg_fee = Payment::where(['loan_id' => $loan->id, 'payment_type_id' => 3])->sum('amount');
    //                     if ($reg_fee >= (int)$setting->loan_processing_fee || $reg_fee == 400) {
    //                         //meaning the loan processing fee has bee paid so continue in settling the loan
    //                         // Log::info('balance loan', ['balance' => $loan->balance]);
    //                         if ($amt < $loan->balance) {
    //                             //amount remaining is less than or equal to loan amount
    //                             $pay_loan = Payment::create([
    //                                 'loan_id' => $loan->id,
    //                                 'date_payed' => Carbon::now("Africa/Nairobi"),
    //                                 'transaction_id' => $callbackData->TransID,
    //                                 'amount' => $amt,
    //                                 'channel' => "MPESA",
    //                                 'payment_type_id' => 1,


    //                             ]);
    //                         } else {
    //                             //amount remaining is greator than loan balance so put the remaining in reg fee account
    //                             $over_pay = $amt - $loan->balance;
    //                             $pay_loan = Payment::create([
    //                                 'loan_id' => $loan->id,
    //                                 'date_payed' => Carbon::now("Africa/Nairobi"),
    //                                 'transaction_id' => $callbackData->TransID,
    //                                 'amount' => $loan->balance,
    //                                 'channel' => "MPESA",
    //                                 'payment_type_id' => 1,
    //                             ]);

    //                             //set loan as paid
    //                             Loan::find($loan->id)->update(['settled' => true]);
    //                             $reg2 = Regpayment::where('customer_id', $cust->id)->first();
    //                             $add_to_reg = $reg2->update([
    //                                 // 'date_payed' => Carbon::now('Africa/Nairobi'),
    //                                 "amount" => $reg2->amount + $over_pay,
    //                                 "transaction_id" => $callbackData->TransID,
    //                             ]);
    //                         }
    //                         $this->handle_installments($loan, $amt);
    //                     }
    //                 } else {
    //                     /*************************if paid registration amount is less than set registration fee*****************/
    //                     $remaining_reg = (int)$setting->registration_fee - (int)$reg->amount;

    //                     if ($amt <= $remaining_reg) {
    //                         $reg->update([
    //                             'date_payed' => Carbon::now('Africa/Nairobi'),
    //                             "amount" => (int)$reg->amount + $amt,
    //                             "transaction_id" => $callbackData->TransID,
    //                         ]);
    //                     } else {
    //                         //more amount than registration
    //                         $reg->update([
    //                             'date_payed' => Carbon::now('Africa/Nairobi'),
    //                             "amount" => (int)$setting->registration_fee,
    //                             "transaction_id" => $callbackData->TransID,
    //                         ]);

    //                         //remaider after reg
    //                         $remaiderafter_reg = (int)$amt - $remaining_reg;
    //                         $this->group_rem_after_reg($callbackData, $cust, $remaiderafter_reg, $group);
    //                     }
    //                 }
    //             }
    //             /****************************************very unlickely to happen that customer have approved loan and he has not paid registration fee *********************/
    //             else {
    //                 if ($amt <= (int)$setting->registration_fee) {
    //                     $regi = Regpayment::create([
    //                         'customer_id' => $cust->id,
    //                         'date_payed' => Carbon::now('Africa/Nairobi'),
    //                         "amount" => $amt,
    //                         "transaction_id" => $callbackData->TransID,
    //                         "channel" => "MPESA",
    //                     ]);
    //                 } else {
    //                     //more amount than registration
    //                     $remaining_reg = (int)$amt - (int)$setting->registration_fee;
    //                     $regi = Regpayment::create([
    //                         'customer_id' => $cust->id,
    //                         'date_payed' => Carbon::now('Africa/Nairobi'),
    //                         "amount" => (int)$setting->registration_fee,
    //                         "transaction_id" => $callbackData->TransID,
    //                         "channel" => "MPESA",
    //                     ]);
    //                     //remaider after reg
    //                     $remaiderafter_reg = $remaining_reg;
    //                     $this->group_rem_after_reg($callbackData, $cust, $remaiderafter_reg, $group);
    //                 }
    //             }

    //             if ($loan->balance > 0){
    //                 $amessage = 'Dear ' . $cust->fname . ',' . "\r\n" . 'a reminder fee of Ksh. ' . $amt . ' has been received and debited to your account ' . $group->name . '. Outstanding group loan balance is Ksh. ' . $loan->balance;
    //             } else {
    //                 $amessage = 'Dear ' . $cust->fname . ',' . "\r\n" . 'a reminder fee of Ksh. ' . $amt . ' has been received and debited to your account ' . $group->name . '. Outstanding group loan balance is Ksh. ' . $loan->balance;
    //             }
    //         }
    //         $transaction = RepaymentMpesaTransaction::create($result);
    //         $aphone = '+254' . substr($cust->phone, -9);
    //         $auser = $cust;
    //         $suser_type = false;
    //         $fnd = dispatch(new Sms(
    //             $aphone, $amessage, $auser, $suser_type
    //         ));
    // }

    private function checkoff_payment_confirmation($callbackData){
        $BillRefNumber = Str::upper($callbackData->BillRefNumber);
        $employer = CheckOffEmployer::query()->where('code', '=', $BillRefNumber)->first();
        if ($employer){
            $employee_phone_number = '254' . substr($callbackData->MSISDN, -9);
            $employee = CheckOffEmployee::query()->where([
                'employer_id' => $employer->id,
                'phone_number' => $employee_phone_number
            ])->first();
            if ($employee) {
                $unsettled_loan = CheckOffLoan::query()->where([
                    'employee_id' => $employee->id,
                    'approved' => true,
                    'settled' => false,
                    'disbursed' => true,
                ])->first();
                if($unsettled_loan){
                    $payment = CheckOffPayment::query()->updateOrCreate([
                        'TransID' => $callbackData->TransID,
                    ],[
                        'employer_id' => $employer->id,
                        'loan_id' => $unsettled_loan->id,
                        'employee_id' => $employee->id,
                        'TransAmount' => $callbackData->TransAmount,
                        'TransTime' => $callbackData->TransTime,
                        'BusinessShortCode' => $callbackData->BusinessShortCode,
                        'BillRefNumber' => $callbackData->BillRefNumber,
                        'InvoiceNumber' => $callbackData->InvoiceNumber,
                        'OrgAccountBalance' => $callbackData->OrgAccountBalance,
                        'MSISDN' => $callbackData->MSISDN,
                        'FirstName' => $callbackData->FirstName,
                        'MiddleName' => $callbackData->MiddleName,
                        'LastName' => $callbackData->LastName
                    ]);
                    $balance = $unsettled_loan->balance;

                    if ($balance > 0){
                        $message = "Hello $callbackData->FirstName, your Willow Capital Advance payment of KES. ".number_format($payment->TransAmount)." has been received MPESA Ref: $payment->TransID. Your Advance loan balance is now at KES. ".number_format($balance).". For any queries kindly contact our customer care on " . config('app.customer_care_contact');
                    } else {
                        $unsettled_loan->update([
                            'settled' => true,
                            'settled_at' => now()
                        ]);
                        $message = "Hello $callbackData->FirstName, your Willow Capital Advance payment of KES. ".number_format($payment->TransAmount)." has been received MPESA Ref: $payment->TransID. Thank you for successfully completing your Willow Capital Advance Loan. For any queries kindly contact our customer care on " . config('app.customer_care_contact');
                    }
                    if ($message != ''){
                        $auser = User::first();
                        dispatch(new Sms(
                            '+254' . substr($callbackData->MSISDN, -9), $message, $auser, false
                        ));
                    }
                }
            }
        }
        return response()->json([
            'ResultCode' => '0',
            'ResultDesc' => 'Completed',
        ]);
    }

    public function getEncryptedPasswd($plaintext)
    {
        $pk = openssl_pkey_get_public($this->getPublicKey());
        openssl_public_encrypt($plaintext, $encrypted, $pk, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }

    /**
     * The Public key certificate being used. Replace with the respective certificate required depending on environment.
     * To get it, just open the certificate in notepad++ and copy paste the contents here, then remove the
     * new line characters '\n' <strong>EXCEPT</strong> the first and last newlines. Check the one below for the required format
     *
     * This is currently the G2 sandbox certificate, will not work with Daraja sandbox
     *
     * @return string
     */
    private function getPublicKey()
    {
        $certStr = "-----BEGIN CERTIFICATE-----\n".     /** DO NOT remove this newline */
            "MIIGkzCCBXugAwIBAgIKXfBp5gAAAD+hNjANBgkqhkiG9w0BAQsFADBbMRMwEQYK".
            "CZImiZPyLGQBGRYDbmV0MRkwFwYKCZImiZPyLGQBGRYJc2FmYXJpY29tMSkwJwYD".
            "VQQDEyBTYWZhcmljb20gSW50ZXJuYWwgSXNzdWluZyBDQSAwMjAeFw0xNzA0MjUx".
            "NjA3MjRaFw0xODAzMjExMzIwMTNaMIGNMQswCQYDVQQGEwJLRTEQMA4GA1UECBMH".
            "TmFpcm9iaTEQMA4GA1UEBxMHTmFpcm9iaTEaMBgGA1UEChMRU2FmYXJpY29tIExp".
            "bWl0ZWQxEzARBgNVBAsTClRlY2hub2xvZ3kxKTAnBgNVBAMTIGFwaWdlZS5hcGlj".
            "YWxsZXIuc2FmYXJpY29tLmNvLmtlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB".
            "CgKCAQEAoknIb5Tm1hxOVdFsOejAs6veAai32Zv442BLuOGkFKUeCUM2s0K8XEsU".
            "t6BP25rQGNlTCTEqfdtRrym6bt5k0fTDscf0yMCoYzaxTh1mejg8rPO6bD8MJB0c".
            "FWRUeLEyWjMeEPsYVSJFv7T58IdAn7/RhkrpBl1dT7SmIZfNVkIlD35+Cxgab+u7".
            "+c7dHh6mWguEEoE3NbV7Xjl60zbD/Buvmu6i9EYz+27jNVPI6pRXHvp+ajIzTSsi".
            "eD8Ztz1eoC9mphErasAGpMbR1sba9bM6hjw4tyTWnJDz7RdQQmnsW1NfFdYdK0qD".
            "RKUX7SG6rQkBqVhndFve4SDFRq6wvQIDAQABo4IDJDCCAyAwHQYDVR0OBBYEFG2w".
            "ycrgEBPFzPUZVjh8KoJ3EpuyMB8GA1UdIwQYMBaAFOsy1E9+YJo6mCBjug1evuh5".
            "TtUkMIIBOwYDVR0fBIIBMjCCAS4wggEqoIIBJqCCASKGgdZsZGFwOi8vL0NOPVNh".
            "ZmFyaWNvbSUyMEludGVybmFsJTIwSXNzdWluZyUyMENBJTIwMDIsQ049U1ZEVDNJ".
            "U1NDQTAxLENOPUNEUCxDTj1QdWJsaWMlMjBLZXklMjBTZXJ2aWNlcyxDTj1TZXJ2".
            "aWNlcyxDTj1Db25maWd1cmF0aW9uLERDPXNhZmFyaWNvbSxEQz1uZXQ/Y2VydGlm".
            "aWNhdGVSZXZvY2F0aW9uTGlzdD9iYXNlP29iamVjdENsYXNzPWNSTERpc3RyaWJ1".
            "dGlvblBvaW50hkdodHRwOi8vY3JsLnNhZmFyaWNvbS5jby5rZS9TYWZhcmljb20l".
            "MjBJbnRlcm5hbCUyMElzc3VpbmclMjBDQSUyMDAyLmNybDCCAQkGCCsGAQUFBwEB".
            "BIH8MIH5MIHJBggrBgEFBQcwAoaBvGxkYXA6Ly8vQ049U2FmYXJpY29tJTIwSW50".
            "ZXJuYWwlMjBJc3N1aW5nJTIwQ0ElMjAwMixDTj1BSUEsQ049UHVibGljJTIwS2V5".
            "JTIwU2VydmljZXMsQ049U2VydmljZXMsQ049Q29uZmlndXJhdGlvbixEQz1zYWZh".
            "cmljb20sREM9bmV0P2NBQ2VydGlmaWNhdGU/YmFzZT9vYmplY3RDbGFzcz1jZXJ0".
            "aWZpY2F0aW9uQXV0aG9yaXR5MCsGCCsGAQUFBzABhh9odHRwOi8vY3JsLnNhZmFy".
            "aWNvbS5jby5rZS9vY3NwMAsGA1UdDwQEAwIFoDA9BgkrBgEEAYI3FQcEMDAuBiYr".
            "BgEEAYI3FQiHz4xWhMLEA4XphTaE3tENhqCICGeGwcdsg7m5awIBZAIBDDAdBgNV".
            "HSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwEwJwYJKwYBBAGCNxUKBBowGDAKBggr".
            "BgEFBQcDAjAKBggrBgEFBQcDATANBgkqhkiG9w0BAQsFAAOCAQEAC/hWx7KTwSYr".
            "x2SOyyHNLTRmCnCJmqxA/Q+IzpW1mGtw4Sb/8jdsoWrDiYLxoKGkgkvmQmB2J3zU".
            "ngzJIM2EeU921vbjLqX9sLWStZbNC2Udk5HEecdpe1AN/ltIoE09ntglUNINyCmf".
            "zChs2maF0Rd/y5hGnMM9bX9ub0sqrkzL3ihfmv4vkXNxYR8k246ZZ8tjQEVsKehE".
            "dqAmj8WYkYdWIHQlkKFP9ba0RJv7aBKb8/KP+qZ5hJip0I5Ey6JJ3wlEWRWUYUKh".
            "gYoPHrJ92ToadnFCCpOlLKWc0xVxANofy6fqreOVboPO0qTAYpoXakmgeRNLUiar".
            "0ah6M/q/KA==\n".       /** DO NOT remove this newline */
            "-----END CERTIFICATE-----";

        return $certStr;
    }

}
