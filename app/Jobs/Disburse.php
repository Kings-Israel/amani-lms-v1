<?php

namespace App\Jobs;

use App\models\Loan;
use App\models\Mrequest;
use App\models\Msetting;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Disburse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable/*, SerializesModels*/;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $token;
    protected $loan;
    protected $PartyB;
    protected $url;
    protected $InitiatorName;
    protected $PartyA;
    protected $Remarks;
    protected $QueueTimeOutURL;
    protected $ResultURL;
    protected $Occasion;
    protected $SecurityCredential;
    protected $CommandID;
    protected $id;
    protected $requestedby;
    protected $ip;









    public function __construct($token, $id, $loan, $PartyB, $url, $InitiatorName, $PartyA, $Remarks, $QueueTimeOutURL, $ResultURL,
                                $Occasion, $SecurityCredential,$CommandID, $requestedby, $ip)
    {
        $this->token = $token;
        $this->loan = $loan;
        $this->PartyB = $PartyB;
        $this->url = $url;
        $this->InitiatorName = $InitiatorName;
        $this->PartyA = $PartyA;
        $this->Remarks = $Remarks;
        $this->QueueTimeOutURL = $QueueTimeOutURL;
        $this->ResultURL = $ResultURL;
        $this->Occasion = $Occasion;
        $this->SecurityCredential = $SecurityCredential;
        $this->CommandID = $CommandID;
        $this->id = $id;
        $this->requestedby = $requestedby;
        $this->ip = $ip;







    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $client = new Client();

       // try {
            $check = $client->request('post', $this->url, [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(
                    [
                        'InitiatorName' => $this->InitiatorName,
                        'SecurityCredential' => $this->SecurityCredential,
                        'CommandID' => $this->CommandID,
                        'Amount' => $this->loan,
                        'PartyA' => $this->PartyA,
                        'PartyB' => $this->PartyB,
                        'Remarks' => $this->Remarks,
                        'QueueTimeOutURL' => $this->QueueTimeOutURL,
                        'ResultURL' => $this->ResultURL,
                        'Occasion' => $this->Occasion,

                    ]
                )
            ]);




            $obj = json_decode((string)$check->getBody());


            // dd($obj);
            if ($obj->ResponseCode == 0) {

                $mreq = Mrequest::create([
                    'ConversationID' => $obj->ConversationID,
                    'loan_id' => $this->id,
                    'OriginatorConversationID' => $obj->OriginatorConversationID,
                    'ResponseCode' => $obj->ResponseCode,
                    'ResponseDescription' => $obj->ResponseDescription,
                    'requested_by' => $this->requestedby,
                    'amount' => $this->loan,
                    'disburse_loan_ip' => $this->ip
                ]);




            }


        /*} catch (\Exception $e) {



        }*/
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
    }
}
