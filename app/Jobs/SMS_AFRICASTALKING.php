<?php

namespace App\Jobs;

use AfricasTalking\SDK\AfricasTalking;
use App\models\Branch;
use App\models\CustomerSms;
use App\models\UserSms;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

//class Sms implements ShouldQueue
class SMS_AFRICASTALKING


{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    //use Dispatchable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $phone;
    protected $message;
    protected $user;
    protected $type;
    public function __construct($phone, $message, $user, $type)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (env('APP_ENV') == 'local'){
            $phone = '+254725730055';
            $username = 'sandbox';
            $apiKey = env('A_sandbox_key');
        }
        else{
            $phone = $this->phone;
            $username = config('app.AT_USERNAME');
            $apiKey = config('app.AT_KEY');
        }
        // $AT = new AfricasTalking($username, $apiKey);
        // $sms = $AT->sms();
        // $sms->send([
        //     'from' => config('app.AT_FROM'),
        //     'to' =>  $phone,
        //     'message' => $this->message
        // ]);

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
                    "sender_name" => "PASANDA",
                    "contact" => $this->phone,
                    "message" => $this->message,
                    "callback" => "https://pasanda.com/sms/callback"
                )
         ),

         CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$token->access_token
         ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);



        if ($this->user){
            if($this->type){
                UserSms::create([
                    'user_id' => $this->user->id,
                    'sms' => $this->message,
                    'branch_id' => $this->user->branch_id,
                    'phone' => $phone
                ]);
            }
            else{
                CustomerSms::create([
                    'customer_id' => $this->user->id,
                    'sms' => $this->message,
                    'branch_id' => $this->user->branch_id,
                    'phone' => $phone
                ]);
            }
        }
    }
}
