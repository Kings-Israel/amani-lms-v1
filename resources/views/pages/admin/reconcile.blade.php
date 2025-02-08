@extends("layouts.master")
@section("css")
    <link rel="stylesheet" href="{{asset('bower_components/select2/css/select2.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form action="{{route('reconcile_post')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="customer">Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-industries-alt-2"></i></span>
                                    <select class="js-example-basic-single form-control{{ $errors->has('customer_id') ? ' is-invalid' : '' }}" name="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{$customer->id}}">
                                                {{$customer->fullName}} ( {{$customer->phone}} )
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if ($errors->has('customer'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('customer') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="datetimepicker1">Date Paid</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont ico"></i></span>
                                    <input id="datetimepicker1" type="text" autocomplete="off" name="date_payed" value="{{ old('date_payed')}}" class="form-control{{ $errors->has('date_payed') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('date_payed'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('date_payed') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="trans">Transaction ID</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">TID</span>
                                    <input type="text" id="trans" name="transaction_id" value="{{ old('transaction_id')}}" class="form-control{{ $errors->has('transaction_id') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('transaction_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('transaction_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="amount">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">A</span>
                                    <input type="number" name="amount" value="{{ old('amount')}}" class="form-control{{ $errors->has('amount') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('amount'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                            </div>


                           {{-- <div class="col-md-6">
                                <label for="role">Role</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-key"></i></span>
                                    <select class="form-control{{ $errors->has('role') ? ' is-invalid' : '' }}" name="role" required>
                                        @foreach($roles as $role)

                                            <option
                                                value="{{$role->name}}" {{ isset($user) ? (($user->roles()->pluck('name')->implode(' ') == $role->name) ? 'selected' : '') : $branch->id }}>
                                                <span>{{$role->name}}</span>
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                                @if ($errors->has('role'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('role') }}</strong>
                                    </span>
                                @endif
                            </div>--}}

                            <div class="col-md-6">
                                <label for="salary">Channel</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">CH</span>
                                    <input type="text" name="channel" value="{{ old('channel')}}" class="form-control{{ $errors->has('channel') ? ' is-invalid' : '' }}" >


                                </div>
                                @if ($errors->has('channel'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('channel') }}</strong>
                                    </span>
                                @endif
                            </div>


                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                        <a href="{{ route('reconcile_bulk') }}" class="btn btn-success float-right">Bulk(Excel Upload)</a>


                    </form>
                </div>
                <div class="card-footer">
                    <p>Reconcile Transactions</p>
                </div>


            </div>

        </div>
    </div>

@stop
@section('js')

    <script type="text/javascript" src="{{asset('bower_components/select2/js/select2.full.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/pages/advance-elements/select2-custom.js')}}"></script>
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>





    <script>
        jQuery(document).ready(function () {

            "use strict";
            jQuery('#datetimepicker1').datetimepicker({

                format:'Y-m-d H:i'
                //format: 'Y-m-d'

            });
        })
        </script>
    @stop
