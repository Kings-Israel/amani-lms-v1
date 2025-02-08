<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MpesaPaymentController;
use App\Http\Controllers\PaymentController;
use App\models\Arrear;
use App\models\Customer;
use App\models\Installment;
use App\models\Loan;
use App\models\Msetting;
use App\models\Payment;
use App\models\Product;
use App\models\Regpayment;
use App\models\RepaymentMpesaTransaction;
use App\models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/super-crunch-callback','SuperCrunchController@callback');

Route::get('/mp/register_url', 'MpesaPaymentController@registerurl')->name('mpesa.registerurl');

Route::post('/mp/confirmation', 'MpesaPaymentController@confirmation')->name('api.mpesa.confirmation');
Route::post('/mp/validation', 'MpesaPaymentController@validation_url')->name('api.mpesa.validation_url');
Route::post('/mp/disbursement_result', 'DisbursementController@mpesa_disbursement_result')->name('api.mpesa_disbursement.result');
Route::post('/mp/disbursement_result_timeout', 'DisbursementController@mpesa_disbursement_result_timeout')->name('api.mpesa_disbursement.timeout');
Route::get('/mpesa/balance', 'DisbursementController@mpesa_balance')->name('api.mpesa_balance');
Route::post('mp/balance', 'DisbursementController@mpesa_balance_result')->name('api.mpesa_balance.result');
Route::post('/mp/balance_timeout', 'DisbursementController@mpesa_balance_timeout')->name('api.mpesa_balance.timeout');

Route::get('/transaction/{transaction}/status', function ($transaction) {
    $url = 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query';
    $disbursement = new DisbursementController;
    $token = $disbursement->generateLiveToken();

    return $token;

    $msetting = Msetting::first();

    $InitiatorName = $msetting->InitiatorName;
    $PartyA = $msetting->paybill;
    $SecurityCredential = $msetting->SecurityCredential;
    $CommandID = "TransactionStatusQuery";
    $IdentifierType = "4";
    $QueueTimeOutURL = 'https://lms.litsacredits.com/api/mp/transaction/status/timeout';
    $ResultURL = 'https://lms.litsacredits.com/api/mp/transaction/status';
    $Remarks = 'ok';
    $TransactionID = $transaction;

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
                'IdentifierType' => $IdentifierType,
                "TransactionID" => $TransactionID,
                "Remarks" => "OK",
                "Occasion" => "OK",
            )
        ),

        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    info($response);

});

Route::post('/mp/transaction/status', function () {
    $callbackJSONData = file_get_contents('php://input');
    $callbackData = json_decode($callbackJSONData);
    info($callbackJSONData);
})->name('api.mpesa_transaction_status');

Route::post('/mp/transaction/status/timeout', function () {
    $callbackJSONData = file_get_contents('php://input');
    $callbackData = json_decode($callbackJSONData);
    info($callbackJSONData);
})->name('api.mpesa_transaction_status_timeout');

Route::get('/mpesa/password/encrypt', function(){
    //how to get MPesa encrypted password
    $initiatorEnc = new MpesaPaymentController();
    // $encrypted = $initiatorEnc->getEncryptedPasswd("Test#2018");
    $encrypted = $initiatorEnc->getEncryptedPasswd("1996IsraeL010#");
    // echo "Encrypted password: ".$encrypted."\n\n<br><br>";
    return $encrypted;
});

Route::get('/balance', 'DisbursementController@mpesa_balance');

Route::post('/disbursement/upload', [LoanController::class, 'uploadDisbursement']);
Route::get('/installments/update', [LoanController::class, 'updateInstallments']);

Route::get('/loan/amount/{amount}', function ($amount) {
    if ($amount == 5600) {
        return 7000;
    }
    return floor($amount * (int) 135) / 100;
});

Route::post('/transactions/reconcile', [LoanController::class, 'reconcileTransactions']);

Route::post('/restructure/loan', [LoanController::class, 'restructure_loan']);
Route::post('/payment/remove', [LoanController::class, 'removePayment']);
Route::post('/payments/remove', [LoanController::class, 'removePayments']);
Route::post('/payment/amount/remove', [LoanController::class, 'removeAmount']);
Route::get('/resolve/payments', [LoanController::class, 'resolvePayments']);
Route::post('/resolve/application-fees', [LoanController::class, 'resolveApplicationFee']);
Route::post('/resolve/end-dates', [LoanController::class, 'resolveEndDates']);

Route::post('/loan/update', [CustomerController::class, 'refactorLoan']);
Route::get('/loan/{phone}/unapprove', [LoanController::class, 'unapproveLoan']);
