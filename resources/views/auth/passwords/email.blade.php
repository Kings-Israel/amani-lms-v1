@extends('auth.master')

@section('content')

<section class="login-block">

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">

                <form method="POST" action="{{ route('password.email') }}" class="md-float-material form-material">
                    @csrf
<!-- 
                    <div class="text-center">
                        <img src="{{asset('assets/images/LITSALOGO.jpg')}}"  style="height: 200px;width: 250px;"  alt="logo.png">
                    </div> -->

                    <div class="auth-box card">
                        <div class="card-block">
                            <div class="row m-b-20">
                                <div class="col-md-12">
                                <div class="text-center">
                                            <img src="{{asset('assets/images/LITSALOGO.jpg')}}" style="width: 150px;"  alt="LITSA CREDITS">
                                        </div>
                                        <h6 class="text-center"> Recover your password </h6>
                                    <!-- <h3 class="text-left">Recover your password</h3> -->
                                </div>

                                @if (session('status'))
                                    <div class="alert alert-success col-md-12" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif
                            </div>
                            <div class="form-group form-primary">

                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                                <span class="form-bar"></span>
                            </div>


                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit"
                                            class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">
                                        Send Password Reset Link
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
{{--                                    <img src="{{asset('assets/images/auth/Logo-small-bottom.png')}}" alt="small-logo.png">--}}
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
