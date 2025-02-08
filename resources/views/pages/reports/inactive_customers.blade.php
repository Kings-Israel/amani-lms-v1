@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Branch Name</th>
                                <th>Loan Officer</th>
                                <th>Phone Number</th>
                                <th>Profession</th>
                                <th>Dealing In</th>
                                <th>Identification Number</th>
                                <th>Prequalified Amount</th>
                                <th>Customer Created Date</th>
                                <th>Last Disbursement</th>
                                <th>Inactive Days</th>
                                <th>Total Number of Loans</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Branch Name</th>
                                <th>Loan Officer</th>
                                <th>Phone Number</th>
                                <th>Profession</th>
                                <th>Dealing In</th>
                                <th>Identification Number</th>
                                <th>Prequalified Amount</th>
                                <th>Customer Created Date</th>
                                <th>Last Disbursement</th>
                                <th>Inactive Days</th>
                                <th>Total Number of Loans</th>
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
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis'],


            ajax: '{!! route('inactive_customers.data') !!}',
            columns: [
                {data: 'id'},
                {data: 'fullName', name: 'lname'},
                {data: 'branchName'},
                {data: 'loanOfficer'},
                {data: 'phoneNumber', name: 'phone'},
                {data: 'profession'},
                {data: 'dealingIn'},
                {data: 'identificationNumber', name: 'id_no'},
                {data: 'prequalifiedAmount'},
                {data: 'customerCreatedDate'},
                {data: 'lastDisbursement'},
                {data: 'inactiveDays'},
                {data: 'totalNumberOfLoans'}
            ],
        });
    </script>


@stop
