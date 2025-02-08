@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <form id="form" action="{{route('loans.post_disburse_multiple')}}" method="post">
                @csrf




            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <button type="submit" class="disburse-btn btn btn-primary">Disburse</button>


                        </div>
                    </div>

                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Amount</th>
                                <th>Branch</th>
                                <th>Date Approved</th>
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
                                <th>Branch</th>
                                <th>Date Approved</th>
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
    <script type="text/javascript" src="{{asset('bower_components/sweetalert/js/sweetalert.min.js')}}"></script>


    <script>
        $(document).ready(function() {

            $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
                "processing": true,
                "serverSide": true,
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('disburse_loans.data') !!}',
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                {data: 'owner', name: 'owner'},
                {data: 'phone', name: 'phone'},
                {data: 'product', name: 'product'},
                {data: 'installments', name: 'installments'},
                {data: 'interest', name: 'interest'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'branch_name', name: 'branch_name'},
                {data: 'approved_date', name: 'approved_date'},
                /*{data: 'disbursed', name: 'disbursed'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'end_date', name: 'end_date'},
                {data: 'settled', name: 'settled'},*/
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ],
        });

        /*************************multi approval*********************/
        $('.disburse-btn').keypress(function(e) {
            if(e.which == 13) { // Checks for the enter key
                e.preventDefault(); // Stops IE from triggering the button to be clicked
            }
        });


        $(".disburse-btn").on('click',function(e){

            if ($('input[name^=id]:checked').length <= 0) {
                swal(
                    "Warning",
                    "You Must check the loan you want to disburse",
                    "warning"
                );
                return false
            }


            $('#form').on('submit', function(e) {
                var form = this;
                e.preventDefault();

                swal({
                        title: "Please confirm",
                        text: "Do you want to disburse the selected loans?",
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



            $('body').on('click', 'a.disburse', function (e) {
                var form = $(this).attr("href");
                e.preventDefault();
                // console.log(form);

                swal({
                        title: "Please confirm",
                        text: "Do you want to disburse this loan?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#96d25f',
                        confirmButtonText: 'Yes, disburse',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function () {
                        window.location = form;
                    });
            });

            $('body').on('click', 'a.ldelete', function (e) {
                var form = $(this).attr("href");
                e.preventDefault();
                // console.log(form);

                swal({
                        title: "Please confirm",
                        text: "Do you want to delete this loan?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#96d25f',
                        confirmButtonText: 'Yes, Delete',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function () {
                        window.location = form;
                    });
            });


        })
    </script>


@stop
