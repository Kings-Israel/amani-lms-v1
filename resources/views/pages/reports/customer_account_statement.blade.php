@extends('layouts.master')
@section('css')

@stop

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">

                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Branch Name</th>
                                    <th>Loan Officer</th>
                                    <th>Customer Name</th>
                                    <th>Mobile Number</th>
                                    <th>Id No</th>
                                    <th>Created Date</th>
                                    {{-- <th>Referee</th>
                                    <th>Location (Ward, Const., County)</th> --}}
                                    <th>Action</th>
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
                                    <th>Created Date</th>
                                    {{-- <th>Referee</th>
                                    <th>Location (Ward, Const., County)</th> --}}
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{
                extend: 'copyHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            }, {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            }, {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            }, 'colvis'],


            ajax: '{!! route('customer_account_statement.data') !!}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'branchName'
                },
                {
                    data: 'loanOfficer'
                },
                {
                    data: 'customerName',
                    name: 'lname'
                },
                {
                    data: 'mobileNumber',
                    name: 'phone'
                },
                {
                    data: 'idNo',
                    name: 'id_no'
                },
                {
                    data: 'createdDate'
                },
                // {data: 'referee', name: 'referee'},
                // {data: 'location', name: 'location'},
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
        });
    </script>
@endsection
