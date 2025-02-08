@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-header">

                    {{--<div class="row col-md-3">
                        <a class="btn btn-primary" href="{{route('products.create')}}">Add New</a>
                    </div>--}}

                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Payment Type</th>
                                <th>Transaction No</th>
                                <th>Owner</th>
                                <th>Branch</th>
                                <th>Action</th>



                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Payment Type</th>
                                <th>Transaction No</th>
                                <th>Owner</th>
                                <th>Branch</th>
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
        $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            "ordering": false,
            buttons: [/*{extend: 'copyHtml5', exportOptions: {columns: ':visible'}},*/ {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}/*, 'colvis'*/,'pageLength'],


            ajax: '{!! route('settlement_transactions.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'amount', name: 'amount'},
                {data: 'date_payed', name: 'date_payed'},
                {data: 'type', name: 'type'},
                {data: 'transaction_id', name: 'transaction_id'},
                {data: 'user_name', name: 'user_name'},
                {data: 'branch', name: 'branch'},
                { data: 'action', name: 'action', orderable: false, searchable: false }



            ],
        });
    </script>


@stop
