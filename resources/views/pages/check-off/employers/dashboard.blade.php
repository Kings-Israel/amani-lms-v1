@extends("pages.check-off.employers.layouts.master")

@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bulma/bulma.css" rel="stylesheet">



@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Institution</th>
                                <th>Loan Amount</th>
                                <th>Interest</th>
                                <th>Total Amount</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>End Date</th>
                                <th>Approved</th>
                                <th>Settled</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Institution</th>
                                <th>Loan Amount</th>
                                <th>Interest</th>
                                <th>Total Amount</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>End Date</th>
                                <th>Approved</th>
                                <th>Settled</th>
                                <th>Created At</th>
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
    {{--
        <script type="text/javascript" src="{{asset('bower_components/sweetalert/js/sweetalert.min.js')}}"></script>
    --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>


    <script>
        $(document).ready(function() {

            $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {extend: 'copyHtml5', exportOptions: {columns: ':visible'}},
                    {extend: 'excelHtml5', exportOptions: {columns: ':visible'}},
                    {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}},
                    'colvis', 'pageLength'],
                "processing": true,
                "serverSide": true,
                ajax: '{!! route('check_off.approve_loans.data') !!}',
                order: [0, 'desc'],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'full_name', name: 'employee.last_name',},
                    {data: 'employee.phone_number', name: 'employee.phone_number'},
                    {data: 'employee.employer.name', name: 'employee.employer.name'},
                    {data: 'loan_amount', name: 'loan_amount'},
                    {data: 'interest', name: 'interest'},
                    {data: 'total_amount', name: 'total_amount'},
                    {data: 'amount_paid', name: 'amount_paid'},
                    {data: 'balance', name: 'balance'},
                    {data: 'end_date', name: 'end_date'},
                    {data: 'approved', name: 'approved'},
                    {data: 'settled', name: 'settled'},
                    {data: 'created_at', name: 'created_at'},
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });
            var $_base = '{{env('APP_URL')}}';



            /*************************multi approval*********************/
            $('.approve-btn').keypress(function (e) {
                if (e.which == 13) { // Checks for the enter key
                    e.preventDefault(); // Stops IE from triggering the button to be clicked
                }
            });



            $(".approve-btn").on('click', function (e) {

                if ($('input[name^=id]:checked').length <= 0) {

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









                $('#form').on('submit', function (e) {
                    var form = this;
                    e.preventDefault();

                    //check validity of the
                    $.ajax({

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            // 'Cache-Control': 'no-cache, no-store, must-revalidate',

                        },
                        cache: false,
                        method: "get",
                        url: $_base + "ajax_verify_token/"+'1'+"/approve",
                        dataType: 'json',
                        success: function (json) {
                            if(json.valid === 1){
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

                                return false
                            } else{
                                send_token();


                                Swal.fire({
                                    title: 'Enter Your Activity Token',
                                    input: 'text',
                                    inputAttributes: {
                                        autocapitalize: 'off'
                                    },
                                    showCancelButton: true,
                                    confirmButtonText: 'Submit',
                                    confirmButtonColor: '#8ec63f',

                                    showLoaderOnConfirm: true,
                                    preConfirm: (login) => {


                                        //alert(login)
                                        //return fetch(`//api.github.com/users/${login}`)
                                        return fetch($_base+'ajax_verify_token/'+login+"/approve")



                                            /*.then(response => {
                                                console.log(response)
                                                if (!response.ok) {
                                                    throw new Error(response.statusText)
                                                }
                                                //return response.json()
                                                return 0;
                                            })*/
                                            .then(response => response.json())
                                            .then(json => {
                                                valid = JSON.stringify(json.valid);
                                                if(valid === "0"){
                                                    console.log(JSON.stringify(json.valid))
                                                    Swal.showValidationMessage(
                                                        `Invalid Activity Token`
                                                    )
                                                    return 0;


                                                } else{
                                                    form.submit();

                                                }

                                            })

                                            .catch(error => {
                                                Swal.showValidationMessage(
                                                    `Request failed: ${error}`
                                                )
                                            })
                                        //$('#activity_token').val(login);
                                        //form.submit();

                                    },
                                    allowOutsideClick: () => !Swal.isLoading()
                                })


                            }



                        }
                    });


                });

            });

            $('body').on('click', 'a.approve', function (e) {
                var form = $(this).attr("href");
                e.preventDefault();
                //check validity of the
                $.ajax({

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        // 'Cache-Control': 'no-cache, no-store, must-revalidate',

                    },
                    cache: false,
                    method: "get",
                    url: $_base + "ajax_verify_token/"+'{{1}}'+"/approve",
                    dataType: 'json',
                    success: function (json) {
                        if(json.valid === 1){
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
                                    window.location = form;


                                }

                            })

                            return false
                        } else{
                            send_token();


                            Swal.fire({
                                title: 'Enter Your Activity Token',
                                input: 'text',
                                inputAttributes: {
                                    autocapitalize: 'off'
                                },
                                showCancelButton: true,
                                confirmButtonText: 'Submit',
                                confirmButtonColor: '#8ec63f',

                                showLoaderOnConfirm: true,
                                preConfirm: (login) => {
                                    return fetch($_base+'ajax_verify_token/'+login+"/approve")

                                        .then(response => response.json())
                                        .then(json => {
                                            valid = JSON.stringify(json.valid);
                                            if(valid === "0"){
                                                console.log(JSON.stringify(json.valid))
                                                Swal.showValidationMessage(
                                                    `Invalid Activity Token`
                                                )
                                                return 0;


                                            } else{
                                                //form.submit();
                                                window.location = form;

                                            }

                                        })

                                        .catch(error => {
                                            Swal.showValidationMessage(
                                                `Request failed: ${error}`
                                            )
                                        })
                                    //$('#activity_token').val(login);
                                    //form.submit();

                                },
                                allowOutsideClick: () => !Swal.isLoading()
                            })


                        }



                    }
                });




            });

            function send_token(){
                //send the otp
                $.ajax({

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        // 'Cache-Control': 'no-cache, no-store, must-revalidate',

                    },
                    cache: false,
                    method: "get",
                    url: $_base + "ajax_send_token",
                    dataType: 'json',
                    data: {
                        activity: "approve",



                    },

                    success: function (json) {

                        // $('#pre').text(json.data['pre']);


                    }
                });
            }

            //function se

            $('body').on('click', 'a.ldelete', function (e) {
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

        })


    </script>


@stop

@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-header">

                    <div class="row col-md-3">
                        <a class="btn btn-primary" href="{{route('products.create')}}">Add New</a>
                    </div>

                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Installments</th>
                                <th>Interest</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Installments</th>
                                <th>Interest</th>
                                <th>Status</th>
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
        $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis'],


            ajax: '{!! route('products.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'product_name', name: 'product_name'},
                {data: 'installments', name: 'installments'},
                {data: 'interest', name: 'interest'},
                {data: 'status', name: 'status'},
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ],
        });
    </script>


@stop
