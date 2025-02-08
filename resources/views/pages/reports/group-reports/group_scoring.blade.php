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
                                <th>Group</th>
                                <th>Branch Name</th>
                                <th>Loan Officer</th>
                                <th>Leader Details</th>
                                <th>Members</th>
                                <th>Total Loans</th>
                                <th>Repaid Loans</th>
                                <th>Loans in Arrears</th>
                                <th>Loans Paid without arrears</th>
                                <th>% Loans without Arrears</th>
                                <th>Skipped Due Payments</th>
                                <th>Created At</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Group</th>
                                <th>Branch Name</th>
                                <th>Loan Officer</th>
                                <th>Leader Details</th>
                                <th>Members</th>
                                <th>Total Loans</th>
                                <th>Repaid Loans</th>
                                <th>Loans in Arrears</th>
                                <th>Loans Paid without arrears</th>
                                <th>% Loans without Arrears</th>
                                <th>Skipped Due Payments</th>
                                <th>Created At</th>
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

            ajax: '{!! route('group_scoring_data') !!}',
            columns: [
                {data: 'id'},
                {data: 'name', name: 'name'},
                {data: 'branchName'},
                {data: 'loanOfficer'},
                {data: 'group_leader', name: 'group_leader'},
                {data: 'members', name: 'members'},
                {data: 'totalNumberOfLoans'},
                {data: 'paid_loans'},
                {data: 'loansArrear'},
                {data: 'loansWithoutArrear'},
                {data: 'perOfLoanwithoutArrears'},
                {data: 'skippedDuePayments'},
                {data: 'groupCreatedDate'},
            ],
        });
    </script>


@stop
