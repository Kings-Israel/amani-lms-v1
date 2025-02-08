@extends("layouts.master")
@section("css")
@stop
@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-4">
                            <label for="branch">Select Branch</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                    <option @if($check_role) @else disabled @endif value="all" > All </option>
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
                        <div class="col-md-4">
                            <label for="branch">Select Loan Officer</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                    <option @if($check_role) @else disabled @endif value="all" > All </option>
                                    @foreach($lfs as $lf)
                                        <option  value="{{$lf->id}}" > {{$lf->name}} </option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('name'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary" type="submit">Filter</button>
                        </div>
                    </form>

                    <button class="btn btn-sm btn-primary mb-3 mt-1" id="btn-show-all-children" type="button">Expand All</button>
                    <button class="btn btn-sm btn-primary mb-3 mt-1" id="btn-hide-all-children" type="button">Collapse All</button>

                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Owner</th>
                                <th>Owner Phone Number</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Amount</th>
                                <th>Total</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th> Guarantor</th>
                                <th>Location (Ward, Const., County)</th>
                                <th>Business Type</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Owner</th>
                                <th>Owner Phone Number</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Amount</th>
                                <th>Total</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th> Guarantor </th>
                                <th>Location (Ward, Const., County)</th>
                                <th>Business Type</th>
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
        $(document).ready(function() {
            function format ( d ) {
                return `
                    <table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">
                        <tr>
                            <td><b>Registered Branch:</b></td>
                            <td>${d.branch}</td>
                            <td><b>Credit Officer:</b></td>
                            <td>${d.field_agent}</td>
                        </tr>
                        <tr>
                            <td><b>Registered By:</b></td>
                            <td>${d.created_by}</td>
                            <td><b>Date Registered:</b></td>
                            <td>${d.created_at}</td>
                        </tr>
                        <tr>
                            <td><b>Approved By:</b></td>
                            <td>${d.approved_by}</td>
                            <td><b>Date Approved:</b></td>
                            <td>${d.approved_date}</td>
                        </tr>
                        <tr>
                            <td><b>Disbursed By:</b></td>
                            <td>${d.disbursed_by}</td>
                            <td><b>Date Disbursed:</b></td>
                            <td>${d.disbursement_date}</td>
                        </tr>
                    </table>
                `;
            }
            var oTable =
                $('#cbtn-selectors1').DataTable({
                    "processing": true,
                    "serverSide": true,
                    dom: 'Bfrtip',
                    buttons: [
                        {extend: 'copyHtml5'},
                        {
                            extend: 'excelHtml5',
                            exportOptions: {columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]},
                        },
                        {
                            extend: 'pdfHtml5',
                            orientation: 'landscape',
                            pageSize: 'TABLOID'
                        },
                        'colvis','pageLength'
                    ],
                    "lengthMenu": [[10, 25, 100, -1], [10, 25, 100,"All"]],
                    "order": [1, 'DESC'],
                    ajax:{
                        url: '{!! route('loans.active.data') !!}',
                        data: function (d) {
                            d.branch = $('select[name=branch_id]').val();
                            d.lf = $('select[name=name]').val();
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
                        {data: 'branch', name: 'branch'},
                        {data: 'owner', name: 'customers.fname'},
                        {data: 'owner_phone_number', name: 'customers.phone', },
                        {data: 'product_name', name: 'products.product_name'},
                        {data: 'installments', name: 'products.installments'},
                        {data: 'interest', name: 'products.interest'},
                        {data: 'loan_amount', name: 'loan_amount'},
                        {data: 'total', name: 'total'},
                        {data: 'amount_paid', name: 'amount_paid'},
                        {data: 'balance', name: 'balance'},
                        {data: 'disbursement_date', name: 'disbursement_date'},
                        {data: 'end_date', name: 'end_date'},
                        {data: 'referee', name: 'referee'},
                        {data: 'location', name: 'location'},
                        {data: 'businessType'},
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                });

                $('#search').on('submit', function(e) {
                    oTable.draw();
                    e.preventDefault();
                });

                // Add event listener for opening and closing details
                $('#cbtn-selectors1 tbody').on('click', 'td.details-control', function () {
                    var tr = $(this).closest('tr');
                    var row = oTable.row( tr );

                    if ( row.child.isShown() ) {
                        // This row is already open - close it
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        // Open this row
                        row.child( format(row.data()) ).show();
                        tr.addClass('shown');
                    }
                } );

                // Handle click on "Expand All" button
                $('#btn-show-all-children').on('click', function(){
                    // Enumerate all rows
                    oTable.rows().every(function(){
                        // If row has details collapsed
                        if(!this.child.isShown()){
                            // Open this row
                            this.child(format(this.data())).show();
                            $(this.node()).addClass('shown');
                        }
                    });
                });

                // Handle click on "Collapse All" button
                $('#btn-hide-all-children').on('click', function(){
                    // Enumerate all rows
                    oTable.rows().every(function(){
                        // If row has details expanded
                        if(this.child.isShown()){
                            // Collapse row details
                            this.child.hide();
                            $(this.node()).removeClass('shown');
                        }
                    });
                });

            });
    </script>
@stop
