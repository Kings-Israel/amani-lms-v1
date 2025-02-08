@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">



@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <form id="form" action="{{route('post_disburse_salary')}}" method="post">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3">
                                <button type="submit" class="approve-btn btn btn-primary">Pay</button>

                            </div>

                        </div>

                    </div>
                    <div class="card-block">
                        <div class="dt-responsive table-responsive">
                            <table id="cbtn-selectors1"
                                   class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th>Check To Pay</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Branch</th>
                                    <th>Salary</th>
                                    <th>Role</th>


                                    {{-- <th>Action</th>--}}

                                </tr>
                                </thead>

                                <tfoot>
                                <tr>
                                    <th>Check To Approve</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Branch</th>
                                    <th>Salary</th>
                                    <th>Role</th>


                                    {{--<th>Action</th>--}}
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
    <script type="text/javascript" src="{{asset('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js')}}"></script>


    <script>
        $(function () {
            //Add text editor
            $("#compose-textarea").wysihtml5();

        });

        $('#cbtn-selectors1').DataTable({
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',
            buttons: [/*{extend: 'copyHtml5', exportOptions: {columns: ':visible'}},*/ {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}},/* 'colvis',*/'pageLength'],


            ajax: '{!! route('ro_salary_settlement.data') !!}',
            columns: [
                /* {data: 'checkbox', name: 'checkbox'},*/
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },

                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'email', name: 'email'},
                {data: 'branch', name: 'branch'},
                {data: 'salary', name: 'salary'},
                {data: 'role', name: 'role'},


                /* { data: 'action', name: 'action', orderable: false, searchable: false }*/

            ],
        });


        /*************************multi approval*********************/
        $('.approve-btn').keypress(function(e) {
            if(e.which == 13) { // Checks for the enter key
                e.preventDefault(); // Stops IE from triggering the button to be clicked
            }
        });


        $(".approve-btn").on('click',function(e){

            if ($('input[name^=id]:checked').length <= 0) {
                swal(
                    "Warning",
                    "You Must check the employee you want to pay",
                    "warning"
                );
                return false
            }


            $('#form').on('submit', function(e) {
                var form = this;
                e.preventDefault();

                swal({
                        title: "Please confirm",
                        text: "Do you want to settle salaries the selected people?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#96d25f',
                        confirmButtonText: 'Yes, continue',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function() {
                        form.submit();
                    });
            });

        });
    </script>


@stop
