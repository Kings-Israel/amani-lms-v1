<?php

namespace App\Http\Controllers;

use App\Jobs\Sms;
use App\models\Branch;
use App\models\Business_type;
use App\models\CheckOffEmployeeSms;
use App\models\CheckOffEmployer;
use App\models\Customer;
use App\models\Guarantor;
use App\models\Industry;
use App\models\Loan;
use App\models\LoanType;
use App\models\Product;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoanReapplicationController extends Controller
{
    public function prompt_verification(){
        return view('customer-reapplications.prompt-verification');
    }

    public function prompt_verification_post(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            "id_number" => "required|string|min:7|max:14",
        ]);
        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }
        $id_number = $request->get('id_number');
        $customer = Customer::query()->where('id_no', '=', $id_number)->first();
        if ($customer and $customer->status) {
            $verification_code = mt_rand(1000,99999);
            $phone_number = $customer->phone;
            $message = "Hello $customer->fullName. Thank you for showing interest in our LITSA CREDIT Loan Product. Your Verification Code is: $verification_code".PHP_EOL.
                "Kindly use this code to proceed with your application.";
            Session::put('customer_reapplication_verification', ['code' => $verification_code, 'sent_at' => now(), 'phone_number' => $phone_number, 'id_number' => $id_number]);
            dispatch(new Sms($phone_number, $message, $customer, false));
            return Redirect::route('customer-reapplications.verify', $customer->id_no)
                ->with('success', 'Kindly Enter the Code sent to your phone number to proceed with your LITSA CREDIT Advance Loan Application.');
        } else {
            return Redirect::back()->withErrors(['id_number' => 'The ID Number provided does not match our records.'])->withInput()
                ->with('error', "The Provided ID Number '$id_number' is not registered under our Active Customers. Please try again with a valid id number or Contact Customer care for support.");
        }
    }

    public function verify($customer_id_number){
        if (Session::has('customer_reapplication_verification')){
            $data = Session::get('customer_reapplication_verification');
            if (isset($data['id_number'])){
                $id_no = $data['id_number'];
                if($customer_id_number == $id_no){
                    return view('customer-reapplications.verify');
                } else {
                    Session::forget('customer_reapplication_verification');
                    return Redirect::route('customer-reapplications.prompt-verification')->with('warning', 'Kindly Fill in your ID Number to proceed');
                }
            } else {
                Session::forget('customer_reapplication_verification');
                return Redirect::route('customer-reapplications.prompt-verification')->with('warning', 'Kindly Fill in your ID Number to proceed');
            }
        }
        return Redirect::route('customer-reapplications.prompt-verification')->with('warning', 'Kindly Fill in your ID Number to proceed');
    }

    public function verify_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "verification_code" => "required|string|min:2|max:8",
        ]);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }
        //check verification code
        $customer_reapplication_verification = Session::get('customer_reapplication_verification');
        if ($customer_reapplication_verification) {
            $valid_code = $customer_reapplication_verification['code'];
            if ($request->input('verification_code') == $valid_code) {
                $data = Session::get('customer_reapplication_verification');
                $id_no = $data['id_number'];
                $customer = Customer::query()->where('id_no', '=', $id_no)->first();
                if ($customer){
                    Session::put('passed_loan_application_verification', true);
                    $unsettled_loan = Loan::query()
                        ->where('customer_id', '=', $customer->id)
                        ->where('settled', '=', false)
                        ->latest()
                        ->first();
                    if ($unsettled_loan){
                        session()->flash('warning', 'Sorry, You do not qualify for a loan reapplication as you have an incomplete loan.');
                    } else {
                        session()->flash('success', 'You Qualify for a LITSA CREDIT Loan Application. Kindly fill in the form below to proceed');
                    }
                    return Redirect::route('customer-reapplications.application', $customer->id_no);
                }
                else {
                    return Redirect::route('customer-reapplications.prompt-verification')
                        ->withErrors(['id_number' => 'The ID Number provided does not match our records.'])
                        ->with('error', "The Provided ID Number is not registered under our Customers. Please try again with a valid id number or Contact Customer care for support.");
                }
            }
            else {
                return Redirect::back()->withInput()->withErrors(['verification_code' => 'The OTP Code provided does not match our records.'])
                    ->with('error', 'The provided Verification Code is Invalid, kindly try again. If issue persists, contact customer care for assistance.');
            }
        }
        else {
            return Redirect::route('customer-reapplications.prompt-verification')->with('warning', 'Kindly fill in the form below to proceed.');
        }
    }

    public function application($customer_id_number){
        if (Session::has('passed_loan_application_verification') and Session::get('passed_loan_application_verification')){
            $customer = Customer::query()->with('guarantor')
                ->where('id_no', '=', $customer_id_number)
                ->first();
            $current_guarantor = $customer->guarantor;
            $has_incomplete_loans = false;
            $unsettled_loan = Loan::query()
                ->where('customer_id', '=', $customer->id)
                ->where('settled', '=', false)
                ->latest()
                ->first();
            if ($unsettled_loan){
                $has_incomplete_loans = true;
                session()->flash('warning', 'Sorry, You do not qualify for a loan reapplication as you have an incomplete loan.');

            } else {
                session()->flash('success', 'You Qualify for a LITSA CREDIT Loan Application. Kindly fill in the form below to proceed');
            }
            $this->data['has_incomplete_loans'] = $has_incomplete_loans;
            $this->data['unsettled_loan'] = $unsettled_loan;
            $this->data['customer_identifier'] = encrypt($customer->id);
            $this->data['customer'] = $customer;
            $this->data['guarantor'] = $current_guarantor;
            $this->data['products'] = Product::all();
            $this->data['loan_types'] = LoanType::all();
            $this->data['industries'] = Industry::all();
            $this->data['businesses'] = Business_type::all();
            return view('customer-reapplications.application-form', $this->data);
        } else {
            Session::forget('customer_reapplication_verification');
            return Redirect::route('customer-reapplications.prompt-verification')->with('warning', 'Kindly Fill in your ID Number to proceed');
        }
    }

    public function submit_application(Request $request, $customer_identifier): \Illuminate\Http\RedirectResponse
    {
        $customer_id = decrypt($customer_identifier);
        if ($customer_id){
            $customer = Customer::query()->find($customer_id);
            if ($customer){
                //dd($request->all());
                $validator = Validator::make($request->all(), [
                    'full_name' => 'required|min:3',
                    'id_no' => 'required|exists:customers,id_no',
                    'phone' => 'required|exists:customers,phone',
                    'product_id' => 'required|exists:products,id',
                    'loan_type' => 'required|exists:loan_types,id',
                    'purpose' => 'required',
                    'loan_amount' => 'required|integer|min:1|',

                    'gname' => 'required|string|min:3|max:200',
                    'gphone' => 'required|string|min:3|max:200',
                    'gid' => 'required|string|min:3|max:200',
                    'gdob' => 'required|date',
                    'location' => 'required|string|min:3|max:200',
                    'marital_status' => 'required|string|min:3|max:200',
                    'industry_id' => 'required|exists:industries,id',
                    'business_id' => 'required|exists:business_types,id',
                    'terms_and_conditions' => 'required',
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
                }
                $product = Product::query()->find($request->post('product_id'));
                $end_date = Carbon::now()->addDays($product->duration);
                $loan_type = LoanType::query()->find($request->post('loan_type'));
                $loan = Loan::where(['customer_id' => $customer->id, 'settled' => false])->first();
                $branch = Branch::query()->find($customer->branch_id);
                if (!isset($loan)) {
//check if loan amouunt is more than 20k
                    if ($request->input('loan_amount') > 20000 || $request->input('loan_amount') > $customer->prequalified_amount){
                        return Redirect::route('customer-reapplications.prompt-verification')->with('error', 'Application amount cannot be greater than '.$customer->prequalified_amount);

                    }

                    $loan = Loan::query()->create([
                        'loan_amount' => $request->input('loan_amount'),
                        'product_id' => $product->id,
                        'customer_id' => $customer->id,
                        'loan_type_id' => $loan_type->id,
                        'date_created' => Carbon::now('Africa/Nairobi'),
                        'purpose' => $request->input('purpose'),
                        'loan_account' => $branch->bname . "-" . date('m/d') . "-" . mt_rand(10, 10000),
                        'end_date' => $end_date,
                        'created_by' => null,
                        'self_application' => true,
                    ]);

                    $guarantor = Guarantor::query()->updateOrCreate(
                        [
                            'gphone' => '254' . substr($request->input('gphone'), -9),
                            'gid' => $request->input('gid'),
                        ],
                        [
                        'gname' => $request->input('gname'),
                        'gdob' => Carbon::parse($request->input('gdob')),
                        'location' => $request->input('location'),
                        'latitude' => 0,
                        'longitude' => 0,
                        'marital_status' => $request->input('marital_status'),
                        'industry_id' => $request->input('industry_id'),
                        'business_id' => $request->input('business_id'),
                    ]);

                    $customer->update(['guarantor_id' => $guarantor->id]);

                    $guarantor_sms = "Hello $guarantor->gname, this is a notification to inform you that $customer->fullName has added you as a Guarantor on LITSA CREDIT. For any queries regarding this application contact our Customer Service via +254100100114";

                    dispatch(new Sms($customer->phone, $guarantor_sms, null, false));

                    //sms Customer
                    $message = "Hello $customer->fullName. Your Application has been received successfully for processing under Loan Account $loan->loan_account. For any queries regarding your application contact your Credit Officer.";

                    dispatch(new Sms($customer->phone, $message, $customer, false));

                    //sms Credit Officer
                    $credit_officer = User::query()->find($customer->field_agent_id);
                    if ($credit_officer){
                        $co_message = "Customer Loan Reapplication Notification".
                            PHP_EOL . "Customer: $customer->fullName" .
                            PHP_EOL . "Contact: $customer->phone" .
                            PHP_EOL . "Loan Amount: $loan->loan_amount";
                        dispatch(new Sms('+254' . substr($credit_officer->phone, -9), $co_message, $credit_officer, true));
                    }

                    Session::forget('customer_reapplication_verification');
                    Session::forget('passed_loan_application_verification');
                    return Redirect::route('customer-reapplications.prompt-verification')->with('success', 'Your application has been received. Kindly be patient as we process your request.');
                }
                else{
                    Session::forget('customer_reapplication_verification');
                    Session::forget('passed_loan_application_verification');
                    return Redirect::route('customer-reapplications.prompt-verification')->with('error', 'Your application failed as you still have an unsettled loan. Kindly Contact your Credit Officer for more details.');
                }
            }
            else {
                Session::forget('customer_reapplication_verification');
                Session::forget('passed_loan_application_verification');
                return Redirect::route('customer-reapplications.prompt-verification')->with('error', 'Sorry, something went wrong on our side. Please try again later. Contact your Credit Officer for further assistance');
            }
        }
        else {
            Session::forget('customer_reapplication_verification');
            Session::forget('passed_loan_application_verification');
            return Redirect::route('customer-reapplications.prompt-verification')->with('error', 'Sorry, something went wrong on our side. Please try again later. Contact your Credit Officer for further assistance');
        }
    }

    public function close_application(): \Illuminate\Http\RedirectResponse
    {
        Session::forget('customer_reapplication_verification');
        Session::forget('passed_loan_application_verification');
        return Redirect::route('customer-reapplications.prompt-verification')
            ->with('success', 'Your application failed as you still have an unsettled loan. Kindly Contact your Credit Officer for more details.');

    }
}
