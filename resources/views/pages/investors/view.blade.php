@extends("layouts.master")


@section('css')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">

@stop

@section("content")
    <div class="row">
        <div class="col-lg-12">
            @include('layouts.alert')


            <div class="row">
                <div class="col-xl-4">

                    <div class="card">
                        {{--<div class="card-header contact-user">
                           --}}{{-- <img class="img-radius img-40"
                                 src="../files/assets/images/avatar-4.jpg"
                                 alt="contact-user">--}}{{--
                            <h5 class="m-l-10">{{$investor->fullname}}</h5>
                        </div>--}}
                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active"><a href="#">Investor Details</a></li>
                                <li class="list-group-item"><a href="#"><b>Name:</b> {{$investor->name}}</a></li>

                                <li class="list-group-item"><a href="#"><b>Phone:</b> {{$investor->phone}}</a></li>
                                <li class="list-group-item"><a href="#"><b>Email:</b> {{$investor->email}}</a></li>
                            </ul>
                        </div>
                        <div class="card-block groups-contact">
                            <ul class="list-group">
                                <li class="list-group-item active"><a href="#" style="color: white">Investment</a></li>

                                <li class="list-group-item justify-content-between">
                                    <b>Total Investment:</b> {{number_format($investments, 2)}}
                                    {{-- <span class="badge badge-primary badge-pill">30</span>--}}
                                </li>

                                {{--<li class="list-group-item justify-content-between">--}}
                                    {{--<b>Phone:</b>--}}
                                    {{--{{$loan->field_agent->phone}}--}}
                                {{--</li>--}}
                                {{--<li class="list-group-item justify-content-between">--}}
                                    {{--<b>Email:</b>--}}
                                    {{--{{$loan->field_agent->email}}--}}
                                {{--</li>--}}

                            </ul>
                        </div>



                        {{--<div class="card-block groups-contact">--}}
                            {{--<ul class="list-group">--}}
                                {{--<li class="list-group-item active"><a href="#" style="color: white">Loan Details</a></li>--}}

                                {{--<li class="list-group-item justify-content-between">--}}
                                    {{--<b>Product:</b> {{$loan->product}}--}}
                                    {{-- <span class="badge badge-primary badge-pill">30</span>--}}
                                {{--</li>--}}

                                {{--<li class="list-group-item justify-content-between">--}}
                                    {{--<b>Interest:</b>--}}
                                    {{--<span class="badge badge-info badge-pill">{{$loan->product()->first()->interest}}%</span>--}}
                                {{--</li>--}}
                                {{--<li class="list-group-item justify-content-between">--}}
                                    {{--<b>Days Remaining:</b>--}}
                                    {{--<span class="badge badge-danger badge-pill">{{$days_remaining}}</span>--}}
                                {{--</li>--}}
                                {{--@if($loan->disbursed)--}}
                                    {{--<li class="list-group-item justify-content-between">--}}
                                        {{--<b>End Date:</b> {{Carbon\Carbon::parse($loan->end_date)->format('Y-m-d')}}--}}
                                        {{--<span class="badge badge-success badge-pill">20</span>--}}
                                    {{--</li>--}}
                                {{--@endif--}}
                            {{--</ul>--}}
                        {{--</div>--}}

                    </div>

                </div>
                <div class="col-xl-8">
                    <div class="row">
                        <div class="col-sm-12">

                            <div class="card">

                                <div class="card-header">
                                    <div>

                                    </div>

                                    <h5 class="card-header-text">Investments</h5>
                                    <button type="button" class="btn-primary btn" style="float: right" data-toggle="modal"
                                            data-target="#default-Modal1">Add Investment
                                    </button>
                                </div>
                                <div class="card-block contact-details">
                                    <div class="data_table_main table-responsive dt-responsive">
                                        <table id="simpletable"
                                               class="table  table-striped table-bordered nowrap">
                                            <thead>
                                            <tr>
                                                <th>Amount</th>
                                                <th>Date Payed</th>
                                                <th>Transaction NO</th>

                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>Amount</th>
                                                <th>Date Payed</th>
                                                <th>Transaction NO</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <form method="post" action="{{route('add.investment')}}">


    <div class="modal fade" id="default-Modal1" tabindex="-1"
         role="dialog" style="z-index: 10000">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Investment</h4>
                    <button type="button" class="close"
                            data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="dt-responsive">
                            @csrf
                            <input type="hidden" name="user" value="{{$investor->id}}">
                            <div class="row">
                            <div class="col-md-6">
                                <label for="amount">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">A</span>
                                    <input type="number" name="amount" value="{{ old('amount', isset($investor->amount) ? $investor->amount : '')}}" class="form-control{{ $errors->has('amount') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('amount'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="date_payed">Date Paid</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">D</span>
                                    <input autocomplete="off" id="date_payed" type="text" name="date_payed" value="{{ old('date_payed', isset($investor->date_payed) ? $investor->date_payed : '')}}" class="form-control{{ $errors->has('date_payed') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('date_payed'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('date_payed') }}</strong>
                                    </span>
                                @endif
                            </div>
                            </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-default waves-effect "
                            data-dismiss="modal">Close
                    </button>
                    <button type="submit"
                            class="btn btn-primary waves-effect waves-light ">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
    </form>



@stop

@section('js')
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>

    <script>
        jQuery('#date_payed').datetimepicker({

            //format:'Y-m-d H:i'
            format:'Y-m-d'

        });

        $('#simpletable').DataTable({
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}},'pageLength'],


            ajax: '{!! route('investor.investments', ['id' => encrypt($investor->id)]) !!}',
            columns: [
                {data: 'amount', name: 'amount'},
                {data: 'date_payed', name: 'date_payed'},
                {data: 'transaction_no', name: 'transaction_no'},

                /*{data: 'date_created', name: 'date_created'},
                {data: 'approved', name: 'approved'},
                {data: 'approved_date', name: 'approved_date'},
                {data: 'disbursed', name: 'disbursed'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'end_date', name: 'end_date'},
                {data: 'settled', name: 'settled'},
                { data: 'action', name: 'action', orderable: false, searchable: false }*/

            ],
        });
    </script>

@stop
