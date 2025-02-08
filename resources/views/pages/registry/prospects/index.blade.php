@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">


@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <form id="form" action="{{route('sms_selected')}}" method="post">
                <input type="hidden" name="check" value="selected">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" name="sms" class="approve-btn btn btn-primary">Send Selected</button>
                                <a href="{{route('prospects_sms')}}" class="btn btn-primary">Send All</a>
                                <a href="{{route('prospects.create')}}" class="btn btn-primary">Create New</a>

                                <a href="{{route('delete_all_prospects')}}" onclick="confirm(`Are you sure you want to delete all {{ number_format($prospects_count) }}  prospects?`)" class="btn btn-primary">Delete All Prospects</a>

                                <form id="form2" action="{{route('delete_selected')}}" method="post">
                                    @csrf
                                    <button type="submit" name="delete" value="delete_btn" class="delete-btn btn btn-primary">Delete Selected</button>
                                </form>




                            </div>
                            <div class="col-md-3">

                            </div>

                        </div>

                    </div>
                    <div class="card-block">
                        <div class="dt-responsive table-responsive">
                            <table id="cbtn-selectors1"
                                   class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th>Check To Approve</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Action</th>

                                </tr>
                                </thead>

                                <tfoot>
                                <tr>
                                    <th>Check To Approve</th>
                                    <th>Name</th>
                                    <th>Phone</th>
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
                buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                    extend: 'excelHtml5',
                    exportOptions: {columns: ':visible'}
                }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis', 'pageLength'],


                ajax: '{!! route('prospects.data') !!}',
                columns: [
                    /* {data: 'checkbox', name: 'checkbox'},*/
                    {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false},

                    {data: 'name', name: 'name'},
                    {data: 'phone', name: 'phone'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}

                ],
            });


            /*************************multi send sms*********************/
            $('.approve-btn').keypress(function (e) {
                if (e.which == 13) { // Checks for the enter key
                    e.preventDefault(); // Stops IE from triggering the button to be clicked
                }
            });


            $(".approve-btn").on('click', function (e) {

                if ($('input[name^=id]:checked').length <= 0) {
                    swal(
                        "Warning",
                        "You Must check the prospect you want to send sms",
                        "warning"
                    );
                    return false
                }


                $('#form').on('submit', function (e) {
                    var form = this;
                    e.preventDefault();

                    swal({
                            title: "Please confirm",
                            text: "Do you want to send sms to selected prospects?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: '#96d25f',
                            confirmButtonText: 'Yes, Send',
                            cancelButtonText: "Cancel",
                            closeOnConfirm: true,
                            closeOnCancel: true
                        },
                        function () {
                            form.submit();
                        });
                });

            });


            /*************************multi send sms*********************/
            $('.delete-btn').keypress(function (e) {
                if (e.which == 13) { // Checks for the enter key
                    e.preventDefault(); // Stops IE from triggering the button to be clicked
                }
            });


            $(".delete-btn").on('click', function (e) {

                if ($('input[name^=id]:checked').length <= 0) {
                    swal(
                        "Warning",
                        "You Must check the prospect you want to delete",
                        "warning"
                    );
                    return false
                }


                $('#form2').on('submit', function (e) {
                    var form = this;
                    e.preventDefault();

                    swal({
                            title: "Please confirm",
                            text: "Do you want to delete selected prospects?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: '#96d25f',
                            confirmButtonText: 'Yes, Delete',
                            cancelButtonText: "Cancel",
                            closeOnConfirm: true,
                            closeOnCancel: true
                        },
                        function () {
                            form.submit();
                        });
                });

            });

           /* $('body').on('click', 'a.approve', function (e) {
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
                    function () {
                        window.location = form;
                    });
            });*/


        })


    </script>


@stop
