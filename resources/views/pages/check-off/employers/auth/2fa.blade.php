@extends('pages.check-off.employers.auth.master')
@section('content')
    <section class="login-block">

        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            @include('layouts.alert')
                        </div>
                    </div>
                    <form method="POST" action="{{ route('checkoff.post.2fa') }}" class="md-float-material form-material">
                        @csrf
                        <div class="text-center">
                            <img src="{{asset('assets/images/logo.png')}}"  style="height: 200px;width: 250px;"  alt="small-logo.png">
                        </div>
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-center">Verify OTP</h3>
                                    </div>
                                    <input type="hidden" name="password" value="{{$password}}">
                                    <input type="hidden" name="username" value="{{$username}}">


                                </div>
                                <div class="form-group form-primary">
                                    <input id="token" type="number" class="form-control{{ $errors->has('token') ? ' is-invalid' : '' }}" name="token" value="{{ old('token') }}" required autofocus>
                                    <span class="form-bar"></span>
                                    @if ($errors->has('token'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('token') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="row m-t-25 text-left">
<!--                                    <div class="col-6">
                                        <div class=" ">
                                            <a href="{{route('resend.2fa')}}" class="text-right f-w-600">
                                              <i class="feather icon-mail"></i>  Send New Token
                                            </a>
                                        </div>
                                    </div>-->
<!--                                    <div class="col-6">
                                        <div class="text-right f-right">
                                            <a class="text-right f-w-600" href="{{ route('logout.2fa') }}">
                                                <i class="feather icon-log-out"></i> Logout
                                            </a>
                                        </div>
                                    </div>-->
                                </div>
                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit"
                                                class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20">
                                           Verify
                                        </button>
                                    </div>
                                </div>
                                <hr/>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-inverse text-left m-b-0">Growing together is our motto.</p>

                                    </div>
                                    <div class="col-md-2">
{{--                                        <img src="{{asset('assets/images/logo.png')}}" alt="small-logo.png">--}}
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
