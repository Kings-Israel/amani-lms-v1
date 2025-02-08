@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">


@stop

@section("content")

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
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                    @foreach($branches as $brach)

                                        <option
                                                value="{{$brach->id}}" >
                                            {{$brach->bname}}
                                        </option>
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
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                <option disabled selected>Select RO</option>
                                    @foreach($lfs as $brach)

                                        <option
                                                value="{{$brach->id}}" >
                                            {{$brach->name}}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('name'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                            @endif
                        </div>


                        <div class="col-md-3">
                            <button class="btn btn-grd-primary">View Report</button>
                        </div>
                    </form>
                        <div class="dt-responsive table-responsive">
                            <table id="cbtn-selectors1"
                                   class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>RO</th>
                                    <th>Owner</th>
                                    <th>Phone</th>
                                    <th>Product</th>
                                    <th>Branch</th>
                                    <th>Loan Amount</th>
                                    <th>% Interest</th>
                                    <th>P+I paid</th>
                                    <th>OLB at Rollover</th>
                                    <th>Rollover Fee</th>
                                    <th>Total Paid Amount</th>
                                    <th>Balance</th>
                                    <th>Start Date</th>
                                    <th>Roll Over Date</th>
                                    <th>Days in Rollover</th>
                                    <th>End Date</th>
                                </tr>
                                </thead>

                                <tfoot>
                                <tr>

                                    <th>#</th>

                                    <th>RO</th>
                                    <th>Owner</th>

                                    <th>Phone</th>
                                    <th>Product</th>
                                    <th>Branch</th>
                                    <th>Loan Amount</th>
                                    <th>% Interest</th>
                                    <th>P+I paid</th>
                                    <th>OLB at Rollover</th>
                                    <th>Rollover Fee</th>
                                    <th>Total Paid Amount</th>
                                    <th>Balance</th>
                                    <th>Start Date</th>
                                    <th>Roll Over Date</th>
                                    <th>Days in Rollover</th>

                                    <th>End Date</th>


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
                'colvis','pageLength'],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],



           // ajax: '{!! route('rolled_over_loans.data') !!}',
           ajax: {
                url: '{!! route('rolled_over_loans.data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();
                    d.name = $('select[name=name]').val();
                   // d.end_date = $('input[name=end_date]').val();

                }
            },
            columns: [
                /* {data: 'checkbox', name: 'checkbox'},*/

                {data: 'id', name: 'id'},
                {data: 'field_agent', name: 'field_agent'},

                {data: 'owner', name: 'customers.lname'},
                {data: 'phone', name: 'customers.phone'},
                {data: 'product_name', name: 'products.product_name'},

                {data: 'branch', name: 'branch'},
                {data: 'loan_amount', name: 'loan_amount'},

                {data: 'interest', name: 'products.interest'},
                {data: 'amount_paid_b4_rollover', name: 'amount_paid_b4_rollover'},
                {data: 'balance_b4_rollover', name: 'balance_b4_rollover'},
                {data: 'rollover_fee', name: 'rollover_fee'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'balance', name: 'balance'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'rolled_over_date', name: 'rolled_over_date'},
                {data: 'days', name: 'days'},
                {data: 'end_date', name: 'end_date'},






            ],
        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });


    </script>


@stop
