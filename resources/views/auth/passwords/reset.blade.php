@extends('auth.master')

@section('content')

    <section class="login-block">

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">

                    <form method="POST" action="{{ route('password.update') }}" class="md-float-material form-material">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">


                        <div class="text-center">
                            <img src="{{asset('assets/images/LITSALOGO.jpg')}}"  style="height: 200px;width: 250px;"  alt="logo.png">
                        </div>
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-left">Recover your password</h3>
                                    </div>
                                </div>
                               {{-- <div class="form-group form-primary">
                                    <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email') }}</label>

                                    <div class="col-md-6">

                                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>

                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                    <span class="form-bar"></span>
                                    </div>
                                </div>--}}
                                <div class="form-group form-primary row">
                                    <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Email') }}</label>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" required>

                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group form-primary row">
                                    <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                        @if ($errors->has('password'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group form-primary row">
                                    <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                                    <div class="col-md-6">
                                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit"
                                                class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">
                                            Reset Password
                                        </button>
                                    </div>
                                </div>
                                <p class="f-w-600 text-right">Back to <a href="{{route('login')}}">Login.</a></p>
                                <div class="row">
                                    <div class="col-md-10">
                                        <p class="text-inverse text-left m-b-0">Thank you.</p>
                                        {{--<p class="text-inverse text-left"><a href="index-2.html"><b class="f-w-600">Back to
                                                    website</b></a></p>--}}
                                    </div>
                                    <div class="col-md-2">
{{--                                        <img src="{{asset('assets/images/auth/Logo-small-bottom.png')}}" alt="small-logo.png">--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>


                </div>

            </div>

        </div>

    </section>

@endsection


