<?php

namespace App\Http\Middleware;

use App\Jobs\Sms;
use App\LoginToken;
use App\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TwoFactorVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle_initial($request, Closure $next)
    {
        $user = auth()->user();
        $active_token = $user->login_token()->where('active', '=', true)->orderBy('id', 'desc')->first();
        //if no token is present redirect to 2fa to receive new token
        if ($active_token){
            //if token exists and is yet to expire, proceed to next request
            if ($active_token->token_expires_at > \Carbon\Carbon::now() and $active_token->in_use == true) {

                $active_token->update([
                    "token_expires_at"=>Carbon::parse($active_token->token_expires_at)->addMinutes(120), //adds 120 min to token to increase validity
                    "updated_at"=> Carbon::now()
                ]);

                return $next($request);
            }
        }

        //else create token and send to user
        $newToken = new LoginToken();
        $newToken->user_id = $user->id;
        $newToken->token = rand(1000,9999);
        $newToken->token_expires_at = Carbon::now()->addMinutes(120); //a user can use the same token for 3 hrs
        $newToken->created_at = Carbon::now();
        $newToken->updated_at = Carbon::now();
        $newToken->save();

        $message = "Dear ".$user->name.",".PHP_EOL."Your login OTP is: ".$newToken->token;
        $auser = Auth::user();
        dispatch(new Sms(
            '+254' . substr($user->phone, -9), $message, $auser, true
        ));

        return redirect('/2fa');
    }

    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if (config('app.env') == 'local' || $user->email !== 'litsa@admin.com') {
            return $next($request);
        }

        $active_token = $user->login_token()->where(['active' => true])->first();

        //if no token is present redirect to 2fa to receive new token
        if ($active_token){
            if ($active_token->in_use) {
                return $next($request);
            }
        }

        $atokens = $user->login_token()->get();
        if ($atokens){
            foreach ($atokens as $token){
                $token->delete();
            }
        }

        //else create token and send to user
        $newToken = new LoginToken();
        $token = rand(1000,9999);
        $newToken->user_id = $user->id;
        $newToken->token = $token;
        $newToken->token_expires_at = Carbon::now()->addMinutes(120); //a user can use the same token for 3 hrs
        $newToken->created_at = Carbon::now();
        $newToken->updated_at = Carbon::now();
        $newToken->save();

        $message = "Dear ".$user->name.",".PHP_EOL."Your login OTP is: ".$newToken->token;
        $auser = Auth::user();
        dispatch(new Sms(
            '+254' . substr($user->phone, -9), $message, $auser, true
        ));

        return redirect('/2fa');
    }

}
