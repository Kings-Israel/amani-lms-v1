@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/sweetalert/css/sweetalert.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bulma/bulma.css" rel="stylesheet">
    <link type="text/css" href="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css" rel="stylesheet" />
    <style>
        table.dataTable tr th.select-checkbox.selected::after {
            content: "✔";
            margin-top: -11px;
            margin-left: -4px;
            text-align: center;
            text-shadow: rgb(176, 190, 217) 1px 1px, rgb(176, 190, 217) -1px -1px, rgb(176, 190, 217) 1px -1px, rgb(176, 190, 217) -1px 1px;
        }
    </style>
@endsection

@section('content')
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
                                    <option value="all" > All </option>
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
                                    <option value="all">All</option>
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
                        <div class="col-md-3">
                            <label for="date">Date</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                    class="icofont icofont-bank-alt"></i></span>
                                <input id="datetimepicker1" type="text" autocomplete="off" name="date" class="form-control{{ $errors->has('date') ? ' is-invalid' : '' }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Owner</th>
                                    <th>Phone</th>
                                    <th>ID Number</th>
                                    <th>Amount</th>
                                    <th>Product</th>
                                    <th>Installments</th>
                                    <th>% Interest</th>
                                    <th>Registration Fee Paid</th>
                                    <th>Date Created</th>
                                    <th>Guarantor</th>
                                    <th>Location (Ward, Const., County)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th>Owner</th>
                                    <th>Phone</th>
                                    <th>ID Number</th>
                                    <th>Amount</th>
                                    <th>Product</th>
                                    <th>Installments</th>
                                    <th>% Interest</th>
                                    <th>Registration Fee Paid</th>
                                    <th>Date Created</th>
                                    <th>Guarantor</th>
                                    <th>Location (Ward, Const., County)</th>
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
    <script type="text/javascript" src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            let example = $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                processing: true,
                serverSide: true,
                ordering: false,
                columnDefs: [{
                    targets: 0,
                    checkboxes: {
                        selectRow: true
                    }
                }],
                select: {
                    style: 'multi'
                },
                buttons: [
                    {
                        extend: 'copyHtml5',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    'colvis',
                    'pageLength'
                ],
                ajax: {
                    url: '{{ route('waiting_approve_loans.data') }}',
                    data: function(d) {
                        d.branch = $('select[name=branch_id]').val();
                        d.lf = $('select[name=name]').val();
                        d.date = $('input[name=date]').val();
                    }
                },
                columns: [
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'owner', name: 'owner' },
                    { data: 'phone', name: 'phone' },
                    { data: 'id_number', name: 'id_number' },
                    { data: 'loan_amount', name: 'loan_amount' },
                    { data: 'product_name', name: 'products.product_name' },
                    { data: 'installments', name: 'products.installments' },
                    { data: 'interest', name: 'products.interest' },
                    { data: 'regPayment', name: 'reg_payment' },
                    { data: 'date_created', name: 'date_created' },
                    { data: 'referee', name: 'referee' },
                    { data: 'location', name: 'location' },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Multi-approval button click handler
            $(".approve-btn").on('click', function(e) {
                e.preventDefault();
                var rowsSelected = example.column(0).checkboxes.selected();

                if (rowsSelected.length === 0) {
                    Swal.fire({
                        text: "You must check the loan(s) you want to approve.",
                        icon: 'warning',
                        confirmButtonColor: '#8ec63f',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                var form = $('#form');
                form.find('input[name="id[]"]').remove();

                $.each(rowsSelected, function(index, rowId) {
                    form.append(
                        $('<input>', {
                            type: 'hidden',
                            name: 'id[]',
                            value: rowId
                        })
                    );
                });

                form.append(
                    $('<input>', {
                        type: 'hidden',
                        name: 'source',
                        value: 'main'
                    })
                );

                Swal.fire({
                    title: 'Please Confirm',
                    text: "Do you want to approve the selected loan(s)?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#8ec63f',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Accept!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Individual loan approval click handler
            $('body').on('click', 'a.approve', function(e) {
                e.preventDefault();
                var url = $(this).attr("href");

                Swal.fire({
                    title: 'Please Confirm',
                    text: "Do you want to accept this loan?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#8ec63f',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Accept!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Accepted!',
                                    text: 'The loan has been accepted.',
                                    confirmButtonColor: '#8ec63f'
                                }).then(() => {
                                    example.ajax.reload(); // Reload DataTable
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            });

            // Loan deletion click handler
            $('body').on('click', 'a.ldelete', function(e) {
                e.preventDefault();
                var url = $(this).attr("href");

                Swal.fire({
                    title: 'Please Confirm',
                    text: "Do you want to delete this loan?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#8ec63f',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'The loan has been deleted.',
                                    confirmButtonColor: '#8ec63f'
                                }).then(() => {
                                    example.ajax.reload(); // Reload DataTable
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            });

            // Filter loans
            $('#search').on('submit', function(e) {
                e.preventDefault();
                example.draw();
            });
        });
    </script>
@endsection
