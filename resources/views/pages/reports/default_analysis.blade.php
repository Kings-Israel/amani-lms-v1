@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">

@stop

@section("content")
    <div class="row justify-content-center">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-yellow f-w-600" id="disbursed_amount"></h4>
                            <h6 class="text-muted m-b-0">Disbursed Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="feather icon-bar-chart f-28"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-yellow">
                    <div class="row align-items-center">
                        <div class="col-6">
                            {{--                            <p class="text-white m-b-0">Due <span id="due_interactions"></span></p>--}}
                        </div>
                        <div class="col-6 text-right">

                            {{--                                                        <i class="feather icon-trending-up text-white f-16"></i>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-green f-w-600" id="total_amount"></h4>
                            <h6 class="text-muted m-b-0">Total Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="feather icon-file-text f-28"></i>

                            {{--                            <h4 class="text-c-green f-w-600" id="success_rate"></h4>--}}

                            {{--                            <h6 class="text-muted m-b-0">Success</h6>--}}
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-green">
                    <div class="row align-items-center">
                        {{--                        <div class="col-6">--}}
                        {{--                            <p class="text-white m-b-0">Closed: <span id="inactive_interactions"></span></p>--}}
                        {{--                        </div>--}}
                        {{--                        <div class="col-6 text-right">--}}
                        {{--                            <p class="text-white m-b-0">Success: <span id="success_interaction"></span></p>--}}
                        {{--                        </div>--}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-blue f-w-600" id="collected_amount"></h4>
                            <h6 class="text-muted m-b-0">Collected Amount</h6>
                        </div>
                        <div class="col-4 text-right">
{{--                            <i class="feather icon-download f-28"></i>--}}
                            <span id="percentage_collection"></span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-blue">
                    <div class="row align-items-center">
{{--                        <div class="col-6">--}}
{{--                            <p class="text-white m-b-0">Due:<span id="pdue_interactions">152</span></p>--}}
{{--                        </div>--}}
{{--                        <div class="col-6 text-right">--}}
{{--                            <p class="text-white m-b-0">Arrears:<span id="pre_arrears">1124</span></p>--}}
{{--                        </div>--}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-pink f-w-600" id="defaulted_amount"></h4>
                            <h6 class="text-muted m-b-0">Defaulted Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="feather icon-activity f-28"></i>

                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-pink">
                    <div class="row align-items-center">
                        <div class="col-12">
                                                        <p class="text-white m-b-0"><span style="text-transform: uppercase"></span> Defaulted Count: <span
                                                                id="defaulted_count"></span></p>
                        </div>
<!--                        <div class="col-6 text-right">
                                                        <i class="feather icon-trending-up text-white f-16"></i>

                        </div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-3">
                            <label for="branch">BRANCH</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select
                                    class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}"
                                    name="branch_id" required>
                                    <option value="all"> All Branches</option>
                                    @foreach($branches as $brach)
                                        <option value="{{$brach->id}}"> {{$brach->bname}} </option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('branch_id'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch_id') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label for="branch">Loan Officer</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select
                                    class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                    name="name" required>
                                    <option value="all">All Credit Officers</option>
                                    @foreach($lfs as $lf)
                                        <option value="{{$lf->id}}"> {{$lf->name}} - ({{$lf->branch}})</option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('name'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label for="month">Select Month <small> - ({{\Carbon\Carbon::now()->format('Y')}}
                                    )</small></label>
                            <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i
                                                class="icofont icofont-calendar"></i></span>
                                <select id="month" name="month"
                                        class="form-control{{ $errors->has('month') ? ' is-invalid' : '' }}">
                                    @foreach($months as $month)
                                        <option
                                            value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>

                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('branch'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label for="collection_year">Select Year </label>
                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i
                                                        class="icofont icofont-calendar"></i></span>
                                <select id="collection_year" name="collection_year"
                                        class="form-control{{ $errors->has('collection_year') ? ' is-invalid' : '' }}">
                                    @foreach($years as $year)
                                        <option
                                            value="{{$year}}" {{($year == now()->format('Y') ) ? 'selected' : ''}}>{{$year}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('collection_year'))
                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('collection_year') }}</strong>
                                                </span>
                            @endif
                        </div>


                        <div class="col-md-2">
                            {{--                            <button type="submit" class="view btn btn-grd-primary">View Report</button>--}}
                            <input class="view btn btn-grd-primary" type="submit" value="Submit" onClick="mySubmit1()">

                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Product</th>
                                <th>Branch</th>
                                <th>Disbursed Date</th>
                                <th>End Date</th>
                                <th>Loan Officer</th>
                                <th>Disbursed Amount</th>
                                <th>Loan Total Amount</th>
                                <th>Total Collected</th>
                                <th>Amount Defaulted</th>
                                {{--                                <th>Next Payment Date</th>--}}
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Product</th>
                                <th>Branch</th>
                                <th>Disbursed Date</th>
                                <th>End Date</th>


                                <th>Loan Officer</th>
                                <th>Disbursed Amount</th>
                                <th>Loan Total Amount</th>
                                <th>Total Collected</th>
                                <th>Amount Defaulted</th>
                                {{--                                <th>Next Payment Date</th>--}}
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop


@section('js')
    <script type="text/javascript" src="{{asset('bower_components/sweetalert/js/sweetalert.min.js')}}"></script>

    <script>
        var $_base = '{{env('APP_URL')}}';
        var $_current_url = '{{env('APP_URL')}}';

        var oTable = $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{extend: 'copyHtml5'}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'},
            },
                {
                    extend: 'pdfHtml5', /*exportOptions: {columns: ':visible'}*/
                    orientation: 'landscape',
                    pageSize: 'TABLOID'
                },
                'colvis', 'pageLength'],
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],

            ajax: {
                url: '{!! route('default_analysis_report.data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();
                    d.lf = $('select[name=name]').val();
                    d.year = $('select[name=collection_year]').val();
                    d.month = $('select[name=month]').val();


                }
            },
            columns: [
                /* {data: 'checkbox', name: 'checkbox'},*/

                {data: 'id', name: 'id'},
                // {data: 'owner', name: 'customers.lname'},
                {data: 'owner', name: 'owner'},
                {data: 'phone', name: 'customers.phone'},
                // {data: 'product_name', name: 'products.product_name'},
                {data: 'product_name', name: 'product_name'},
                {data: 'branch', name: 'branch'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'end_date', name: 'end_date'},
                {data: 'field_agent', name: 'field_agent'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'total_amount', name: 'total_amount'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'balance', name: 'balance'},
                // {data: 'total_amount', name: 'total_amount'},
                // {data: 'amount_paid', name: 'amount_paid'},
                // {data: 'defaulted_amount', name: 'defaulted_amount'},
                // {data: 'next_payment_date', name: 'next_payment_date'},

                // {data: 'principal_paid', name: 'principal_paid'},
                // {data: 'interest_paid', name: 'interest_paid'},
                // {data: 'principal_due', name: 'principal_due'},
                // {data: 'interest_due', name: 'interest_due'},
                // {data: 'total_arrears', name: 'total_arrears'},
                // {data: 'principal_arrears', name: 'total_arrears'},
                // {data: 'interest_arrears', name: 'total_arrears'},
                // {data: 'amount_paid', name: 'amount_paid'},
                // {data: 'balance', name: 'balance'},
                // {data: 'installments', name: 'products.installments'},
                // {data: 'elapsed_schedule', name: 'elapsed_schedule'},
                // {data: 'overdue', name: 'overdue'},
                // {data: 'disbursement_date', name: 'disbursement_date'},
                // {data: 'next_payment_date', name: 'next_payment_date'},
                {data: 'action', name: 'action'},
            ],
            order: [0, 'desc']
        });

        ajx();

        $('#search').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        function mySubmit1() {
            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    // 'Cache-Control': 'no-cache, no-store, must-revalidate',

                },
                cache: false,
                method: "get",
                url: $_base + "default_ajax",
                dataType: 'json',

                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();
                    d.lf = $('select[name=name]').val();
                    d.year = $('select[name=collection_year]').val();
                    d.month = $('select[name=month]').val();
                    d.ajax = true;


                },
                data: {
                    branch: $('select[name=branch_id]').val(),
                    lf: $('select[name=name]').val(),
                    year: $('select[name=collection_year]').val(),
                    month: $('select[name=month]').val(),
                    ajax: true


                },
                success: function (json) {
                    //if (json.status === "success")

                    $('#disbursed_amount').text(json['disbursed_amount']);
                    $('#total_amount').text(json['total_amount']);
                    $('#collected_amount').text(json['collected_amount']);
                    $('#defaulted_amount').text(json['defaulted_amount']);
                    $('#defaulted_count').text(json['defaulted_count'])

                    $('#percentage_collection').text(json['percentage_collection'] + "%");


                }
            });


        }

        function ajx() {
            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    // 'Cache-Control': 'no-cache, no-store, must-revalidate',

                },
                cache: false,
                method: "get",
                url: $_base + "default_ajax",
                dataType: 'json',

                data: {
                    branch: $('select[name=branch_id]').val(),
                    lf: $('select[name=name]').val(),
                    year: $('select[name=collection_year]').val(),
                    month: $('select[name=month]').val(),
                    ajax: true


                },
                success: function (json) {
                        $('#total_amount').text(json['total_amount']);
                        $('#collected_amount').text(json['collected_amount']);
                        $('#defaulted_amount').text(json['defaulted_amount']);
                        $('#disbursed_amount').text(json['disbursed_amount']);
                    $('#defaulted_count').text(json['defaulted_count'])


                    $('#percentage_collection').text(json['percentage_collection'] + "%");






                }
            });

        }


    </script>

@stop
