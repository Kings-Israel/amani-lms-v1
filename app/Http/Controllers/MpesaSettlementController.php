<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\Expense;
use App\models\Mpesa_settlement;
use App\models\Settllement_request;
use App\models\User_payments;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MpesaSettlementController extends Controller
{
    public function settlement_result(Request $request){
        $callbackJSONData=file_get_contents('php://input');

        $callbackData=json_decode($callbackJSONData);

        $resultCode=$callbackData->Result->ResultCode;
        $resultDesc=$callbackData->Result->ResultDesc;
        $originatorConversationID=$callbackData->Result->OriginatorConversationID;
        $conversationID=$callbackData->Result->ConversationID;
        $transactionID=$callbackData->Result->TransactionID;
        $req = Settllement_request::where('OriginatorConversationID', $originatorConversationID)->first();
        $user = User::find($req->user_id);
        $phone = $user->phone;

        //$product = Product::where

        /******************success parameters*************************/
        if ($resultCode == 0) {
            $TransactionAmount = $callbackData->Result->ResultParameters->ResultParameter[0]->Value;
            $TransactionReceipt = $callbackData->Result->ResultParameters->ResultParameter[1]->Value;
            $B2CRecipientIsRegisteredCustomer = $callbackData->Result->ResultParameters->ResultParameter[2]->Value;
            $B2CChargesPaidAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[3]->Value;
            $ReceiverPartyPublicName = $callbackData->Result->ResultParameters->ResultParameter[4]->Value;
            $TransactionCompletedDateTime2 = $callbackData->Result->ResultParameters->ResultParameter[5]->Value;
            $B2CUtilityAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[6]->Value;
            $B2CWorkingAccountAvailableFunds = $callbackData->Result->ResultParameters->ResultParameter[7]->Value;

            $int = date_create($TransactionCompletedDateTime2);
            //$TransactionCompletedDateTime = Carbon::parse($TransactionCompletedDateTime2)->toDateTimeString();
            $TransactionCompletedDateTime = date("Y-m-d h:m:s");



            $result=[
                "ResultCode"=>$resultCode,
                "ResultDesc"=>$resultDesc,
                "OriginatorConversationID"=>$originatorConversationID,
                "ConversationID"=>$conversationID,
                "TransactionID"=>$transactionID,
                "TransactionAmount"=>$TransactionAmount,
                "TransactionReceipt"=>$TransactionReceipt,
                "B2CRecipientIsRegisteredCustomer"=>$B2CRecipientIsRegisteredCustomer,
                "B2CChargesPaidAccountAvailableFunds"=>$B2CChargesPaidAccountAvailableFunds,
                "ReceiverPartyPublicName"=>$ReceiverPartyPublicName,
                "TransactionCompletedDateTime"=>$TransactionCompletedDateTime,
                "B2CUtilityAccountAvailableFunds"=>$B2CUtilityAccountAvailableFunds,
                "B2CWorkingAccountAvailableFunds"=>$B2CWorkingAccountAvailableFunds,
                "status" => true,
                'user_id' => $user->id
            ];

            //$this->data['success2'] = 'Your Request of redeeming Ksh.'.$TransactionAmount.' has been approved';






        }

        /*******************************transaction was not successfull*/
        else{
            $result=[
                "ResultCode"=>$resultCode,
                "ResultDesc"=>$resultDesc,
                "OriginatorConversationID"=>$originatorConversationID,
                "ConversationID"=>$conversationID,
                "TransactionID"=>$transactionID,
                "status" => false,
                'user_id' => $user->id
            ];

//            $mrequest = Mrequest::where('OriginatorConversationID', $originatorConversationID)->first()->amount;
//
//            $this->data['failure'] = 'Your Request of redeeming Ksh.'.$mrequest.' has not been approved. Please try again later';



        }


        //($result);
        // Log::info($result);
        $tran = Mpesa_settlement::where('OriginatorConversationID', $originatorConversationID)->first();
        //  $this->data = [];

        if (!$tran ){
            $Transaction = Mpesa_settlement::create($result);
            $mrequest = Settllement_request::where('OriginatorConversationID', $originatorConversationID)->update(['settled' => true]);


            /*********************update payment about the success transaction**************************/

            if($Transaction->status){
                //create installments

                /************************************update the  */
                $expense = Expense::create([
                    'expense_type_id' => 1,
                    'amount' => $TransactionAmount,
                    'branch_id' => $user->branch_id,
                    'date_payed' => Carbon::now(),
                    'description' => 'Investment withdrawl',
                    'paid_by' => $req->requested_by
                ]);
                $pay = User_payments::create([
                    'user_id' => $user->id,
                    'expense_id' => $expense->id,
                    'amount' => $TransactionAmount,
                    'date_payed' => Carbon::now(),
                    'channel' => 'MPESA',
                    'transaction_id' => $transactionID,
                ]);


                /***************************Send sms*********************************/
                $phone = '+254' . substr($phone, -9);
                $user_type = true;
                $message = "Your ".env('APP_NAME')." Salary of Ksh ".$TransactionAmount. ' has been deposited to your Mpesa';


                $fnd = dispatch(new Sms(

                    $phone, $message,$user,$user_type

                ));






            }



        }

        //terminate transaction
        $this->finishTransaction();






    }


    /******************************finish mpesa transaction*****************/
    public function finishTransaction($status = true)
    {
        if ($status === true) {
            $resultArray=[
                "ResultDesc"=>"Confirmation Service request accepted successfully",
                "ResultCode"=>"0"
            ];
        } else {
            $resultArray=[
                "ResultDesc"=>"Confirmation Service not accepted",
                "ResultCode"=>"1"
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($resultArray);
    }

}
