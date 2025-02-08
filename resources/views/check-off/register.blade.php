@extends('auth.master')
@section('css')
    <style>
        h6 {
            font-weight: bold;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        @include('layouts.alert')
                    </div>
                </div>
                <div class="text-center">
                    <img src="{{asset('assets/images/logo.png')}}"  style="height: 200px;width: 250px; margin-top:-25px"  alt="small-logo.png">
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5>Advance Loan Application</h5>
                    </div>
                    <div class="card-block">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="wizard3">
                                    <section>
                                        <form class="wizard-form" id="design-wizard" action="{{route('check-off.employee.register_post', $employer->code)}}" method="post" enctype="multipart/form-data">
                                            @csrf
                                            <h6>Applicant Details</h6>
                                            <fieldset>
                                                <div class="form-group row">
                                                    <div class="col-md-4 my-1">
                                                        <label for="first_name" class="block">First Name</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user"></i></span>
                                                            <input id="first_name" type="text" name="first_name" value="{{ old('first_name')}}" class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}" required placeholder="First Name">
                                                        </div>
                                                        @if ($errors->has('first_name'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('first_name') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="last_name" class="block">Last Name</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user"></i></span>
                                                            <input id="last_name" type="text" name="last_name" value="{{ old('last_name')}}" class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}" required placeholder="First Name">
                                                        </div>
                                                        @if ($errors->has('last_name'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('last_name') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- ID -->
                                                    <div class="col-md-4 my-1">
                                                        <label class="block">National ID Number</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-envelope-open"></i></span>
                                                            <input type="number" id="id_no" name="id_no" value="{{ old('id_no')}}" class="form-control{{ $errors->has('id_no') ? ' is-invalid' : '' }}" required placeholder="ID NO">
                                                        </div>
                                                        @if ($errors->has('id_no'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('id_no') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="gender" class="block">Gender</label>
                                                        <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="icofont icofont-user"></i>
                                                        </span>
                                                            <select id="gender" name="gender" class="js-example-basic-single form-control{{ $errors->has('gender') ? ' is-invalid' : '' }}" required>
                                                                <option value="" selected disabled>Specify Gender</option>
                                                                <option value="Male" {{ (old("gender") == "Male" ? "selected":"") }}>Male</option>
                                                                <option value="Female" {{ (old("gender") == "Female" ? "selected":"") }}>Female</option>
                                                            </select>
                                                        </div>
                                                        @if ($errors->has('gender'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('gender') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label class="block">Date Of Birth</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-calendar"></i></span>
                                                            <input type="date" id="dob" name="dob" value="{{ old('dob')}}" max="{{now()->subYears(18)->format('Y-m-d')}}" class="form-control{{ $errors->has('dob') ? ' is-invalid' : '' }}" required placeholder="Date Of Birth">
                                                        </div>
                                                        @if ($errors->has('dob'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('dob') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="marital_status" class="block">Marital Status</label>
                                                        <div class="input-group">
                                                        <span class="input-group-addon">
                                                            M
                                                        </span>
                                                            <select id="marital_status" name="marital_status" class="js-example-basic-single form-control{{ $errors->has('marital_status') ? ' is-invalid' : '' }}" required>
                                                                <option value="" selected disabled>Specify Marital Status</option>
                                                                <option value="Single" {{ (old("marital_status") == "Single" ? "selected":"") }}>Single</option>
                                                                <option value="Married" {{ (old("marital_status") == "Married" ? "selected":"") }}>Married</option>
                                                            </select>
                                                        </div>
                                                        @if ($errors->has('marital_status'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('marital_status') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="phone" class="block">Phone</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-ui-call"></i></span>
                                                            <input id="phone" value="{{ (\Illuminate\Support\Facades\Session::has('advance_loan_verification')) ? session('advance_loan_verification')['phone_number'] : ''}}"
                                                                   readonly type="text" name="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required placeholder="Phone">
                                                        </div>
                                                        @if ($errors->has('phone'))
                                                            <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('phone') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="verification_code" class="block">Verification Code</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-ui-message"></i></span>
                                                            <input id="verification_code" value="{{ old('verification_code')  }}"
                                                                   type="number" name="verification_code" class="form-control{{ $errors->has('verification_code') ? ' is-invalid' : '' }}" required placeholder="Verification Code sent to your mobile number">
                                                        </div>
                                                        @if ($errors->has('verification_code'))
                                                            <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('verification_code') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="email" class="block">Email Address</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon">@</span>
                                                            <input id="email" value="{{ old("email") }}"
                                                                   type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required placeholder="Valid Email Address">
                                                        </div>
                                                        @if ($errors->has('email'))
                                                            <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('email') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </fieldset>

                                            <h6>Next Of Kin Details</h6>
                                            <fieldset>
                                                <div class="form-group row">
                                                    <div class="col-md-4 my-1">
                                                        <label for="loan_amount" class="block">Full Name</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user-alt-1"></i></span>
                                                            <input  type="text" id="next_of_kin_name" value="{{ old('next_of_kin_name')}}" name="next_of_kin_name" class="form-control{{ $errors->has('next_of_kin_name') ? ' is-invalid' : '' }}" required placeholder="Next of Kin Name" >
                                                        </div>
                                                        @if ($errors->has('next_of_kin_name'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('next_of_kin_name') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="loan_amount" class="block">Phone Number</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user-alt-1"></i></span>
                                                            <input type="tel" pattern="\d{4}\d{3}\d{3}" title="'Phone Number (Format: 0712345678)'" id="next_of_kin_phone_number" value="{{ old('next_of_kin_phone_number')}}" name="next_of_kin_phone_number" class="form-control{{ $errors->has('next_of_kin_phone_number') ? ' is-invalid' : '' }}" required placeholder="Next of Kin Contact" >
                                                        </div>
                                                        @if ($errors->has('next_of_kin_phone_number'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('next_of_kin_phone_number') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="next_of_kin_relationship" class="block">Relationship</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user-alt-1"></i></span>
                                                            <input  type="text" id="next_of_kin_relationship" value="{{ old('next_of_kin_relationship')}}" name="next_of_kin_relationship" class="form-control{{ $errors->has('next_of_kin_relationship') ? ' is-invalid' : '' }}" required placeholder="Next of Kin Relationship eg. Parent, Spouse" >
                                                        </div>
                                                        @if ($errors->has('next_of_kin_relationship'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('next_of_kin_relationship') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </fieldset>

                                            <h6>Employer Details</h6>
                                            <fieldset>
                                                <div class="form-group row">
                                                    <div class="col-md-4 my-1">
                                                        <label for="institution" class="block">Institution Name</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user-alt-1"></i></span>
                                                            <input readonly type="text" id="institution" value="{{ $employer->name }}" name="institution" class="form-control{{ $errors->has('institution') ? ' is-invalid' : '' }}" required placeholder="Institution Name" >
                                                        </div>
                                                        @if ($errors->has('institution'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('institution') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="gender" class="block">Terms of Employment</label>
                                                        <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="icofont icofont-paper-clip"></i>
                                                        </span>
                                                            <select id="terms_of_employment" name="terms_of_employment" class="js-example-basic-single form-control{{ $errors->has('terms_of_employment') ? ' is-invalid' : '' }}" required>
                                                                <option value="" selected disabled>Specify Terms of Employment</option>
                                                                <option value="Permanent" {{ (old("terms_of_employment") == "Permanent" ? "selected":"") }}>Permanent</option>
                                                                <option value="Contract" {{ (old("terms_of_employment") == "Contract" ? "selected":"") }}>Contract</option>
                                                                <option value="Casual" {{ (old("terms_of_employment") == "Casual" ? "selected":"") }}>Casual</option>
                                                            </select>
                                                        </div>
                                                        @if ($errors->has('terms_of_employment'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('terms_of_employment') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label class="block">Date Of Employment</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-calendar"></i></span>
                                                            <input type="date" id="date_of_employment" name="date_of_employment" value="{{ old('date_of_employment')}}" max="{{now()->format('Y-m-d')}}" class="form-control{{ $errors->has('date_of_employment') ? ' is-invalid' : '' }}" required placeholder="Date Of Employment">
                                                        </div>
                                                        @if ($errors->has('date_of_employment'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('date_of_employment') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </fieldset>

                                            <h6>Referee Details</h6>
                                            <fieldset>
                                                <div class="form-group row">
                                                    <div class="col-md-3 my-1">
                                                        <label for="loan_amount" class="block">Full Name</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user-alt-1"></i></span>
                                                            <input  type="text" id="referee_name" value="{{ old('referee_name')}}" name="referee_name" class="form-control{{ $errors->has('referee_name') ? ' is-invalid' : '' }}" required placeholder="Referee Name" >
                                                        </div>
                                                        @if ($errors->has('referee_name'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('referee_name') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-3 my-1">
                                                        <label for="loan_amount" class="block">Phone Number</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user-alt-1"></i></span>
                                                            <input type="tel" pattern="\d{4}\d{3}\d{3}" title="'Phone Number (Format: 0712345678)'" id="referee_phone_number" value="{{ old('referee_phone_number')}}" name="referee_phone_number" class="form-control{{ $errors->has('referee_phone_number') ? ' is-invalid' : '' }}" required placeholder="Referee Contact" >
                                                        </div>
                                                        @if ($errors->has('referee_phone_number'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('referee_phone_number') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-3 my-1">
                                                        <label for="referee_relationship" class="block">Relationship</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user-alt-1"></i></span>
                                                            <input  type="text" id="referee_relationship" value="{{ old('referee_relationship')}}" name="referee_relationship" class="form-control{{ $errors->has('referee_relationship') ? ' is-invalid' : '' }}" required placeholder="Referee Relationship" >
                                                        </div>
                                                        @if ($errors->has('referee_relationship'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('referee_relationship') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-3 my-1">
                                                        <label for="referee_occupation" class="block">Occupation</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-user-alt-1"></i></span>
                                                            <input  type="text" id="referee_name" value="{{ old('referee_occupation')}}" name="referee_occupation" class="form-control{{ $errors->has('referee_occupation') ? ' is-invalid' : '' }}" required placeholder="Referee Occupation" >
                                                        </div>
                                                        @if ($errors->has('referee_occupation'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('referee_occupation') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </fieldset>

                                            <h6>Loan Details</h6>
                                            <fieldset>
                                                <div class="form-group row">
                                                    <div class="col-md-4 my-1">
                                                        <label for="product_id" class="block">Loan Product</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-social-pandora"></i></span>
                                                            <select id="product_id" name="product_id" class="js-example-basic-single form-control{{ $errors->has('product_id') ? ' is-invalid' : '' }}" required>
                                                                <option value="" selected disabled>Select Product</option>
                                                                @foreach($products as $product)
                                                                    <option data-interest="{{$product->interest}}" value="{{$product->id}}" {{ old('product_id') ? ((old('product_id') == $product->id) ? 'selected' : '') : $product->id }}" >
                                                                    {{$product->name .' - ' . $product->interest . '% Interest'}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        @if ($errors->has('product_id'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>
                                                                    {{ $errors->first('product_id') }}
                                                                </strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-4 my-1">
                                                        <label for="loan_amount" class="block">Loan Amount</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-money"></i></span>
                                                            <input  type="number" id="loan_amount" value="{{ old('loan_amount', isset($loan->loan_amount) ? $loan->loan_amount : '')}}" name="loan_amount" class="form-control{{ $errors->has('loan_amount') ? ' is-invalid' : '' }}" required placeholder="Loan Amount" >
                                                        </div>
                                                        @if ($errors->has('loan_amount'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('loan_amount') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                </div>
                                            </fieldset>

                                            <h6>Declaration</h6>
                                            <fieldset>
                                                <div class="form-group row">
                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" style="margin-left: 0 !important;" type="checkbox" value="" id="terms_and_conditions" name="terms_and_conditions" required>
                                                            <label class="form-check-label" for="terms_and_conditions">
                                                                I confirm that I have read, accepted and understood the terms and conditions to which I agree to be bound to and by without exception.
                                                                By so submitting this digital application, I also confirm that I am personally liable for all amounts owing and due to LITSA CREDIT. under this Loan Agreement.
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>

                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </form>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@stop
