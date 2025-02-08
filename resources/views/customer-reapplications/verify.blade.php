@extends('auth.master')
@section('content')
    <section class="login-block">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            @include('layouts.alert')
                        </div>
                    </div>
                    <div class="text-center">
                        <img src="{{asset('assets/images/logo.png')}}"  style="height: 200px;width: 250px; margin-bottom: -100px; margin-top: -100px"  alt="small-logo.png">
                    </div>
                    <form method="POST" action="{{ route('customer-reapplications.verify_post') }}" class="md-float-material form-material">
                        @csrf
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h5 class="text-center">LITSA CREDIT Loan Re-application</h5>
                                    </div>
                                </div>
                                <div class="form-group form-primary">
                                    <label class="text-muted" for="institution_code">Verification Code:</label>
                                    <input id="verification_code" type="number" placeholder="Kindly Enter the Verification Code Sent to Your Number" class="form-control{{ $errors->has('verification_code') ? ' is-invalid' : '' }}" name="verification_code" value="{{ old('verification_code') }}" required autofocus>
                                    <span class="form-bar"></span>
                                    @if ($errors->has('verification_code'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('verification_code') }}</strong>
                                    </span>
                                    @endif
                                </div>

                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20">
                                           Proceed
                                        </button>
                                    </div>
                                </div>
                                <hr/>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-inverse text-left m-b-0">Growing together is our motto.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    @stop
