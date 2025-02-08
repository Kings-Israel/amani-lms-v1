<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <title>{{isset($title) ? $title : 'LITSA CREDIT'}}</title>
    <!--[if lt IE 10]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
    <meta name="description" content="#">
    <meta name="keywords" content="Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="Kings Israel">

    {{-- <link rel="icon" href="{{ asset('assets/images/fav.png') }}" type="image/x-icon"> --}}
    {{--<link rel="icon" type="image/png" href="{{asset('assets/images/claypot-logo.png')}}">--}}

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">

    <link href="{{asset('assets/fonts/fonts.googleapis.com/css0e2b.css?family=Open+Sans:400,600')}}" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/bootstrap/css/bootstrap.min.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/feather/css/feather.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/icon/icofont/css/icofont.css') }}">

    {{--*******************datatables***********************--}}
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/pages/data-table/css/buttons.dataTables.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/pages/data-table/extensions/buttons/css/buttons.dataTables.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.css') }}"> --}}

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/jquery.mCustomScrollbar.css') }}">
    @yield('css')
    @yield('styles')

    <!-- custom overrides -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
</head>
<body>

<div class="theme-loader">
    <div class="ball-scale">
        <div class='contain'>
            <div class="ring">
                <div class="frame"></div>
            </div>
        </div>
    </div>
</div>

<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">
        @include("layouts.header")
        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                @include("layouts.sidebar")
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                @if(!isset($dashboard))
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <div class="d-inline">
                                                    <h4>{{isset($title) ? $title : ''}}</h4>
                                                    <span>{{isset($sub_title) ? $sub_title : ''}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="page-header-breadcrumb">
                                                <ul class="breadcrumb-title">
                                                    <li class="breadcrumb-item">
                                                        <a href="{{url('/home')}}"> <i class="feather icon-home"></i> </a>
                                                    </li>
                                                    <?php $segments = ''; $i = 0; ?>
                                                    @foreach(Request::segments() as $segment)
                                                    <?php
                                                            try {
                                                                $d = decrypt($segment/*request()->segment(count(request()->segments()))*/);
                                                                $segments .= '/'.$d;
                                                                echo "<li class='breadcrumb-item'>
                                                            <a href='".$segments ."'>".$d."</a>
                                                            </li>";
                                                            } catch(\RuntimeException $e) {
                                                                //$segment = request()->segment(count(request()->segments()));
                                                                if ($i == 0)
                                                                $segments .= '/app/'.$segment;
                                                                else{
                                                                    $segments .= '/'.$segment;

                                                                }
                                                                echo "<li class='breadcrumb-item'>
                                                            <a href='".$segments ."'>".$segment."</a>
                                                            </li>";
                                                            }
                                                        $i++;

                                                        ?>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="page-body">
                            @yield("content")
                                </div>
                            </div>
                           {{-- <div id="styleSelector">
                            </div>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.iesupport')


@include('layouts.scripts')
@yield('js')
</body>

<!-- Mirrored from colorlib.com//polygon/adminty/default/ by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 26 Feb 2019 09:26:54 GMT -->
</html>
