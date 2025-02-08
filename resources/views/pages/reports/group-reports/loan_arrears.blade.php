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
                    <div class="col-md-4">
                        <label for="group">Group Name</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-users"></i></span>
                            <select class="js-example-basic-single form-control{{ $errors->has('group') ? ' is-invalid' : '' }}" name="group" required>
                                <option value="all">All</option>
                                @foreach($groups as $group)
                                    <option  value="{{$group->id}}" > {{$group->name}} </option>
                                @endforeach
                            </select>
                        </div>
                        @if ($errors->has('group'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('group') }}</strong>
                            </span>
                        @endif
                    </div>
                        <div class="col-md-1">
                            <button class="btn btn-sm btn-primary">Filter</button>
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
                                <th>Loan Officer</th>
                                <th>Disbursed Amount</th>
                                <th>Loan Total Amount</th>
                                <th>Principal Paid</th>
                                <th>Interest Paid</th>
                                <th>Principal Due</th>
                                <th>Interest Due</th>
                                <th>Arrears Amount</th>
                                <th>Principal Arrears</th>
                                <th>Interest Arrears</th>
                                <th>Total Paid Amount</th>
                                <th>Total Amount Due</th>
                                <th>Total Schedule</th>
                                <th>Elapsed Schedule</th>
                                <th>Overdue Days</th>
                                <th>Start Date</th>
                                <th>Next Payment Date</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Product</th>
                                <th>Branch</th>
                                <th>Loan Officer</th>
                                <th>Disbursed Amount</th>
                                <th>Loan Total Amount</th>
                                <th>Principal Paid</th>
                                <th>Interest Paid</th>
                                <th>Principal Due</th>
                                <th>Interest Due</th>
                                <th>Arrears Amount</th>
                                <th>Principal Arrears</th>
                                <th>Interest Arrears</th>
                                <th>Total Paid Amount</th>
                                <th>Total Amount Due</th>
                                <th>Total Schedule</th>
                                <th>Elapsed Schedule</th>
                                <th>Overdue Days</th>
                                <th>Start Date</th>
                                <th>Next Payment Date</th>
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
            ajax: {
                url: '{!! route('group_loan_arrears_data') !!}',
                data: function (d) {
                    d.group = $('select[name=group]').val();
                }
            },
            columns: [
                /* {data: 'checkbox', name: 'checkbox'},*/

                {data: 'id', name: 'id'},
                {data: 'owner', name: 'customers.lname'},
                {data: 'phone', name: 'customers.phone'},
                {data: 'product_name', name: 'products.product_name'},
                {data: 'branch', name: 'branch'},
                {data: 'field_agent', name: 'field_agent'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'total', name: 'total'},


                {data: 'principal_paid', name: 'principal_paid'},
                {data: 'interest_paid', name: 'interest_paid'},
                {data: 'principal_due', name: 'principal_due'},
                {data: 'interest_due', name: 'interest_due'},

                {data: 'total_arrears', name: 'total_arrears'},
                {data: 'principal_arrears', name: 'total_arrears'},
                {data: 'interest_arrears', name: 'total_arrears'},

                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'balance', name: 'balance'},
                {data: 'installments', name: 'products.installments'},
                {data: 'elapsed_schedule', name: 'elapsed_schedule'},
                {data: 'overdue', name: 'overdue'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'next_payment_date', name: 'next_payment_date'},
            ],
        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });


    </script>


@stop
