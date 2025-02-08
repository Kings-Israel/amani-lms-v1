<?php

namespace App\Jobs;

use AfricasTalking\SDK\AfricasTalking;
use App\models\UserSms;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Prospects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $message;
    protected $user;
    protected $type;
    protected $prospect;




    public function __construct($phone, $message, $user, $type, $prospect)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->user = $user;
        $this->type = $type;
        $this->prospect = $prospect;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // if (env('APP_ENV') == 'local'){
        //     $phone = '+254725730055';
        //     $username = 'sandbox';
        //     $apiKey = env('A_sandbox_key');
        // }
        // else{
        //     $phone = $this->phone;
        //     $username = config('app.AT_USERNAME');
        //     $apiKey = config('app.AT_KEY');
        // }
        // $AT = new AfricasTalking($username, $apiKey);
        // $sms = $AT->sms();
        // $result = $sms->send([
        //     'from' => config('app.AT_FROM'),
        //     'to'      =>  $phone,
        //     'message' => $this->message
        // ]);
        $phone = '254' . substr($this->phone, -9);

        $curl = curl_init();

        $url = 'https://accounts.jambopay.com/auth/token';
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded',
            )
        );

        curl_setopt($curl, CURLOPT_POSTFIELDS,
            http_build_query(array('grant_type' => 'client_credentials', 'client_id' => config('services.jambopay.sms_client_id'), 'client_secret' => config('services.jambopay.sms_client_secret'))));

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);

        $token = json_decode($curl_response);
        curl_close($curl);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://swift.jambopay.co.ke/api/public/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(
                array(
                    "sender_name" => "LITSACREDIT",
                    "contact" => $phone,
                    "message" => $this->message,
                    "callback" => "https://shugli.deveint.live/sms/callback"
                )
            ),

            CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$token->access_token
            ),
        ));

        $response = curl_exec($curl);
        
        UserSms::create([
            'user_id' => $this->user->id,
            'sms' => $this->message,
            'branch_id' => $this->user->branch_id
        ]);
        if($this->type){
            $this->prospect->update(['received' => true]);
        }
    }
}
