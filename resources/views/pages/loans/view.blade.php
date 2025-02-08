@extends("layouts.master")
@section("content")
    <div class="row">
        <div class="col-lg-12">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    @include('layouts.alert')
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active"><a href="#">Customer Details</a></li>
                                <li class="list-group-item"><a href="#"><b>Name:</b> {{$customer->fullname}}</a></li>

                                <li class="list-group-item"><a href="#"><b>Phone:</b> {{$customer->phone}}</a></li>
                                <li class="list-group-item"><a href="#"><b>Email:</b> {{$customer->email}}</a></li>
                            </ul>
                        </div>
                        <div class="card-block groups-contact">
                            <ul class="list-group">
                                <li class="list-group-item active"><a href="#" style="color: white">Loan Officer Details</a></li>

                                <li class="list-group-item justify-content-between">
                                    <b>Name:</b> {{$loan->field_agent->name}}
                                </li>
                                <li class="list-group-item justify-content-between">
                                    <b>Phone:</b>
                                    {{$loan->field_agent->phone}}
                                </li>
                                <li class="list-group-item justify-content-between">
                                    <b>Email:</b>
                                    {{$loan->field_agent->email}}
                                </li>
                            </ul>
                        </div>

                        <div class="card-block groups-contact">
                            <ul class="list-group">
                                <li class="list-group-item active"><a href="#" style="color: white">Loan Details</a></li>

                                <li class="list-group-item justify-content-between">
                                    <b>Product:</b> {{$loan->product}}
                                </li>

                                <li class="list-group-item justify-content-between">
                                    <b>Interest:</b>
                                    <span class="badge badge-info badge-pill">{{$loan->product()->first()->interest}}%</span>
                                </li>
                                <li class="list-group-item justify-content-between">
                                    <b>Days Remaining:</b>
                                    <span class="badge badge-danger badge-pill">{{$days_remaining}}</span>
                                </li>
                                @if($loan->disbursed)
                                <li class="list-group-item justify-content-between">
                                    <b>End Date:</b> {{Carbon\Carbon::parse($loan->end_date)->format('Y-m-d')}}
                                    {{--<span class="badge badge-success badge-pill">20</span>--}}
                                </li>
                               @endif
                            </ul>
                        </div>

                        @if($guarantor)
                            <div class="card-block guarantor-contact">
                                <ul class="list-group">
                                    <li class="list-group-item active"><a href="#" style="color: white">Loan Guarantor Details</a></li>

                                    <li class="list-group-item justify-content-between">
                                        <b>Name:</b> {{$guarantor->gname}}
                                        {{-- <span class="badge badge-primary badge-pill">30</span>--}}
                                    </li>

                                    <li class="list-group-item justify-content-between">
                                        <b>Phone Number:</b> {{$guarantor->gphone}}
                                    </li>
                                    <li class="list-group-item justify-content-between">
                                        <b>National ID Number:</b> {{$guarantor->gid}}
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Payments
                            </h4>
                        </div>
                        <div class="card-block">
                            <div class="connection-list">
                                <ul class="list-group">
                                    <li class="list-group-item justify-content-between">
                                        <b>Total Paid::</b>
                                        <span class="badge badge-info badge-pill">Ksh. {{number_format($loan->amount_paid)}}</span>
                                    </li>
                                    <li class="list-group-item justify-content-between">
                                        <b>Balance:</b>
                                        <span class="badge badge-danger badge-pill">Ksh. {{number_format($loan->balance)}}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="row">
                        <div class="col-12">
                            {{-- begin loans --}}
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-text">Active Loan</h4>

                                    <div class="row justify-content-center">
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Total Amount</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($totalAmount) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-bar-chart f-30 text-c-pink"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Total Paid</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($paidAmount) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-credit-card f-30 text-secondary"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">O.L.B.</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($balance) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-wind f-30 text-danger"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Principle</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($principalAmount) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-bookmark f-30 text-c-yellow"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Total Interest</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($interestAmount) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-sliders f-30 text-info"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Reg Fees</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($registrationFees) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-sliders f-30 text-danger"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                                    <div class="row">
                                        <div class="col-2">
                                            <button class="btn btn-primary mb-3 mt-1" id="btn-show-all-children" type="button">Expand All</button>
                                        </div>
                                        <div class="col-2">
                                            <button class="btn btn-primary mb-3 mt-1" id="btn-hide-all-children" type="button">Collapse All</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-block">
                                    <div class="dt-responsive table-responsive">
                                        <table id="loan-statements" class="table table-striped table-bordered nowrap">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Product</th>
                                                <th>Installments</th>
                                                <th>% Interest</th>
                                                <th>Amount</th>
                                                <th>Total</th>
                                                <th>Amount Paid</th>
                                                <th>Balance</th>
                                                <th>End Date</th>
                                                <th>Settled</th>
                                            </tr>
                                            </thead>

                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Product</th>
                                                <th>Installments</th>
                                                <th>% Interest</th>
                                                <th>Amount</th>
                                                <th>Total</th>
                                                <th>Amount Paid</th>
                                                <th>Balance</th>
                                                <th>End Date</th>
                                                <th>Settled</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            {{-- end loans --}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Loan Application Documents</h5>
                            @if($loan->document_path != null)
                                <a class="mt-1 btn btn-outline-success" href="{{ asset($loan->document_path) }}">View Loan Application Documents</a>
                                @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') or \Illuminate\Support\Facades\Auth::user()->hasRole('accountant') or \Illuminate\Support\Facades\Auth::user()->hasRole('manager'))
                                    <a class="mt-1 btn btn-outline-danger btn-sm" href="{{ route('loans.delete.document', ['id'=>encrypt($loan->id)]) }}">Delete Uploaded Documents</a>
                                @endif
                                @else
                                <div id="infoMessage" class="alert alert-warning mt-2">
                                    Loan does not have an uploaded Loan Application Form, edit loan details <a href="{{route('loans.edit', ['id' => encrypt($loan->id)])}}" style="font-weight: bolder">here</a> to upload a document
                                </div>
                            @endif

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-header-text">Collaterals</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                                                Add Collateral
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-block contact-details">
                                    <div class="data_table_main table-responsive dt-responsive">
                                        <table
                                               class="table  table-striped table-bordered nowrap">
                                            <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Description</th>
                                                <th>Serial No</th>
                                                <th>Market Value</th>
                                                <th>Image</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if($collaterals->count() == 0)
                                                <tr >
                                                    <td colspan="5" style="text-align: center">
                                                        No Collateral Added
                                                    </td>
                                                </tr>
                                            @endif
                                            @foreach($collaterals as $collateral)
                                                <tr>
                                                    <td>{{$collateral->item}}</td>
                                                    <td>{{$collateral->description}}</td>
                                                    <td>{{$collateral->serial_no}}</td>
                                                    <td>{{$collateral->market_value}}</td>
                                                    @if($collateral->image_url)
                                                    <td><img class="collateral_image"  data-url="{{$collateral->image_url}}" src="{{asset($collateral->image_url)}}" style="max-height: 50px; cursor: pointer "></td>
                                                        @else
                                                        <td>{{$collateral->image_url}}</td>

                                                    @endif
                                                </tr>
                                            @endforeach

                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <th>Item</th>
                                                <th>Description</th>
                                                <th>Serial No</th>
                                                <th>Market Value</th>
                                                <th>Image</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Collateral</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="card">
                            <div class="card-block">
                                <form action="{{route('loan.add_collateral', ['id' => encrypt($loan->id)])}}" method="post" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="item">Item</label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                                <input type="text" id="item" name="item"  class="form-control{{ $errors->has('item') ? ' is-invalid' : '' }}" required>

                                            </div>
                                            @if ($errors->has('item'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('item') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <label for="description">Description</label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                                <input type="text" id="description" name="description"  class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}" required>
                                            </div>
                                            @if ($errors->has('description'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <label for="serial_no">Serial No</label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1">@</span>
                                                <input type="text" name="serial_no" value="" class="form-control{{ $errors->has('serial_no') ? ' is-invalid' : '' }}" >


                                            </div>
                                            @if ($errors->has('serial_no'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('serial_no') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <label for="market_value">Market Value</label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                                <input type="text" name="market_value" value="" class="form-control{{ $errors->has('market_value') ? ' is-invalid' : '' }}" required>
                                            </div>
                                            @if ($errors->has('market_value'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('market_value') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="col-md-12">
                                            <label for="image_url">Image</label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-key"></i></span>
                                                <input type="file" name="image_url" value="" accept=".jpg,.jpeg,.png" class="form-control{{ $errors->has('image_url') ? ' is-invalid' : '' }}" required>
                                            </div>
                                            @if ($errors->has('image_url'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('image_url') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <button class="btn btn-primary float-left">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Collateral Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="card">
                            <div class="card-block">
                                <img style="width: 100%" id="img-url" src="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function () {
            $('.collateral_image').on('click', function (e) {
                $("#img-url").attr("src", '{{env('APP_URL')}}'+$(this).attr("data-url"));
                $('#imageModal').modal('show');
            })
        })
        $('#simpletable').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],

            ajax: '{!! route('payments', ['id' => encrypt($loan->id)]) !!}',
            columns: [
                {data: 'amount', name: 'amount'},
                {data: 'date_payed', name: 'date_payed'},
                {data: 'transaction_id', name: 'transaction_id'},
                {data: 'channel', name: 'channel'},
                {data: 'type', name: 'type'},

                /*{data: 'date_created', name: 'date_created'},
                {data: 'approved', name: 'approved'},
                {data: 'approved_date', name: 'approved_date'},
                {data: 'disbursed', name: 'disbursed'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'end_date', name: 'end_date'},
                {data: 'settled', name: 'settled'},
                { data: 'action', name: 'action', orderable: false, searchable: false }*/
            ],
        });
    </script>
    <script>
        $(document).ready(function() {
            function format (d) {
                function getLoanPayments()
                {
                    //loop through predictions array
                    var payments =  d.payments;
                    var dataArray = [];
                    for (var index = 0; index < payments.length; ++index) {
                        var transaction_id = payments[index].transaction_id;
                        var amount = payments[index].amount;
                        var type = payments[index].type == 'Processing Fee' ? 'Application Fee' : payments[index].type;
                        var date_payed = payments[index].date_payed;
                        var value = '<tr><td>'+type+ '</td><td>'+transaction_id+'</td><td>'+amount+'</td><td>'+ date_payed+'<td></tr>';
                        dataArray.push(value);
                    }
                    return dataArray;
                }
                return `<table style="margin-bottom: 1px; margin-top: 1px">
                                <thead>
                                    <tr>
                                        <th scope="col">Transaction Type</th>
                                        <th scope="col">Mpesa Confirmation Code</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${getLoanPayments()}
                                </tbody>
                            </table>`;
            }
            var  oTable =
                $('#loan-statements').DataTable({
                    "processing": true,
                    "serverSide": true,
                    dom: 'Bfrtip',
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
                    "lengthMenu": [[25, 50, -1], [25, 50, "All"]],
                    "order": [1, 'DESC'],
                    ajax:{
                        url: '{!! route('customer_account_statement_loans_data', ['customer'=> $customer_id]) !!}',
                    },
                    columns: [
                        {
                            className : 'details-control',
                            orderable : false,
                            data : null,
                            defaultContent : ''
                        },
                        {data: 'product_name', name: 'products.product_name'},
                        {data: 'installments', name: 'products.installments'},
                        {data: 'interest', name: 'products.interest'},
                        {data: 'loan_amount', name: 'loan_amount'},
                        {data: 'total', name: 'total'},
                        {data: 'amount_paid', name: 'amount_paid'},
                        {data: 'balance', name: 'balance'},
                        {data: 'end_date', name: 'end_date'},
                        {data: 'settled', name: 'settled'},
                    ],
                });
            // Add event listener for opening and closing details
            $('#loan-statements tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = oTable.row( tr );
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(row.data()) ).show();
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
@endsection
