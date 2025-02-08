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
                        <h5>LITSA CREDIT Application</h5>
                    </div>
                    <div class="card-block">
                        @if($has_incomplete_loans)
                            <div class="row">
                                <div class="col-md-12">
                                    <p>Hello {{ $customer->fullName }}, You do not qualify for a new LITSA CREDIT as you still have an unsettled loan. Below Are The Details:</p>
                                    <p>For more information, kindly contact your Credit Officer</p>
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th scope="col">Loan Account</th>
                                            <th scope="col">Loan Amount</th>
                                            <th scope="col">Amount Payable</th>
                                            <th scope="col">Amount Paid</th>
                                            <th scope="col">Balance</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <th scope="row"> {{ $unsettled_loan->loan_account }}</th>
                                            <td> KES. {{ number_format($unsettled_loan->loan_amount) }} </td>
                                            <td>KES. {{ number_format($unsettled_loan->total) }}</td>
                                            <td>KES. {{ number_format($unsettled_loan->amount_paid) }}</td>
                                            <td>KES. {{ number_format($unsettled_loan->balance) }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <a href="{{ route('customer-reapplications.close_application') }}" class="btn btn-primary">Close Application</a>

                                </div>
                            </div>
                        @else
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="wizard3">
                                        <section>
                                            <form class="wizard-form" id="design-wizard" action="{{route('customer-reapplications.submit_application', encrypt($customer->id))}}" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <h6>Loan Details</h6>
                                                <fieldset>
                                                    <div class="form-group row">
                                                        <div class="col-md-4 my-1">
                                                            <label for="full_name" class="block">Customer Name <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icofont icofont-user"></i></span>
                                                                <input id="full_name" type="text" name="full_name" value="{{ $customer->fullName }}" class="form-control{{ $errors->has('full_name') ? ' is-invalid' : '' }}" required readonly placeholder="Full Name">
                                                            </div>
                                                            @if ($errors->has('full_name'))
                                                                <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('full_name') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>

                                                        <!-- ID -->
                                                        <div class="col-md-4 my-1">
                                                            <label class="block">National ID Number <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icofont icofont-envelope-open"></i></span>
                                                                <input type="number" id="id_no" name="id_no" value="{{ $customer->id_no }}" class="form-control{{ $errors->has('id_no') ? ' is-invalid' : '' }}" required readonly placeholder="ID NO">
                                                            </div>
                                                            @if ($errors->has('id_no'))
                                                                <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('id_no') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>

                                                        <div class="col-md-4 my-1">
                                                            <label for="phone" class="block">Phone Number <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icofont icofont-ui-call"></i></span>
                                                                <input id="phone" value="{{ $customer->phone }}" type="text" name="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required readonly placeholder="Phone">
                                                            </div>
                                                            @if ($errors->has('phone'))
                                                                <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('phone') }}</strong>
                                                        </span>
                                                            @endif
                                                        </div>

                                                        <div class="col-md-4 my-1">
                                                            <label for="product_id" class="block">Loan Product <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="icofont icofont-briefcase"></i>
                                                        </span>
                                                                <select id="product_id" name="product_id" class="js-example-basic-single form-control{{ $errors->has('product_id') ? ' is-invalid' : '' }}" required>
                                                                    <option value="" selected disabled>Specify The Loan Product</option>
                                                                    @foreach($products as $product)
                                                                        <option value="{{ $product->id }}" {{ (old("product_id") == $product->id ? "selected":"") }}>{{ $product->product_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('product_id'))
                                                                <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('product_id') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>

                                                        <div class="col-md-4 my-1">
                                                            <label for="loan_amount" class="block">Desired Loan Amount <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icofont icofont-money"></i></span>
                                                                <input id="loan_amount" value="{{ old('loan_amount', $customer->prequalified_amount)  }}" min="1" max="{{ $customer->prequalified_amount }}"
                                                                       type="number" name="loan_amount" class="form-control{{ $errors->has('loan_amount') ? ' is-invalid' : '' }}" required placeholder="Desired Loan Amount">
                                                            </div>
                                                            @if ($errors->has('loan_amount'))
                                                                <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('loan_amount') }}</strong>
                                                        </span>
                                                            @endif
                                                        </div>

                                                        <div class="col-md-4 my-1">
                                                            <label for="purpose" class="block">Purpose <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icofont icofont-location-pin"></i></span>
                                                                <select id="purpose" name="purpose" class="js-example-basic-single form-control{{ $errors->has('purpose') ? ' is-invalid' : '' }}" required>
                                                                    <option value="Business Expense" {{ (old("purpose") == "Business Expense" ? "selected":"") }}>Business Expense</option>
                                                                    <option value="Start Business" {{ (old("purpose") == "Start Business" ? "selected":"") }}>Start Business</option>
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('purpose'))
                                                                <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('purpose') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>

                                                        <div class="col-md-4 my-1">
                                                            <label for="loan_type" class="block">Loan Type <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="icofont icofont-abacus-alt"></i></span>
                                                                <select id="loan_type" name="loan_type" class="js-example-basic-single form-control{{ $errors->has('loan_type') ? ' is-invalid' : '' }}" required>
                                                                    <option value="" selected disabled readonly>Select Loan Repayment Type</option>
                                                                    @foreach($loan_types as $type)
                                                                        <option value="{{$type->id}}" {{ ((old("loan_type") == $type->id) ? 'selected' : '') }} >
                                                                            {{$type->name}}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('loan_type'))
                                                                <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('loan_type') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <h6>Update Guarantor Details</h6>
                                                        <div class="col-md-12">
                                                            <p>Below are the details of your current LITSA CREDIT Guarantor, feel free to update their details where necessary.</p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="gname">Guarantor Name <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                                                <input type="text" id="gname" name="gname" value="{{ old('gname', isset($guarantor->gname) ? $guarantor->gname : '')}}" class="form-control{{ $errors->has('gname') ? ' is-invalid' : '' }}" required>
                                                            </div>
                                                            @if ($errors->has('gname'))
                                                                <span class="text-danger" role="alert">
                                                                    <strong>{{ $errors->first('gname') }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="gphone">Guarantor Phone Number <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                                                <input type="text" id="gphone" name="gphone" value="{{ old('gphone', isset($guarantor->gphone) ? $guarantor->gphone : '')}}" class="form-control{{ $errors->has('gphone') ? ' is-invalid' : '' }}" placeholder="0708000000" required>
                                                            </div>
                                                            @if ($errors->has('gphone'))
                                                                <span class="text-danger" role="alert">
                                                                    <strong>{{ $errors->first('gphone') }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="gid">Guarantor National ID <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1">@</span>
                                                                <input type="number" name="gid" value="{{ old('gid', isset($guarantor->gid) ? $guarantor->gid : '')}}" class="form-control{{ $errors->has('gid') ? ' is-invalid' : '' }}" required>
                                                            </div>
                                                            @if ($errors->has('gid'))
                                                                <span class="text-danger" role="alert">
                                                                    <strong>{{ $errors->first('gid') }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="gdob">Date of Birth <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                                                                <input type="date" id="datetimepicker1" max="{{now()->subYears(18)->format('Y-m-d')}}" autocomplete="off" name="gdob" value="{{ old('gdob', isset($guarantor->gdob) ? $guarantor->gdob : '')}}" class="datepicker form-control{{ $errors->has('gdob') ? ' is-invalid' : '' }}" required>
                                                            </div>
                                                            @if ($errors->has('gdob'))
                                                                <span class="text-danger" role="alert">
                                                                    <strong>{{ $errors->first('gdob') }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="location">Location <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-location-pin"></i></span>
                                                                <input type="text" id="location" name="location" value="{{ old('location', isset($guarantor->location) ? $guarantor->location : '')}}" class="form-control{{ $errors->has('location') ? ' is-invalid' : '' }}" required>
                                                                <input type="hidden" id="Lat" name="latitude"
                                                                       value="{{ old('latitude', isset($guarantor->latitude) ? $guarantor->latitude : '')}}"/>
                                                                <input type="hidden" id="Lng" name="longitude"
                                                                       value="{{ old('longitude', isset($guarantor->longitude) ? $guarantor->longitude : '')}}"/>
                                                            </div>
                                                            @if ($errors->has('location'))
                                                                <span class="text-danger" role="alert">
                                                                    <strong>{{ $errors->first('location') }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="marital_status">Marital Status <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-users-alt-4"></i></span>
                                                                <select class="form-control{{ $errors->has('marital_status') ? ' is-invalid' : '' }}" name="marital_status" required>
                                                                    <option value="single">Single</option>
                                                                    <option value="married">Married</option>
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('marital_status'))
                                                                <span class="text-danger" role="alert">
                                                                    <strong>{{ $errors->first('marital_status') }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="industry_id">Industry <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-industries-alt-2"></i></span>
                                                                <select class="js-example-basic-single form-control{{ $errors->has('industry_id') ? ' is-invalid' : '' }}" name="industry_id" required>
                                                                    @foreach($industries as $industry)
                                                                        <option
                                                                            value="{{$industry->id}}" {{ isset($guarantor->industry_id) ? (($guarantor->industry_id == $industry->id) ? 'selected' : '') : $industry->id }}>
                                                                            {{$industry->iname}}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('industry_id'))
                                                                <span class="text-danger" role="alert">
                                                                    <strong>{{ $errors->first('industry_id') }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="business_id">Business <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                                                <select class="js-example-basic-single form-control{{ $errors->has('business_id') ? ' is-invalid' : '' }}" name="business_id" required>
                                                                    @foreach($businesses as $business)
                                                                        <option
                                                                            value="{{$business->id}}" {{ isset($guarantor->business_id) ? (($guarantor->business_id == $business->id) ? 'selected' : '') : $business->id }}>
                                                                            {{$business->bname}}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('business_id'))
                                                                <span class="text-danger" role="alert">
                                                                    <strong>{{ $errors->first('business_id') }}</strong>
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
                                                                <input class="form-check-input" style="margin-left: 0 !important;" type="checkbox" value="accepted" id="terms_and_conditions" name="terms_and_conditions" required>
                                                                <label class="form-check-label" for="terms_and_conditions">
                                                                    I confirm that I have read, accepted and understood the terms and conditions to which I agree to be bound to and by without exception.
                                                                    By so submitting this digital application, I also confirm that I am personally liable for all amounts owing and due to LITSA CREDIT. under this Loan Agreement.
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>

                                                <button type="submit" class="btn btn-primary">Submit Application</button>
                                            </form>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
@stop
@section('js')
{{--    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
        $(document).ready(function () {
            "use strict";
            $('#datetimepicker1').datetimepicker({
                format: 'Y-m-d'
            });
        });
    </script>--}}
@endsection
