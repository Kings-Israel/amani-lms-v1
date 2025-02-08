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
                                <th>Loan Total Amount</th>
                                <th>Total Paid Amount</th>
                                <th>% Paid</th>
                                <th>Balance</th>
                                <th>Last Payment Date</th>
                                <th>Days Unpaid</th>
                                <th>Disbursed Date</th>
                                <th>Settlement Date</th>
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
                                <th>Loan Total Amount</th>
                                <th>Total Paid Amount</th>
                                <th>% Paid</th>
                                <th>Balance</th>
                                <th>Last Payment Date</th>
                                <th>Days Unpaid</th>
                                <th>Disbursed Date</th>
                                <th>Settlement Date</th>
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
        $('#cbtn-selectors1').DataTable({
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
                },
                'colvis','pageLength'],
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],

            ajax: '{!! route('non_performing_loans.data') !!}',
            order:[0,'desc'],
            columns: [
                /* {data: 'checkbox', name: 'checkbox'},*/

                {data: 'id', name: 'id'},
                {data: 'owner', name: 'customers.lname'},
                {data: 'phone', name: 'customers.phone'},
                {data: 'product_name', name: 'products.product_name'},
                {data: 'branch', name: 'branch'},
                {data: 'field_agent', name: 'field_agent'},
                {data: 'total', name: 'total'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'percentage_paid', name: 'percentage_paid'},
                {data: 'balance', name: 'balance'},
                {data: 'last_payment_date', name: 'last_payment_date'},
                {data: 'days_unpaid', name: 'days_unpaid'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'end_date', name: 'end_date'},
            ],
        });
    </script>
@stop
