@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.css') }}">
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-3 col-md-6">
            @include('layouts.alert')
        </div>
    </div>
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
                            <p class="m-b-5">Total Principle</p>
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
                            <p class="m-b-5">Registration Fees</p>
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
        <div class="col-sm-12">
            <div class="card z-depth-bottom-2">
                <div class="card-block">
                    <button class="btn btn-sm btn-primary mb-3 mt-1" id="btn-show-all-children" type="button">Expand
                        All</button>
                    <button class="btn btn-sm btn-primary mb-3 mt-1" id="btn-hide-all-children" type="button">Collapse
                        All</button>

                    <div class="dt-responsive table-responsive">
                        <table id="loan-statements" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>#</th>
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
                                    <th>#</th>
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
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
        $(document).ready(function() {
            function format(d) {
                function getLoanPayments() {
                    //loop through predictions array
                    var payments = d.payments;
                    var dataArray = '';
                    for (var index = 0; index < payments.length; ++index) {
                        var transaction_id = payments[index].transaction_id
                        var amount = payments[index].amount
                        var type = payments[index].type == 'Processing Fee' ? 'Application Fee' : payments[index].type
                        var date_payed = payments[index].date_payed
                        var value = '<tr><td>' + type + '</td><td>' + transaction_id + '</td><td>' + amount + '</td><td>' + date_payed + '<td></tr>'
                        dataArray += value
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
            var oTable =
                $('#loan-statements').DataTable({
                    "processing": true,
                    "serverSide": true,
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'copyHtml5'
                        }, {
                            extend: 'excelHtml5',
                            exportOptions: {
                                columns: ':visible'
                            },
                        },
                        {
                            extend: 'pdfHtml5',
                            orientation: 'landscape',
                            pageSize: 'TABLOID'
                        },
                        'colvis', 'pageLength'
                    ],
                    "lengthMenu": [
                        [25, 50, -1],
                        [25, 50, "All"]
                    ],
                    "order": [1, 'DESC'],
                    ajax: {
                        url: '{!! route('customer_account_statement_loans_data', ['customer' => $custID]) !!}',
                    },
                    columns: [
                        {
                            className: 'details-control',
                            orderable: false,
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            name: 'id'
                        },
                        {
                            data: 'product_name',
                            name: 'products.product_name'
                        },
                        {
                            data: 'installments',
                            name: 'products.installments'
                        },
                        {
                            data: 'interest',
                            name: 'products.interest'
                        },
                        {
                            data: 'loan_amount',
                            name: 'loan_amount'
                        },
                        {
                            data: 'total',
                            name: 'total'
                        },
                        {
                            data: 'amount_paid',
                            name: 'amount_paid'
                        },
                        {
                            data: 'balance',
                            name: 'balance'
                        },
                        {
                            data: 'end_date',
                            name: 'end_date'
                        },
                        {
                            data: 'settled',
                            name: 'settled'
                        },
                    ],
                });
            // Add event listener for opening and closing details
            $('#loan-statements tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = oTable.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Open this row
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            // Handle click on "Expand All" button
            $('#btn-show-all-children').on('click', function() {
                // Enumerate all rows
                oTable.rows().every(function() {
                    // If row has details collapsed
                    if (!this.child.isShown()) {
                        // Open this row
                        this.child(format(this.data())).show();
                        $(this.node()).addClass('shown');
                    }
                });
            });

            // Handle click on "Collapse All" button
            $('#btn-hide-all-children').on('click', function() {
                // Enumerate all rows
                oTable.rows().every(function() {
                    // If row has details expanded
                    if (this.child.isShown()) {
                        // Collapse row details
                        this.child.hide();
                        $(this.node()).removeClass('shown');
                    }
                });
            });

        });
    </script>
@stop
