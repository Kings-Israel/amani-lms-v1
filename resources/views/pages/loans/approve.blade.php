@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/sweetalert/css/sweetalert.css') }}">
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <form id="form" action="{{ route('loans.post_approve_multiple') }}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3">
                                <button type="submit" class="approve-btn btn btn-primary">Approve</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-block">
                        <div class="dt-responsive table-responsive">
                            <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Owner</th>
                                        <th>Phone</th>
                                        <th>Product</th>
                                        <th>Installments</th>
                                        <th>% Interest</th>
                                        <th>Amount</th>
                                        <th>Date Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>Owner</th>
                                        <th>Phone</th>
                                        <th>Product</th>
                                        <th>Installments</th>
                                        <th>% Interest</th>
                                        <th>Amount</th>
                                        <th>Date Created</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script type="text/javascript" src="{{ asset('bower_components/sweetalert/js/sweetalert.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                "processing": true,
                "serverSide": true,
                buttons: [
                    'selectAll',
                    'selectNone',
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
                    'colvis', 'pageLength'
                ],

                ajax: '{!! route('approve_loans.data') !!}',
                columns: [
                    // {data: 'checkbox', name: 'checkbox'},
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'owner',
                        name: 'owner'
                    },
                    {
                        data: 'phone',
                        name: 'customers.phone'
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
                        data: 'date_created',
                        name: 'date_created'
                    },
                    /*{data: 'approved', name: 'approved'},*/
                    // {data: 'approved_date', name: 'approved_date'},
                    /*{data: 'disbursed', name: 'disbursed'},
                    {data: 'disbursement_date', name: 'disbursement_date'},
                    {data: 'end_date', name: 'end_date'},
                    {data: 'settled', name: 'settled'},*/
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });


            /*************************multi approval*********************/
            $('.approve-btn').keypress(function(e) {
                if (e.which == 13) { // Checks for the enter key
                    e.preventDefault(); // Stops IE from triggering the button to be clicked
                }
            });

            $(".approve-btn").on('click', function(e) {
                if ($('input[name^=id]:checked').length <= 0) {
                    swal(
                        "Warning",
                        "You Must check the loan you want to approve",
                        "warning"
                    );
                    return false
                }

                $('#form').on('submit', function(e) {
                    var form = this;
                    e.preventDefault();

                    swal({
                        title: "Please confirm",
                        text: "Do you want to approve the selected loans?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#96d25f',
                        confirmButtonText: 'Yes, approve',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function() {
                        form.submit();
                    });
                });
            });

            $('body').on('click', 'a.approve', function(e) {
                var form = $(this).attr("href");
                e.preventDefault();
                // console.log(form);

                swal({
                    title: "Please confirm",
                    text: "Do you want to approve this loan?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#96d25f',
                    confirmButtonText: 'Yes, approve',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function() {
                    window.location = form;
                });
            });

            $('body').on('click', 'a.ldelete', function(e) {
                var form = $(this).attr("href");
                e.preventDefault();
                swal({
                    title: "Please confirm",
                    text: "Do you want to delete this loan?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#96d25f',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function() {
                    window.location = form;
                });
            });
        })
    </script>
@stop
