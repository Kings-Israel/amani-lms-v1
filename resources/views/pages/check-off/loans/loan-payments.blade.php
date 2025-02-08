@extends("layouts.master")
@section("css")
@stop
@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="row justify-content-center">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col">
                                    <p class="m-b-5">Total Loan Amount Due</p>
                                    <h4 class="m-b-0">KES. {{number_format($loan->total_amount)}}</h4>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-list f-50 text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col">
                                    <p class="m-b-5">Total Amount Paid</p>
                                    <h4 class="m-b-0">KES. {{number_format($loan->amount_paid)}}</h4>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-list f-50 text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col">
                                    <p class="m-b-5">Total Balance</p>
                                    <h4 class="m-b-0">KES. {{number_format($loan->balance)}}</h4>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-list f-50 text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
            ajax: '{!! route('check-off.loans.loan_payments_data', encrypt($loan->id)) !!}',
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
