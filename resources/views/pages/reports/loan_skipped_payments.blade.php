@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-3">
                            <label for="branch">Select Branch</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                    <option selected value="all" > All Branches </option>
                                @foreach($branches as $brach)
                                        <option value="{{$brach->id}}" > {{$brach->bname}} </option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('branch_id'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch_id') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label for="branch">Select Field Agent</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                <option selected value="all">All Field Agents</option>
                                    @foreach($lfs as $lf)
                                        <option  value="{{$lf->id}}" > {{$lf->name}} - ({{$lf->branch}} Branch) </option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('name'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Skipped Payments</th>
                                <th>Disbursed Amount</th>
                                <th>Loan Total Amount</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                {{-- <th>Principal Paid</th> --}}
                                {{-- <th>Interest Paid</th> --}}
                                {{-- <th>Principal Due</th> --}}
                                {{-- <th>Interest Due</th> --}}
                                <th>Total Arrears</th>
                                {{-- <th>Principal Arrears</th>
                                <th>Interest Arrears</th> --}}
                                <th>Total Paid Amount</th>
                                <th>Total Amount Due</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Skipped Payments</th>
                                <th>Disbursed Amount</th>
                                <th>Loan Total Amount</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                {{-- <th>Principal Paid</th> --}}
                                {{-- <th>Interest Paid</th> --}}
                                {{-- <th>Principal Due</th> --}}
                                {{-- <th>Interest Due</th> --}}
                                <th>Total Arrears</th>
                                {{-- <th>Principal Arrears</th>
                                <th>Interest Arrears</th> --}}
                                <th>Total Paid Amount</th>
                                <th>Total Amount Due</th>
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
    <script type="text/javascript" src="{{asset('bower_components/sweetalert/js/sweetalert.min.js')}}"></script>
    <script>
        $(function() {
            $('#start_date').datetimepicker({
                maxDate: new Date(),
                format:'Y-m-d'
            });
            $('#end_date').datetimepicker({
                maxDate: new Date(),
                format:'Y-m-d'
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            function format ( d ) {
                function getInstallments(){
                    //loop through predictions array
                    var installments =  d.skipped_installments_obj;
                    var dataArray = [];
                    for (var index = 0; index < installments.length; ++index) {
                        var principal_amount = installments[index].principal_amount;
                        var total = installments[index].total;
                        var amount_paid = installments[index].amount_paid;
                        var due_date = installments[index].due_date;
                        if (amount_paid === null){
                            amount_paid = 0;
                        }
                        var str = '';

                        var value = str.concat('<tr>', '<td>',principal_amount, '</td>','<td>', total,'</td>', '<td>',amount_paid,'</td>', '<td>', due_date,'<td>','</tr>');
                        dataArray.push(value);
                    }
                    return dataArray;
                }
                let output = `
                        <table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">
                            <tr>
                                <td><b>Loan Officer</b></td>
                                <td colspan="4">${d.field_agent}</td>
                            </tr>
                            <tr>
                                <td><b>Branch</b></td>
                                <td>${d.branch}</td>
                                <td><b>Loan Account</b></td>
                                <td>${d.loan_account}</td>
                            </tr>
                            <tr>
                                <td><b>Product:</b></td>
                                <td> ${d.product_name}</td>
                                <td><b>Installments:</b></td>
                                <td> ${d.installments}</td>
                            </tr>

                            <tr>
                                <td><b>Disbursement Date:</b></td>
                                <td>${d.disbursement_date}</td>
                                <td><b>Next Scheduled Payment:</b></td>
                                <td>${d.next_payment_date}</td>
                            </tr>
                            <tr>
                                <td><b>Last Payment Date:</b></td>
                                <td colspan="4">${d.last_payment_date}</td>
                            </tr>
                        </table>
                      <br>
                        <table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">
                            <thead>
                            <tr>
                              <th scope="col">Principle Amount</th>
                              <th scope="col">Total Amount</th>
                              <th scope="col">Paid Amount</th>
                              <th scope="col">Due Date</th>
                              <th></th>
                            </tr>
                          </thead>
                           <tbody>
                            ${getInstallments()}
                            </tbody>
                        </table>
                    `;

                return output;
            }
            var oTable = $('#cbtn-selectors1').DataTable({
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

                ajax: {
                    url: '{!! route('loan_skipped_payments.data') !!}',
                    data: function (d) {
                        d.branch = $('select[name=branch_id]').val();
                        d.lf = $('select[name=name]').val();
                        d.start_date = $('input[name=start_date]').val();
                        d.end_date = $('input[name=end_date]').val();
                    }
                },
                columns: [
                    {
                        className : 'details-control',
                        orderable : false,
                        data : null,
                        defaultContent : ''
                    },
                    {data: 'id', name: 'id'},
                    {data: 'owner', name: 'customers.lname'},
                    {data: 'phone', name: 'customers.phone'},
                    {data: 'skipped_installments', name: 'skipped_installments'},
                    {data: 'loan_amount', name: 'loan_amount'},
                    {data: 'total_amount', name: 'total_amount'},
                    {data: 'disbursement_date', name: 'start_date'},
                    {data: 'end_date', name: 'end_date'},
                    // {data: 'principal_paid', name: 'principal_paid'},
                    // {data: 'interest_paid', name: 'interest_paid'},
                    // {data: 'principal_due', name: 'principal_due'},
                    // {data: 'interest_due', name: 'interest_due'},
                    {data: 'total_arrears', name: 'total_arrears'},
                    // {data: 'principal_arrears', name: 'total_arrears'},
                    // {data: 'interest_arrears', name: 'total_arrears'},
                    {data: 'total_amount_paid', name: 'total_amount_paid'},
                    {data: 'balance', name: 'balance'},
                ],
                order: [4, 'desc']
            });
            // Add event listener for opening and closing details
            $('#cbtn-selectors1 tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = oTable.row( tr );


                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
            $('#search').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });
        });
    </script>


@stop
