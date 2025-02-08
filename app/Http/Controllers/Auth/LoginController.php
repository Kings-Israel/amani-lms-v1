<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            $atokens = auth()->user()->login_token()->get();
            if ($atokens){
                foreach ($atokens as $token){
                    $token->delete();
                }
            }
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }


    public function authenticated(Request $request, $user)
    {
        if (!$user->status) {
            auth()->logout();
            return back()->with(['error' => 'Your Account has been deactivated. Reach admin for more details']);
        }

        if($user->hasRole('Intern')){
            auth()->logout();
            return back()->with(['error' => 'You have No access to the system!']);
        }

        return redirect()->intended($this->redirectPath());
    }

    function handle_otp(){
        $user = auth()->user();
        $active_token = $user->login_token()->where('active', '=', true)->orderBy('id', 'desc')->first();
        //if no token is present redirect to 2fa to receive new token
        if ($active_token){
            //if token exists and is yet to expire, proceed to next request
           // if ($active_token->token_expires_at > \Carbon\Carbon::now() and $active_token->in_use == true) {
            if ($active_token->in_use == true) {
                $active_token->update([
                    "token_expires_at"=>Carbon::parse($active_token->token_expires_at)->addMinutes(10), //adds 7 min to token to increase validity
                    "updated_at"=> Carbon::now()
                ]);
               // return $next($request);
            }
        }
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
