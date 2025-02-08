@extends("layouts.master")
@section("css")
@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
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
                                <th>Channel</th>
                                <th>Owner</th>
                                <th>Loan Account</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Payment Type</th>
                                <th>Transaction No</th>
                                <th>Channel</th>
                                <th>Owner</th>
                                <th>Loan Account</th>
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
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('payments.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'amount', name: 'amount'},
                {data: 'date_payed', name: 'date_payed'},
                {data: 'type', name: 'type'},
                {data: 'transaction_id', name: 'transaction_id'},
                {data: 'channel', name: 'channel'},
                {data: 'owner', name: 'owner'},
                {data: 'loan_account', name: 'loan_account'},

            ],
        });
    </script>


@stop
