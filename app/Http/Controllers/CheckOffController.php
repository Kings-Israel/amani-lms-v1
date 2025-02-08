<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\CheckOffEmployeeSms;
use App\models\CheckOffEmployer;
use App\models\CheckOffLoan;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CheckOffController extends Controller
{
    public function login()
    {
        $this->data['title'] = "Check Off Login";
        $this->data['sub_title'] = "List of all employers whose employees can apply for Advance Loans";
        return view('pages.check-off.employers.auth.login', $this->data);
    }

    public function login_post(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        /*if (Auth::guard('employers')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }*/
        //dd('here');

        $user = CheckOffEmployer::where(['code' => $request->username])->orWhere('contact_phone_number', '0' . substr($request->username, -9))->first();
        if ($user) {
           //dd($user->password, $request->password, Hash::check($request->password, $user->password));


            if (Hash::check($request->password, $user->password)) {
                //send otp
                $this->send_otp($user);

                return Redirect::route('check-off.employer.2fa', ['username'=>encrypt($request->username),'password'=>encrypt($request->password)])->with('message', 'State saved correctly!!!');

            } else{
             //   return back()->with('message', 'State saved correctly!!!')
                    return back()->with(['error' => 'The provided credentials do not match our records'])->onlyInput('username');

            }
        } else{
            return back()->with(['error' => 'The provided credentials do not match our records'])->onlyInput('username');
        }



    }
    function send_otp($employer, $type=null){
        $token = rand(1000,9999);

        if ($type == "reset"){
            $message = "Reset Password link".PHP_EOL.route('check-off.password.reset', $token);

        } else{
            $message = "Dear Partner".PHP_EOL."Your login OTP is: ".$token;

        }

        dispatch(new Sms(
            '+254' . substr($employer->contact_phone_number, -9), $message, null, false
        ));
        DB::table('checkoff_employer_smses')->insert([
            'employer_id' => $employer->id,
            'message' => $message,
            'phone' => $employer->contact_phone_number,
            'created_at' => Carbon::now()
        ]);
        $employer->update(['otp' => $token]);
        return $token;
    }

    public function otp(Request $request)
    {
        $this->data['title'] = "OTP";
        $this->data['username'] = $request->username;
        $this->data['password'] = $request->password;


        $this->data['sub_title'] = "List of all employers whose employees can apply for Advance Loans";
        return view('pages.check-off.employers.auth.2fa', $this->data);
    }


    public function post_2fa(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'token' => 'required|min:4',
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $username = Decrypt($request->username);
        $password = Decrypt($request->password);

        $user = CheckOffEmployer::where(['code' => $username])->orWhere('contact_phone_number', '0' . substr($username, -9))->first();
       // dd($request->all());
        if ($user) {

            if (Auth::guard('employers')->attempt(['code' =>$username, 'password' =>$password, 'otp' => $request->token]) || Auth::guard('employers')->attempt(['contact_phone_number' =>'0' . substr($username, -9), 'password' =>$password, 'otp' => $request->token])) {
               // $request->session()->regenerate();

                $user->update(['otp' => null]);

                return Redirect::route('check-off.employer.dashboard')->with('message', 'State saved correctly!!!');
            }


            else{
                //   return back()->with('message', 'State saved correctly!!!')
                return back()->with(['error' => 'The provided credentials do not match our records'])->onlyInput('username');

            }
        } else{
            return back()->with(['error' => 'The provided credentials do not match our records'])->onlyInput('username');
        }        //dd($ses);


    }

    public function dashboard(){
        //dd(Auth::guard('employers')->user());

        $this->data['title'] = "Approve Loans";

        $this->data['sub_title'] = "";
        return view('pages.check-off.employers.dashboard', $this->data);
    }


    public function logout(Request $request){

        Auth::guard('employers')->logout();
        return redirect()->route('check-off.employer.login');

    }

    public function approve_loans_data(){
        $lo = CheckOffLoan::query()
            ->join('check_off_employees', function ($join)  {
                $join->on('check_off_employees.id', '=', 'check_off_loans.employee_id')
                    ->where('check_off_employees.employer_id', '=', Auth::guard('employers')->user()->id)
                    ->where('check_off_loans.disbursed', '=', false)
                    ->where([['check_off_loans.approved', '=', false]])
                    ->where('check_off_loans.rejected', '=', false);
            })            ->with('employee.employer')
            ->with('product:id,name')
            ->select('check_off_loans.*');
        return DataTables::of($lo)
            ->addColumn('full_name', function ($lo) {
                return $lo->employee->first_name . ' ' . $lo->employee->last_name;
            })
            ->addColumn('balance', function ($lo) {
                return $lo->balance;
            })
            ->addColumn('amount_paid', function ($lo) {
                return $lo->amount_paid;
            })
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'APPROVED';
                }
                else if ($lo->rejected) {
                    return 'REJECTED';
                }
                else {
                    return 'PENDING APPROVAL';
                }
            })
            ->editColumn('settled', function ($lo) {
                if ($lo->settled) {
                    return 'SETTLED';
                }
                else if ($lo->rejected) {
                    return 'REJECTED';
                }
                else {
                    return 'INCOMPLETE';
                }
            })
            ->addColumn('action', function ($lo) {
                $action_buttons = '<div class="btn-group text-center">
                                        <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                             <li><a href="'.route('check_off_employer.loans.mark_as_approved', $lo->id).'"><i class="feather icon-user-check text-secondary"></i> Mark as Approved </a></li>
                                             <li><a onclick="return confirm(`Are you sure you want to mark this loan as rejected? Once confirmed this action cannot be undone and a notification will be sent out to the customer.`)"
                                             href="'.route('check_off_employer.loans.mark_as_rejected', $lo->id).'"><i class="feather icon-user-check text-warning"></i> Mark as Rejected </a></li>

                                             <li><a onclick="return confirm(`Are you sure you want to delete thios loan? Once confirmed this action cannot be undone.`)"
                                             href="'.route('check_off_employer_loans.destroy', $lo->id).'"><i class="feather icon-user-check text-warning"></i> Delete Loan </a></li>

                                            </form>

                                          </ul>
                                        </div>';
                return $action_buttons;
            })
            ->rawColumns(['action'])
            ->toJson();
    }
    public function loans(){
        //dd(Auth::guard('employers')->user());

        $this->data['title'] = "loans";

        $this->data['sub_title'] = "";
        return view('pages.check-off.employers.all_loans', $this->data);
    }
    public function loans_data(){
        $lo = CheckOffLoan::query()
            ->join('check_off_employees', function ($join)  {
                $join->on('check_off_employees.id', '=', 'check_off_loans.employee_id')
                    ->where('check_off_employees.employer_id', '=', Auth::guard('employers')->user()->id);
                // ->where('loans.settled', '=', false);
            })            ->with('employee.employer')
            ->with('product:id,name')
            ->select('check_off_loans.*');
        return DataTables::of($lo)
            ->addColumn('full_name', function ($lo) {
                return $lo->employee->first_name . ' ' . $lo->employee->last_name;
            })
            ->addColumn('balance', function ($lo) {
                return $lo->balance;
            })
            ->addColumn('amount_paid', function ($lo) {
                return $lo->amount_paid;
            })
            ->editColumn('approved', function ($lo) {
                if ($lo->approved) {
                    return 'APPROVED';
                }
                else if ($lo->rejected) {
                    return 'REJECTED';
                }
                else {
                    return 'PENDING APPROVAL';
                }
            })
            ->editColumn('settled', function ($lo) {
                if ($lo->settled) {
                    return 'SETTLED';
                }
                else if ($lo->rejected) {
                    return 'REJECTED';
                }
                else {
                    return 'INCOMPLETE';
                }
            })
            ->addColumn('action', function ($lo) {
                $action_buttons = null;
                if ($lo->approved){
                    /*$action_buttons = '<div class="btn-group text-center">
                                        <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                          </ul>
                                    </div>';*/
                }
                else if ($lo->rejected){
                    $action_buttons = '<div class="btn-group text-center">
                                        <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                            <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                <li>
                                                    <a href="#" onclick="event.preventDefault();
                                                    document.getElementById(`delete-form-'. $lo->id .'`).submit();">
                                                    <i class="feather icon-user text-danger" ></i> Delete Loan Request </a>
                                                </li>

                                                <form id="delete-form-'. $lo->id .'" action="'. route('check_off_employer_loans.destroy', $lo->id) .'"
                                                     method="POST" style="display: none;">
                                                    '.csrf_field().'
                                                    <input type="hidden" name="_method" value="DELETE">
                                                </form>
                                            </ul>
                                    </div>';
                }
                else {
                    $action_buttons = '<div class="btn-group text-center">
                                        <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                             <li><a href="'.route('check_off_employer.loans.mark_as_approved', $lo->id).'"><i class="feather icon-user-check text-secondary"></i> Mark as Approved </a></li>
                                             <li><a onclick="return confirm(`Are you sure you want to mark this loan as rejected? Once confirmed this action cannot be undone and a notification will be sent out to the customer.`)"
                                             href="'.route('check_off_employer.loans.mark_as_rejected', $lo->id).'"><i class="feather icon-user-check text-warning"></i> Mark as Rejected </a></li>
                                             <li><a href="#"><i class="feather icon-user-check text-danger" onclick="event.preventDefault();
                                                document.getElementById(`delete-form-'. $lo->id .'`).submit();"></i> Delete Loan Request </a></li>
                                        <form id="delete-form-'. $lo->id .'" action="'. route('check_off_employer_loans.destroy', $lo->id) .'"
                                                 method="POST" style="display: none;">
                                                '.csrf_field().'
                                                <input type="hidden" name="method" value="DELETE">
                                            </form>

                                          </ul>
                                        </div>';
                }
                return $action_buttons;
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * @param $checkOffLoanId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function mark_as_approved($checkOffLoanId): \Illuminate\Http\RedirectResponse
    {
        $loan = CheckOffLoan::query()->find($checkOffLoanId);
        $approved = $loan->approved;
        if ($approved){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as approved.');
        } else {
            $loan->update([
                'approved' => true,
                'employer_approval_id' => Auth::guard('employers')->id(),
                'approved_date' => now()
            ]);
            return Redirect::back()->with('success',' The specified loan ('.$checkOffLoanId.') has successfully been marked as approved');
        }
    }

    /**
     * @param $checkOffLoanId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function mark_as_rejected($checkOffLoanId): \Illuminate\Http\RedirectResponse
    {
        $loan = CheckOffLoan::query()->with('employee')->find($checkOffLoanId);
        $approved = $loan->approved;
        if ($approved){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as approved and can therefore not be rejected.');
        } else {
            $loan->update([
                'rejected' => true,
                'rejected_by_employer' => Auth::guard('employers')->id(),
                'rejected_at' => now()
            ]);
            if ($loan->employee){
                //sms customer
                $phone_number = '254' . substr($loan->employee->phone_number, -9);
                $message = "Hello, Thank you for showing interest in our LITSA CREDIT Advance Loans Product. Unfortunately, your submitted loan has failed to pass our verification process and has therefore been rejected. A member of our team will reach out to you for further clarification.";
                CheckOffEmployeeSms::query()->create([
                    'employee_id' => $loan->employee->id,
                    'sms' => $message,
                    'phone_number' => $phone_number
                ]);
                dispatch(new Sms($phone_number, $message, null, true));
            }
            return Redirect::back()->with('success',' The specified loan ('.$checkOffLoanId.') has successfully been marked as rejected');
        }
    }

    /**
     * @param $checkOffLoanId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy_loan($checkOffLoanId): \Illuminate\Http\RedirectResponse
    {
        $loan = CheckOffLoan::query()->with('employee')->find($checkOffLoanId);
        $approved = $loan->approved;
        $disbursed = $loan->disbursed;
        if ($approved){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as approved and can therefore not be deleted.');
        }
        else if ($disbursed){
            return Redirect::back()->with('warning', ' The specified loan ('.$checkOffLoanId.') is already marked as disbursed and can therefore not be deleted.');
        }
        else {
            $loan->delete();
            return Redirect::back()->with('success',' The specified loan ('.$checkOffLoanId.') has successfully been deleted successfully');
        }
    }


    /**
     *update password
     *
     *
     */
    public function password_request(){
        $this->data['title'] = "OTP";

        $this->data['sub_title'] = "List of all employers whose employees can apply for Advance Loans";
        return view('pages.check-off.employers.auth.password_request', $this->data);

    }
    public function update_password_request(Request $request){
        $user = CheckOffEmployer::where(['code' => $request->username])->orWhere('contact_phone_number', '0' . substr($request->username, -9))->first();
        if ($user){
           $url =  $this->send_otp($user, 'reset');
          // dd($url);
            return back()->with(['success' => 'Sent your resent link to your registered phone']);



        } else{
            return back()->with(['error' => 'No user found with username or phone number '.$request->username])->onlyInput('username');


        }

    }

    public function reset($token){
        $this->data['title'] = "Reset";
        $this->data['token'] = encrypt($token);
        $this->data['sub_title'] = "";
        $employer = CheckOffEmployer::where('otp', $token)->first();
        if (!$employer){
            abort(404);
        }

        return view('pages.check-off.employers.auth.reset', $this->data);
    }

    public function update_password_confirm(Request $request){
        $request->validate([
            'token' => 'required',
            'username' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

    /*    $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );*/
        //dd($request->all());



        $user = CheckOffEmployer::where(['code' => $request->username])->orWhere('contact_phone_number', '0' . substr($request->username, -9))->first();
        if ($user){
            //check if the code is correct
            if ($user->otp == decrypt($request->token)){
               // dd($request->password);
                $user->update(['password' => Hash::make($request->password), 'otp' => null]);
                return Redirect::route('check-off.employer.login')->with(['success' => 'Successfully edited your password!!!']);

            } else{
               // return back()->with(['error' => 'your used reset link is invalid']);
                return Redirect::route('check-off.employer.login')->with(['error' => 'your used reset link is invalid']);


            }


        }
        else{
            return back()->with(['error' => 'No user found with username or phone number '.$request->username]);

        }

    }



}
