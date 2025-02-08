<?php

namespace App\Services;


use App\models\Activity_otp;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Custom
{
    function check_token_validity($token, $activity){
        $act = Activity_otp::where(['token' => $token, 'user_id' => \auth()->id(), 'status' => 1, 'activity' =>$activity])->where('expire_at', '>', \Carbon\Carbon::now() )->first();

       // dd($act, $activity, $token);
       // $status = 0;
        if ($act){
            if ($activity == "approve"){
                Session::put('approval_token_session', encrypt($token));

            } elseif($activity == "disburse"){
                Session::put('disburse_token_session', encrypt($token));
            }

            $status = 1;
        } else{
            if ($activity == "approve"){
                session()->forget(['approval_token_session']);

            } elseif($activity == "disburse"){
                session()->forget(['disburse_token_session']);
            }

            $status = 0;
        }

        return $status;

    }

}
