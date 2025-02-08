<?php

namespace App\Jobs;

use App\models\Msetting;
use App\models\Settllement_request;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;

class Settlement_disburse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable/*, SerializesModels*/;

    protected $token;
    protected $user;
    protected $amount;
    protected $url;
    protected $InitiatorName;
    protected $SecurityCredential;
    protected $CommandID;
    protected $PartyA;
    protected $PartyB;
    protected $Remarks;
    protected $QueueTimeOutURL;
    protected $ResultURL;
    protected $Occasion;
    protected $requestedby;






    public function __construct($token, $user, $amount,$url,$InitiatorName,$SecurityCredential,$CommandID,$PartyA,$PartyB,$Remarks,
                                $QueueTimeOutURL,$ResultURL,$Occasion,$requestedby)
    {
        $this->token = $token;
        $this->user = $user;
        $this->amount = $amount;
        $this->url = $url;
        $this->InitiatorName = $InitiatorName;
        $this->SecurityCredential = $SecurityCredential;
        $this->CommandID = $CommandID;
        $this->PartyA = $PartyA;
        $this->PartyB = $PartyB;
        $this->Remarks = $Remarks;
        $this->QueueTimeOutURL = $QueueTimeOutURL;
        $this->ResultURL = $ResultURL;
        $this->Occasion = $Occasion;
        $this->requestedby = $requestedby;


    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();

        //try {
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
                        //'SecurityCredential' => 'nCctVVmOgmkigmtT3O9nKeGGKDCmNI5i+DWVt3PoT5wwjKg4Fql8rKt+tGied4Zo4f7LQbMzTYPeXpYubXR456oWbkVPQlgK9752Yqu4Xyjyjymw5jlpp8Di4m/zyK5JzOOYaCle15133AfACWvP8LwBPDPHjXTF/ySLTMiifJtLPqd1XXneHTDo6a34paZrdj9gWILcvqLRCp1n2lD8tKb55XvT9dO8nmlsUaNPjf/jJDYF5WzEHF7lh40RPLelXenco82D/rNvP+BHbwChenikCiVZPaVjpHAbYJTKiednj0Sso+zmloqiDLjVT7tXGHSrlkHTbvfwvlKi6a8BCA==',
                        'CommandID' => $this->CommandID,
                        'Amount' => $this->amount,
                        'PartyA' => $this->PartyA,
                        'PartyB' => $this->PartyB,
                        'Remarks' => $this->Remarks,
                        'QueueTimeOutURL' => $this->QueueTimeOutURL,
                        'ResultURL' => $this->ResultURL,
                        'Occasion' => $this->Occasion,

                    ]
                )
            ]);


            // var_dump($res);exit();


            $obj = json_decode((string)$check->getBody());


            if ($obj->ResponseCode == 0) {

                $mreq = Settllement_request::create([
                    'ConversationID' => $obj->ConversationID,
                    'user_id' => $this->user,
                    'OriginatorConversationID' => $obj->OriginatorConversationID,
                    'ResponseCode' => $obj->ResponseCode,
                    'ResponseDescription' => $obj->ResponseDescription,
                    'requested_by' => $this->requestedby,
                ]);




            }


        /*} catch (\Exception $e) {



        }*/
    }

    public function failed(\Throwable $exception)
    {
        // Send user notification of failure, etc...
    }
}
