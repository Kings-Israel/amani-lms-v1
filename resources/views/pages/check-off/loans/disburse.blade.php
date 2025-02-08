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
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Institution</th>
                                <th>Loan Amount</th>
                                <th>Interest</th>
                                <th>Total Amount</th>
                                <th>End Date</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Institution</th>
                                <th>Loan Amount</th>
                                <th>Interest</th>
                                <th>Total Amount</th>
                                <th>End Date</th>
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
            buttons: [
                {extend: 'copyHtml5', exportOptions: {columns: ':visible'}},
                {extend: 'excelHtml5', exportOptions: {columns: ':visible'}},
                {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}},
                'colvis', 'pageLength'],
            "processing": true,
            "serverSide": true,
            ajax: '{!! route('check-off.loans-disbursement.data') !!}',
            order: [0, 'desc'],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'full_name', name: 'employee.last_name',},
                {data: 'employee.phone_number', name: 'employee.phone_number'},
                {data: 'employee.employer.name', name: 'employee.employer.name'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'interest', name: 'interest'},
                {data: 'total_amount', name: 'total_amount'},
                {data: 'end_date', name: 'end_date'},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
        });
    </script>
@stop
