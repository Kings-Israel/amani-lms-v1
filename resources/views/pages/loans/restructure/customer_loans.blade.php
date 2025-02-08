@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Amount</th>
                                <th>Total</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Payment Date</th>
                                <th>Settled</th>
                                <th>Action</th>

                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Amount</th>
                                <th>Total</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Payment Date</th>
                                <th>Settled</th>
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
    <script>
    var  oTable =
        $('#cbtn-selectors1').DataTable({
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',

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
            "order": [0, 'DESC'],

            ajax:{
                url: '{!! route('loans.customer_loans_data', ['id'=>$customer->id]) !!}',
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'branch', name: 'branch'},
                {data: 'product_name', name: 'products.product_name'},
                {data: 'installments', name: 'products.installments'},
                {data: 'interest', name: 'products.interest'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'total', name: 'total'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'balance', name: 'balance'},
                {data: 'end_date', name: 'end_date'},
                {data: 'settled', name: 'settled'},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
        });
    </script>


@stop
