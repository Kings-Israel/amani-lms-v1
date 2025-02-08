@extends("layouts.master")

@section("styles")
<link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/themify-icons.css') }}">
@endsection

@section("content")

<div class="row" id="app">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-block">
                <edit-component customer-id={{ $customer->id }}></edit-component>
            </div>
        </div>
    </div>
</div>

@endsection

@section("js")
<script src="{{asset('js/app.js?v=3')}}"></script>
@endsection
