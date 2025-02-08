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
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Mpesa Reference</th>
                                <th>Phone Number</th>
                                <th>Institution</th>
                                <th>Amount</th>
                                <th>Paid At</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Mpesa Reference</th>
                                <th>Phone Number</th>
                                <th>Institution</th>
                                <th>Amount</th>
                                <th>Paid At</th>
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
            buttons: [
                {extend: 'copyHtml5', exportOptions: {columns: ':visible'}},
                {extend: 'excelHtml5', exportOptions: {columns: ':visible'}},
                {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}},
                'colvis', 'pageLength'],
            "processing": true,
            "serverSide": true,
            ajax: '{!! route('check-off.loans.payment_index_data') !!}',
            order: [0, 'desc'],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'full_name', name: 'employee.last_name',},
                {data: 'TransID', name: 'TransID'},
                {data: 'MSISDN', name: 'MSISDN'},
                {data: 'employee.employer.name', name: 'employee.employer.name'},
                {data: 'TransAmount', name: 'TransAmount'},
                {data: 'created_at', name: 'created_at'},
            ],
        });
    </script>
@stop
