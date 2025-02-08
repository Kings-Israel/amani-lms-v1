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
            <form id="form" action="{{ route('loans.post_approve_multiple') }}" method="post">
                @csrf
                <input id="activity_token" type="hidden" name="activity_token">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3">
                                <button type="submit" class="approve-btn btn btn-primary">Approve</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
            jQuery(document).ready(function () {
                "use strict";
                jQuery('#datetimepicker1').datetimepicker({
                    format:'Y-m-d'
                });
            })

            let example = $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                "processing": true,
                "serverSide": true,
                "ordering": false,

                columnDefs: [{
                    'targets': 0,
                    'checkboxes': {
                        'selectRow': true
                    }
                }],
                'select': {
                    'style': 'multi'
                },
                buttons: [
                    {
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
                    },
                    'colvis',
                    'pageLength'
                ],
                ajax: {
                    url: '{!! route('approve_loans.data') !!}',
                    data: function (d) {
                        d.branch = $('select[name=branch_id]').val();
                        d.lf = $('select[name=name]').val();
                        d.date = $('input[name=date]').val();
                    }

                },
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
                        name: 'phone',
                    },
                    {
                        data: 'id_number',
                        name: 'id_number',
                    },
                    {
                        data: 'loan_amount',
                        name: 'loan_amount'
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
                        data: 'regPayment',
                        name: 'reg_payment'
                    },
                    {
                        data: 'date_created',
                        name: 'date_created'
                    },
                    {data: 'referee', name: 'referee'},
                    {data: 'location', name: 'location'},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            var $_base = '{{ env('APP_URL') }}';

            /*************************multi approval*********************/
            $('.approve-btn').keypress(function(e) {
                if (e.which == 13) { // Checks for the enter key
                    e.preventDefault(); // Stops IE from triggering the button to be clicked
                }
            });

            $(".approve-btn").on('click', function(e) {
                var rows_selected = example.column(0).checkboxes.selected();
                if (rows_selected.length <= 0) {
                    Swal.fire({
                        // title: 'Warning!!!',
                        text: "You Must check the loan you want to approve",
                        icon: 'warning',
                        // showCancelButton: true,
                        confirmButtonColor: '#8ec63f',
                        //cancelButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    })
                    return false
                }

                var form = $('#form');
                $.each(rows_selected, function(index, rowId){
                    $(form).append(
                        $('<input>')
                            .attr('type', 'hidden')
                            .attr('name', 'id[]')
                            .val(rowId)
                    )
                });

                // if ($('input[name^=id]:checked').length <= 0) {
                //     Swal.fire({
                //         // title: 'Warning!!!',
                //         text: "You Must check the loan you want to approve",
                //         icon: 'warning',
                //         // showCancelButton: true,
                //         confirmButtonColor: '#8ec63f',
                //         //cancelButtonColor: '#d33',
                //         confirmButtonText: 'OK'
                //     })
                //     return false
                // }

                let start_date = ''

                $('#form').on('submit', function(e) {
                    var form = this;
                    e.preventDefault();
                    Swal.fire({
                            title: "Select start date (date when payment is to start)",
                            input: "date",
                            showCancelButton: true,
                            confirmButtonColor: '#8ec63f',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Approve'
                        }).then(result => {
                            if (result.isConfirmed) {
                                start_date = result.value;
                                $(form).append(
                                        $('<input>')
                                            .attr('type', 'hidden')
                                            .attr('name', 'start_date')
                                            .val(start_date))

                                //valid token
                                Swal.fire({
                                    title: 'Please Confirm?',
                                    text: "Do you want to approve the selected loan!",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#8ec63f',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Yes, Approve!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        form.submit();
                                    }
                                })
                            }
                            // form.submit();
                        });
                    //check validity of the
                    // $.ajax({
                    //     headers: {
                    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    //     },
                    //     cache: false,
                    //     method: "get",
                    //     url: $_base + "ajax_verify_token/" +'{{ decrypt($approval_token_session) }}' + "/approve",
                    //     dataType: 'json',
                    //     success: function(json) {
                    //         if (json.valid === 1) {
                    //             Swal.fire({
                    //                 title: "Select start date (date when payment is to start)",
                    //                 input: "date",
                    //                 showCancelButton: true,
                    //                 confirmButtonColor: '#8ec63f',
                    //                 cancelButtonColor: '#d33',
                    //                 confirmButtonText: 'Approve'
                    //             }).then(result => {
                    //                 if (result.isConfirmed) {
                    //                     start_date = result.value
                    //                     $(form).append(
                    //                             $('<input>')
                    //                                 .attr('type', 'hidden')
                    //                                 .attr('name', 'start_date')
                    //                                 .val(start_date))

                    //                     //valid token
                    //                     Swal.fire({
                    //                         title: 'Please Confirm?',
                    //                         text: "Do you want to approve the selected loan!",
                    //                         icon: 'warning',
                    //                         showCancelButton: true,
                    //                         confirmButtonColor: '#8ec63f',
                    //                         cancelButtonColor: '#d33',
                    //                         confirmButtonText: 'Yes, Approve!'
                    //                     }).then((result) => {
                    //                         if (result.isConfirmed) {
                    //                             form.submit();
                    //                         }
                    //                     })
                    //                 }
                    //             });
                    //             return false
                    //         } else {
                    //             send_token();
                    //             Swal.fire({
                    //                 title: 'Enter Your Activity Token',
                    //                 input: 'text',
                    //                 inputAttributes: {
                    //                     autocapitalize: 'off'
                    //                 },
                    //                 showCancelButton: true,
                    //                 confirmButtonText: 'Submit',
                    //                 confirmButtonColor: '#8ec63f',
                    //                 showLoaderOnConfirm: true,
                    //                 preConfirm: (login) => {
                    //                     return fetch($_base + 'ajax_verify_token/' + login + "/approve")
                    //                         .then(response => response.json())
                    //                         .then(json => {
                    //                             valid = JSON.stringify(
                    //                                 json.valid);
                    //                             if (valid === "0") {
                    //                                 Swal.showValidationMessage(
                    //                                     `Invalid Activity Token`
                    //                                 )
                    //                                 return 0;
                    //                             } else {
                    //                                 Swal.fire({
                    //                                     title: "Select start date (date when payment is to start)",
                    //                                     input: "date",
                    //                                     showCancelButton: true,
                    //                                     confirmButtonColor: '#8ec63f',
                    //                                     cancelButtonColor: '#d33',
                    //                                     confirmButtonText: 'Approve'
                    //                                 }).then(result => {
                    //                                     if (result.isConfirmed) {
                    //                                         start_date = result.value;
                    //                                         $(form).append(
                    //                                                 $('<input>')
                    //                                                     .attr('type', 'hidden')
                    //                                                     .attr('name', 'start_date')
                    //                                                     .val(start_date))

                    //                                         //valid token
                    //                                         Swal.fire({
                    //                                             title: 'Please Confirm?',
                    //                                             text: "Do you want to approve the selected loan!",
                    //                                             icon: 'warning',
                    //                                             showCancelButton: true,
                    //                                             confirmButtonColor: '#8ec63f',
                    //                                             cancelButtonColor: '#d33',
                    //                                             confirmButtonText: 'Yes, Approve!'
                    //                                         }).then((result) => {
                    //                                             if (result.isConfirmed) {
                    //                                                 form.submit();
                    //                                             }
                    //                                         })
                    //                                     }
                    //                                     // form.submit();
                    //                                 });
                    //                             }
                    //                         })
                    //                         .catch(error => {
                    //                             Swal.showValidationMessage(
                    //                                 `Request failed: ${error}`
                    //                             )
                    //                         })
                    //                 },
                    //                 allowOutsideClick: () => !Swal.isLoading()
                    //             })
                    //         }
                    //     }
                    // });
                });
            });

            $('body').on('click', 'a.approve', function(e) {
                var form = $(this).attr("href");
                e.preventDefault();
                Swal.fire({
                        title: "Select start date (date when payment is to start)",
                        input: "date",
                        showCancelButton: true,
                        confirmButtonColor: '#8ec63f',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Approve'
                    }).then(result => {
                        if (result.isConfirmed) {
                            start_date = result.value

                            Swal.fire({
                                title: 'Please Confirm?',
                                text: "Do you want to approve the selected loan!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#8ec63f',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes, Approve!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // form.submit();
                                    window.location = form + '?start_date='+start_date;
                                }
                            })
                        }
                    });
                //check validity of the
                // $.ajax({
                //     headers: {
                //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                //     },
                //     cache: false,
                //     method: "get",
                //     url: $_base + "ajax_verify_token/" + '{{ decrypt($approval_token_session) }}' +"/approve",
                //     dataType: 'json',
                //     success: function(json) {
                //         if (json.valid === 1) {
                //             // Valid token
                //             Swal.fire({
                //                 title: "Select start date (date when payment is to start)",
                //                 input: "date",
                //                 showCancelButton: true,
                //                 confirmButtonColor: '#8ec63f',
                //                 cancelButtonColor: '#d33',
                //                 confirmButtonText: 'Approve'
                //             }).then(result => {
                //                 if (result.isConfirmed) {
                //                     start_date = result.value
                //                     //valid token
                //                     Swal.fire({
                //                         title: 'Please Confirm?',
                //                         text: "Do you want to approve the selected loan!",
                //                         icon: 'warning',
                //                         showCancelButton: true,
                //                         confirmButtonColor: '#8ec63f',
                //                         cancelButtonColor: '#d33',
                //                         confirmButtonText: 'Yes, Approve!'
                //                     }).then((result) => {
                //                         if (result.isConfirmed) {
                //                             window.location = form + '?start_date='+start_date;
                //                         }
                //                     })
                //                 }
                //             });

                //             // Swal.fire({
                //             //     title: 'Please Confirm?',
                //             //     text: "Do you want to approve the selected loan!",
                //             //     icon: 'warning',
                //             //     showCancelButton: true,
                //             //     confirmButtonColor: '#8ec63f',
                //             //     cancelButtonColor: '#d33',
                //             //     confirmButtonText: 'Yes, Approve!'
                //             // }).then((result) => {
                //             //     if (result.isConfirmed) {
                //             //         window.location = form
                //             //     }
                //             // })
                //             return false
                //         } else {
                //             send_token();
                //             Swal.fire({
                //                 title: 'Enter Your Activity Token',
                //                 input: 'text',
                //                 inputAttributes: {
                //                     autocapitalize: 'off'
                //                 },
                //                 showCancelButton: true,
                //                 confirmButtonText: 'Submit',
                //                 confirmButtonColor: '#8ec63f',

                //                 showLoaderOnConfirm: true,
                //                 preConfirm: (login) => {
                //                     return fetch($_base + 'ajax_verify_token/' + login + "/approve")

                //                         .then(response => response.json())
                //                         .then(json => {
                //                             valid = JSON.stringify(json.valid);
                //                             if (valid === "0") {
                //                                 Swal.showValidationMessage(`Invalid Activity Token`)
                //                                 return 0;
                //                             } else {
                //                                 Swal.fire({
                //                                     title: "Select start date (date when payment is to start)",
                //                                     input: "date",
                //                                     showCancelButton: true,
                //                                     confirmButtonColor: '#8ec63f',
                //                                     cancelButtonColor: '#d33',
                //                                     confirmButtonText: 'Approve'
                //                                 }).then(result => {
                //                                     if (result.isConfirmed) {
                //                                         start_date = result.value

                //                                         Swal.fire({
                //                                             title: 'Please Confirm?',
                //                                             text: "Do you want to approve the selected loan!",
                //                                             icon: 'warning',
                //                                             showCancelButton: true,
                //                                             confirmButtonColor: '#8ec63f',
                //                                             cancelButtonColor: '#d33',
                //                                             confirmButtonText: 'Yes, Approve!'
                //                                         }).then((result) => {
                //                                             if (result.isConfirmed) {
                //                                                 // form.submit();
                //                                                 window.location = form + '?start_date='+start_date;
                //                                             }
                //                                         })
                //                                     }
                //                                 });
                //                             }
                //                         })
                //                         .catch(error => {
                //                             Swal.showValidationMessage(
                //                                 `Request failed: ${error}`
                //                             )
                //                         })
                //                 },
                //                 allowOutsideClick: () => !Swal.isLoading()
                //             })
                //         }
                //     }
                // });
            });

            function send_token() {
                //send the otp
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    cache: false,
                    method: "get",
                    url: $_base + "ajax_send_token",
                    dataType: 'json',
                    data: {
                        activity: "approve",
                    },
                    success: function(json) {
                        // $('#pre').text(json.data['pre']);
                    }
                });
            }

            // Delete Loan
            $('body').on('click', 'a.ldelete', function(e) {
                var form = $(this).attr("href");
                e.preventDefault();
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
                        window.location = form;
                    }
                })
            });

            $('#search').on('submit', function(e) {
                example.draw();
                e.preventDefault();
            });
        })
    </script>
@endsection
