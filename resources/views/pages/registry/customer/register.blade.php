@extends("layouts.master")

@section("css")
<link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/themify-icons.css') }}">

@section("content")

<div class="row" id="app">
    <div class="col-sm-12">
        <div class="card">
            
            <div class="card-block">
                <register-component></register-component>
            </div>
        </div>
    </div>
</div>

@endsection

@section("js")
<script src="{{asset('js/app.js?v=4')}}"></script>
@endsection
