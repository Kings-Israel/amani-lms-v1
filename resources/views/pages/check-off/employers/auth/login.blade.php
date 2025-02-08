@extends('pages.check-off.employers.auth.master')
@section('content')
    <section class="login-block">

        <div class="container">
            <div class="row">
                <div class="col-sm-12">

                    <form method="POST" action="{{ route('checkoff.login_post') }}" class="md-float-material form-material">
                        @csrf
                        <div class="text-center">
                            <img src="{{asset('assets/images/logo.png')}}" style="height: 200px;width: 250px;"  alt="logo.png">
                            {{--                            <img src="{{asset('DI-logo.png')}}" style="height:75px; width:150px" alt="logo.png">--}}
                        </div>
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-center">Sign In</h3>
                                    </div>
                                </div>
                                @include('layouts.alert')

                                <div class="form-group form-primary">
                                    <input id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required autofocus>
                                    <span class="form-bar"></span>

                                    @if ($errors->has('username'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group form-primary">

                                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="Password" required>
                                    <span class="form-bar"></span>

                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="row m-t-25 text-left">
                                    <div class="col-12">
                                        <div class="checkbox-fade fade-in-primary d-">
                                            <label>
                                                <input disabled class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <span class="cr">
                                                    <i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                <span class="text-inverse">Remember me</span>
                                            </label>
                                        </div>
                                        <div class="forgot-phone text-right f-right">
                                            <a href="{{ route('check-off.password.request') }}" class="text-right f-w-600"> Forgot
                                                Password?</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit"
                                                class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20">
                                            Sign in
                                        </button>
                                    </div>
                                </div>
                                <hr/>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-inverse text-left m-b-0">Growing together is our motto.</p>

                                    </div>
                                    <div class="col-md-2">
                                        {{--                                        <img src="{{asset('assets/images/logo.png')}}"  style="height: 200px;width: 250px;"  alt="small-logo.png">--}}
                                        {{--                                        <img src="{{asset('DI-logo.png')}}" style="height:75px; width:150px" alt="logo.png">--}}
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
