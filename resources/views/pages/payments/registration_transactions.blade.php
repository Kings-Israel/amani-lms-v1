@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-header">

                   {{-- <div class="row col-md-3">
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
                                <th>Transaction No</th>
                                <th>Channel</th>
                                <th>Customer</th>
                                <th>Customer Phone Number</th>
                                <th>Branch</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Transaction No</th>
                                <th>Channel</th>
                                <th>Customer</th>
                                <th>Customer Phone Number</th>
                                <th>Branch</th>
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
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}},'pageLength'],

            ajax: '{!! route('registration_transactions.data') !!}',
            columns: [
                {data: 'id', name: 'customers.id'},
                {data: 'amount', name: 'amount'},
                {data: 'date_payed', name: 'date_payed'},
                {data: 'transaction_id', name: 'transaction_id'},
                {data: 'channel', name: 'channel'},
                {data: 'owner', name: 'customers.lname'},
                {data: 'phone', name: 'customers.phone'},
                {data: 'branch', name: 'branch'},
            ],
        });
    </script>


@stop
