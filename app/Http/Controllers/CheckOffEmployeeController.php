<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\CheckOffEmployee;
use App\models\CheckOffEmployeeNextOfKin;
use App\models\CheckOffEmployeeReferee;
use App\models\CheckOffEmployeeSms;
use App\models\CheckOffEmployer;
use App\models\CheckOffLoan;
use App\models\CheckOffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class CheckOffEmployeeController extends Controller
{
    public function verify(){
        return view('check-off.verify');
    }

    public function verify_post(Request $request){
        $validator = Validator::make($request->all(), [
            "institution_code" => "required|string|min:2|max:8",
            "phone_number" => "required|string|min:10|max:14",
        ]);
        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }
        $institution_code = Str::upper($request->input('institution_code'));
        $employer = CheckOffEmployer::query()->where('code', '=', $institution_code)->first();
        if ($employer) {
            $verification_code = mt_rand(1000,99999);
            $phone_number = '254' . substr($request->input('phone_number'), -9);
            $message = "Hello, Thank you for showing interest in our LITSA CREDIT Advance Loans Product. Your Verification Code is: $verification_code".PHP_EOL.
            "Kindly use this code to complete your application.";
            $data = CheckOffEmployeeSms::query()->create([
                'employee_id' => null,
                'sms' => $message,
                'phone_number' => $phone_number
            ]);
            Session::put('advance_loan_verification', ['code' => $verification_code, 'sent_at' => now(), 'phone_number' => $phone_number, 'data' => $data]);
            dispatch(new Sms($phone_number, $message, null, true));
            return Redirect::route('check-off.employee.register', $employer->code)->with('success', 'Kindly Fill the form shown below to complete your LITSA CREDIT Advance Loan Application.');
        } else {
            return Redirect::back()->withErrors(['institution_code' => 'The Institution Code provided does not match our records.'])->withInput()
                ->with('error', "The Provided Institution Code '$institution_code' is not registered under our Institutions. Please try again with a valid code or Contact Customer care for support.");
        }
    }

    public function register($employerCode){
        $advance_loan_verification = Session::has('advance_loan_verification');

        if ($advance_loan_verification){
            $employer = CheckOffEmployer::query()->where('code', '=', $employerCode)->first();
            if($employer){
                $this->data['products'] = CheckOffProduct::all();
                $this->data['employer'] = $employer;
                return view('check-off.register', $this->data);
            }
            return Redirect::route('check-off.employee.verify')->with('error', "The Provided Institution Code '$employerCode' is not registered under our Institutions. Please try again with a valid code or Contact Customer care for support.");
        } else {
            return Redirect::route('check-off.employee.verify')->with('warning', "Kindly fill in the form below to proceed.");
        }
    }

    public function register_post(Request $request, $institution_code){
        $validator = Validator::make($request->all(), [
            "first_name" => "required|string|min:2|max:50",
            "last_name" => "required|string|min:2|max:50",
            "id_no" => "required|string|min:2|max:20",
            "dob" => "required|date|max:".now()->subYears(18)->format('Y-m-d'),
            "gender" => "required|string|min:2|max:20|in:Male,Female",
            "marital_status" => "required|string|min:2|max:8|in:Married,Single",
            "phone" => "required|string|min:10|max:14",
            "verification_code" => "required|string|min:2|max:8",
            "email" => "required|email|min:2|max:50",
            "next_of_kin_name" => "required|string|min:2|max:50",
            "next_of_kin_phone_number" => "required|string|min:10|max:14",
            "next_of_kin_relationship" => "required|string|min:2|max:20",
            "institution" => "required|string|min:2|max:100",
            "terms_of_employment" => "required|string|min:2|max:50|in:Permanent,Casual,Contract",
            "date_of_employment" => "required|date",
            "referee_name" => "required|string|min:2|max:50",
            "referee_phone_number" => "required|string|min:10|max:14",
            "referee_relationship" => "required|string|min:2|max:20",
            "referee_occupation" => "required|string|min:2|max:50",
            "product_id" => "required|integer|exists:check_off_products,id",
            "loan_amount" => "required|integer",
        ]);
        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }
        $employer = CheckOffEmployer::query()->where('code', '=', $institution_code)->first();
        if ($employer){
            //check verification code
            $advance_loan_verification = Session::get('advance_loan_verification');

            if($advance_loan_verification){
                $valid_code = $advance_loan_verification['code'];

                if ($request->input('verification_code') == $valid_code){
                    //check employee
                    $employee = CheckOffEmployee::query()->where('phone_number', '=', $request->input('phone'))->first();
                    if ($employee){
                        //check if there is an existing incomplete loan
                        $incomplete_loan = CheckOffLoan::query()
                            ->where('employee_id', '=', $employee->id)
                            ->where('settled', '=', false)->exists();
                        if($incomplete_loan){
                            return Redirect::route('check-off.employee.verify')->with('warning', 'Hello '.$employee->full_name. ' it seems like you still have an unsettled loan, kindly contact your Credit Officer for assistance before applying for a new loan.');
                        }
                    } else {
                        $referee = CheckOffEmployeeReferee::query()->create([
                            'name' => $request->input('referee_name'),
                            'address' => null,
                            'phone_number' => '254' . substr($request->input('referee_phone_number'), -9),
                            'relationship' => $request->input('referee_relationship'),
                            'occupation' => $request->input('referee_occupation')
                        ]);

                        $next_of_kin = CheckOffEmployeeNextOfKin::query()->create([
                            'name' => $request->input('next_of_kin_name'),
                            'phone_number' => $request->input('next_of_kin_phone_number'),
                            'relationship' => $request->input('next_of_kin_relationship')
                        ]);

                        $employee = CheckOffEmployee::query()->create([
                            'referee_id' => $referee->id,
                            'next_of_kin_id' => $next_of_kin->id,
                            'employer_id' => $employer->id,
                            'first_name' => $request->input('first_name'),
                            'last_name' => $request->input('last_name'),
                            'phone_number' => $request->input('phone'),
                            'id_number' => $request->input('id_no'),
                            'primary_email' => $request->input('email'),
                            'institution_email' => $request->input('email'),
                            'dob' => $request->input('dob'),
                            'gender' => $request->input('gender'),
                            'marital_status' => $request->input('marital_status'),
                            'date_of_employment' => $request->input('date_of_employment'),
                            'terms_of_employment' => $request->input('terms_of_employment'),
                        ]);
                    }

                    $product = CheckOffProduct::query()->find($request->input('product_id'));

                    $loan_amount = $request->input('loan_amount');

                    $interest_amount = $loan_amount * ($product->interest / 100);

                    $payable = $loan_amount + $interest_amount;

                    CheckOffLoan::query()->create([
                        'product_id' => $product->id,
                        'employee_id' => $employee->id,
                        'loan_amount' => $loan_amount,
                        'end_date' => now()->addDays($product->period),
                        'effective_date' => now(),
                        'approved' => false,
                        'approved_date' => null,
                        'approved_by' => null,
                        'settled' => false,
                        'settled_at' => null,
                        'interest' => $interest_amount,
                        'total_amount' => $payable
                    ]);

                    //sms employee
                    $message = "Hello $employee->full_name, Your application has been submitted successfully. Kindly be patient as we process your request.";

                    CheckOffEmployeeSms::query()->whereNull('employee_id')
                        ->where('phone_number', '=', $employee->phone_number)
                        ->update(['employee_id' => $employee->id]);

                    CheckOffEmployeeSms::query()->create([
                        'employee_id' => $employee->id,
                        'sms' => $message,
                        'phone_number' => $employee->phone_number
                    ]);

                    dispatch(new Sms($employee->phone_number, $message, null, true));

                    //sms litsa team

                    Session::forget('advance_loan_verification');

                    return Redirect::route('check-off.employee.verify')
                        ->with('success', 'Hello '.$employee->full_name. ' we have received your application and a member of our team will get back to you shortly.');
                } else {
                    return Redirect::back()->withInput()->withErrors(['verification_code' => 'The OTP Code provided does not match our records.'])
                        ->with('error', 'The provided Verification Code is Invalid, kindly try again. If issue persists, contact customer care for assistance.');
                }
            } else {
                return Redirect::route('check-off.employee.verify')->with('warning', 'Kindly fill in the form below to proceed.');
            }
        }
        else {
            return Redirect::route('check-off.employee.verify')->with('warning', 'Invalid Institution Code Provided, please fill in the form below to proceed.');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return array|false|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function index()
    {
        $this->data['title'] = "Check Off Loan Employees";
        $this->data['sub_title'] = "List of all employees who have applied for Advance Loans";
        return view('pages.check-off.employees.index', $this->data);
    }

    /**
     * Data Table
     * @return mixed
     * @throws \Exception
     */
    public function data(){
        $lo = CheckOffEmployee::query()->with('employer:id,name')->select('check_off_employees.*');
        return DataTables::of($lo)
            ->editColumn('full_name', function ($lo) {
                return $lo->first_name . ' ' . $lo->last_name;
            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;
                return '<div class="btn-group text-center">
                                <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                    <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                         <li><a href="#"><i class="feather icon-user-check text-secondary"></i> View Details</a></li>
                                  </ul>
                        </div>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }
}
