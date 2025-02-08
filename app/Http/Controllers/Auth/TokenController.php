<?php

namespace App\Http\Controllers\Auth;

use App\Jobs\Sms;
use App\LoginToken;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TokenController extends Controller
{
    // show the two factor auth form
    public function show2faForm()
    {
        $user = Auth::user();
        $active_token = $user->login_token()->where('active', '=', true)->orderBy('id', 'desc')->first(); //most recent
       // if ($active_token->token_expires_at > \Carbon\Carbon::now() and $active_token->in_use == true){
        if ($active_token){
            if ($active_token->in_use == true) {
                // return redirect()->back(); //if a token exists and is in use, hide 2fa form
                return redirect()->intended('/home');
            }
        }

        return view('auth.2fa');
    }
    public function show2faForm1()
    {
        $user = Auth::user();
        $ses = Session::get("otp_session");
        //dd(decrypt($ses));
        if ($ses){
            $session = decrypt(Session::get("otp_session"));

            // $active_token = $user->login_token()->where('active', '=', true)->first(); //most recent
            $active_token = $user->login_token()->where(['active' => true, 'token' => $session])->first();
            if ($active_token){
                if ($active_token->in_use == true){
                    // if ($active_token->token_expires_at > \Carbon\Carbon::now()){

                    //return redirect()->back(); //if a token exists and is in use, hide 2fa form
                    return redirect()->intended('/home');

                }
            }


        }


        return view('auth.2fa');
    }

    // post token to the backend for check
    public function verifyToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|min:4|exists:login_tokens',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $user = auth()->user();
        $active_token = $user->login_token()->where('active', '=', true)->orderBy('id', 'desc')->first(); //users most recent token
        if (!$active_token){
            return redirect()->back()->with('info', 'You are yet to receive a login token, kindly click on the resend button.');
        }
        if ($request->get('token') == $active_token->token)
        {
            //update the expiry time of the token
            $active_token->update([
                "token_expires_at"=>Carbon::parse($active_token->token_expires_at)->addMinutes(120),
                "in_use"=>true,
                "updated_at"=>Carbon::now()
            ]);

            return redirect()->intended('/home');
        } else {
            $expired_token = $user->login_token()->where('token_expires_at', '<', Carbon::now())->where('token', '=', $request->get('token'))->first();
            if ($expired_token){
                return redirect('/2fa')->withInput()->with('warning', 'Entered Token has already expired, kindly request for another.');
            }
        }

        return redirect('/2fa')->with('error', 'Something went wrong, kindly request for a new token.');
    }
    public function verifyToken1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|min:4|exists:login_tokens',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $user = auth()->user();
        $ses = Session::get("otp_session");
        //dd($ses);
        if ($ses) {
            $session = decrypt(Session::get("otp_session"));
            //$active_token = $user->login_token()->where('active', '=', true)->orderBy('id', 'desc')->first(); //users most recent token
            $active_token = $user->login_token()->where(['active' => true, 'token' => $session])->first();

            if (!$active_token){
                return redirect()->back()->with('info', 'You are yet to receive a login token, kindly click on the resend button.');
            }
            if ($request->get('token') == $active_token->token)
            {
                //update the expiry time of the token
                $active_token->update([
                    "token_expires_at"=>Carbon::parse($active_token->token_expires_at)->addMinutes(2),
                    "in_use"=>true,
                    "updated_at"=>Carbon::now()
                ]);

                return redirect()->intended('/home');
            }
            else
            {
                $expired_token = $user->login_token()->where('token_expires_at', '<', Carbon::now())->where('token', '=', $request->get('token'))->first();
                if ($expired_token){
                    return redirect('/2fa')->withInput()->with('warning', 'Entered Token has already expired, kindly request for another.');
                }
            }
        }


        return redirect('/2fa')->with('error', 'Something went wrong, kindly request for a new token.');
    }

    //resend token
    public function resend_initial(Request $request)
    {
        $user = Auth::user();
        //check if user has recently received a token
        $recent_token = $user->login_token()->where('active', '=', true)->whereBetween('created_at',[Carbon::now()->subHour(),Carbon::now()])->first();

        if ($recent_token){
            return Redirect::back()->with('warning', 'You already received your token at '.Carbon::parse($recent_token->created_at)->format('D d M Y H:i'). ' check your messages for the sent OTP');
        }

        //fetch all previous active tokens and mark as inactive
        $active_tokens = $user->login_token()->where('active', '=', true)->get();
        if ($active_tokens){
            foreach ($active_tokens as $token){
                $token->update([
                    "active"=>false,
                    "in_use"=>false,
                    "updated_at"=>Carbon::now()
                ]);
            }
        }
        //create token and send to user
        $newToken = new LoginToken();
        $token = rand(1000,9999);

        $newToken->user_id = $user->id;
        $newToken->token = $token;
        $newToken->token_expires_at = Carbon::now()->addHour(); //a user can use the same token for an hours time
        $newToken->created_at = Carbon::now();
        $newToken->updated_at = Carbon::now();
        $newToken->save();


        $message = "Dear ".$user->name.",".PHP_EOL."Your login OTP is: ".$newToken->token;
        $auser = Auth::user();
        dispatch(new Sms(
            '+254' . substr($user->phone, -9), $message, $auser, true
        ));
        $request->session()->put('otp_session', encrypt($token));


        return redirect()->back()->with('success', 'A new login token has been created and sent to your mobile number.');

    }
    public function resend(Request $request)
    {
        $user = Auth::user();
        //check if user has recently received a token
        $ses = Session::get("otp_session");
        if ($ses) {
            $session = decrypt(Session::get("otp_session"));

            //$recent_token = $user->login_token()->where(['active' => true, 'token' => $session])->whereBetween('created_at',[Carbon::now()->subHour(),Carbon::now()])->first();
            $recent_token = $user->login_token()->where(['active' => true, 'token' => $session])->first();



            if ($recent_token){
                return Redirect::back()->with('warning', 'You already received your token at '.Carbon::parse($recent_token->created_at)->format('D d M Y H:i'). ' check your messages for the sent OTP');
            }
        }


        //fetch all previous active tokens and mark as inactive
        $active_tokens = $user->login_token()->get();
        if ($active_tokens){
            foreach ($active_tokens as $token){
                /*$token->update([
                    "active"=>false,
                    "in_use"=>false,
                    "updated_at"=>Carbon::now()
                ]);*/
                $token->delete();

            }
        }
        //create token and send to user
        $newToken = new LoginToken();
        $token = rand(1000,9999);

        $newToken->user_id = $user->id;
        $newToken->token = $token;
        $newToken->token_expires_at = Carbon::now()->addMinutes(10); //a user can use the same token for an hours time
        $newToken->created_at = Carbon::now();
        $newToken->updated_at = Carbon::now();
        $newToken->save();


        session()->forget('otp_session');
        $request->session()->put('otp_session', encrypt($token));

        $message = "Dear ".$user->name.",".PHP_EOL."Your login OTP is: ".$newToken->token;

        dispatch(new Sms(
            '+254' . substr($user->phone, -9), $message, $user, true
        ));



        return redirect()->back()->with('success', 'A new login token has been created and sent to your mobile number.');

    }


    //logout link
    public function logout(){
         Auth::logout();
         return redirect('login');
    }
}
