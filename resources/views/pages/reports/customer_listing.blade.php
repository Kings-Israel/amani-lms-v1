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
                                <th>Branch Name</th>
                                <th>Loan Officer</th>
                                <th>Customer Name</th>
                                <th>Mobile Number</th>
                                <th>Id No</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Age</th>
                                <th>Marital Status</th>
                                <th>Residence Type</th>
                                <th>Business Description</th>
                                <th>Dealing In</th>
                                <th>Created Date</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Branch Name</th>
                                <th>Loan Officer</th>
                                <th>Customer Name</th>
                                <th>Mobile Number</th>
                                <th>Id No</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Age</th>
                                <th>Marital Status</th>
                                <th>Residence Type</th>
                                <th>Business Description</th>
                                <th>Dealing In</th>
                                <th>Created Date</th>
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


            ajax: '{!! route('customer_listing.data') !!}',
            columns: [
                {data: 'id'},
                {data: 'branchName'},
                {data: 'loanOfficer'},
                {data: 'customerName', name: 'lname'},
                {data: 'mobileNumber', name: 'phone'},
                {data: 'idNo', name: 'id_no'},
                {data: 'gender'},
                {data: 'dateOfBirth'},
                {data: 'age'},
                {data: 'maritalStatus'},
                {data: 'residenceType'},
                {data: 'businessDescription'},
                {data: 'dealingIn'},
                {data: 'createdDate'}
            ],
        });
    </script>


@stop
