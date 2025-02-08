@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">


@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


<form id="form" action="{{route('groups.approve_multiple')}}" method="post">
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
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Name</th>
                                <th>GroupID</th>
                                <th>Leader</th>
                                <th>Contact</th>
                                <th>Members Count</th>
                                <th>Approval Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Name</th>
                                <th>GroupID</th>
                                <th>Leader</th>
                                <th>Contact</th>
                                <th>Members Count</th>
                                <th>Approval Status</th>
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
            function format ( d ) {
                console.log(d);
                function getMembers(){
                    //loop through predictions array
                    var members =  d.members;
                    var dataArray = [];
                    for (var index = 0; index < members.length; ++index) {
                        var name = members[index].name;
                        var contact = members[index].contact;
                        var role = members[index].role;
                        var badge;
                        if (role === 'leader'){
                            badge = '<h6><span class="badge badge-warning"><b>L</b></span></h6>'
                        } else{
                            badge = '';
                        }
                        var str = '';
                        var value = str.concat( name, ' - ', contact, '<br>');
                        dataArray.push(value);
                    }
                    return dataArray.join('<br/>');
                }
                let output = `
                        <table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">
                            <tr>
                                <td><b>Registered Branch</b></td>
                                <td colspan="4">${d.branch}</td>

                            </tr>
                            <tr>
                                <td><b>Group Members:</b></td>
                                <td colspan="4"> ${getMembers()}</td>
                            </tr>
                            <tr>
                                <td><b>Group Status</b></td>
                                <td colspan="4">${d.status}</td>

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
                                <td>${d.approval_date}</td>
                            </tr>
                        </table>
    `;

                return output;
            }
            var table =  $('#cbtn-selectors1').DataTable({
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
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                ajax: '{!! route('groups.awaiting_approval_data') !!}',
                columns: [
                    {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false},
                    {
                        className : 'details-control',
                        orderable : false,
                        data : null,
                        defaultContent : ''
                    },
                    {data: 'name', name: 'name'},
                    {data: 'unique_id', name: 'unique_id'},
                    {data: 'leader', name: 'leader', },
                    {data: 'phone', name: 'phone'},
                    // {data: 'customers_count', name: 'customers_count'},
                    {data: 'members_count', name: 'members_count'},
                    // {data: 'created_by', name: 'created_by'},
                    {data: 'approved', name: 'approved'},
                    // {data: 'created_at', name: 'created_at'},
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],

            });
            // Add event listener for opening and closing details
            $('#cbtn-selectors1 tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row( tr );


                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );

            // Handle click on "Expand All" button
            $('#btn-show-all-children').on('click', function(){
                // Enumerate all rows
                table.rows().every(function(){
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
                table.rows().every(function(){
                    // If row has details expanded
                    if(this.child.isShown()){
                        // Collapse row details
                        this.child.hide();
                        $(this.node()).removeClass('shown');
                    }
                });
            });
            $('.approve-btn').keypress(function (e) {
                if (e.which == 13) { // Checks for the enter key
                    e.preventDefault(); // Stops IE from triggering the button to be clicked
                }
            });


            $(".approve-btn").on('click', function (e) {

                if ($('input[name^=id]:checked').length <= 0) {
                    swal(
                        "Warning",
                        "You Must check the group you want to approve",
                        "warning"
                    );
                    return false
                }


                $('#form').on('submit', function (e) {
                    var form = this;
                    e.preventDefault();

                    swal({
                            title: "Please confirm",
                            text: "Do you want to approve the selected groups?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: '#96d25f',
                            confirmButtonText: 'Yes, approve',
                            cancelButtonText: "Cancel",
                            closeOnConfirm: true,
                            closeOnCancel: true
                        },
                        function () {
                            form.submit();
                        });
                });

            });

            $('body').on('click', 'a.approve', function (e) {
                var form = $(this).attr("href");
                e.preventDefault();
                // console.log(form);

                swal({
                        title: "Please confirm",
                        text: "Do you want to approve this group?",
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
            });

            $('body').on('click', 'a.ldelete', function (e) {
                var form = $(this).attr("href");
                e.preventDefault();
                // console.log(form);

                swal({
                        title: "Please confirm",
                        text: "Do you want to delete this group?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#96d25f',
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function () {
                        window.location = form;
                    });
            });
        });
        {{--$(document).ready(function() {--}}

        {{--    $('#cbtn-selectors1').DataTable({--}}
        {{--        dom: 'Bfrtip',--}}
        {{--        "processing": true,--}}
        {{--        "serverSide": true,--}}
        {{--        buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {--}}
        {{--            extend: 'excelHtml5',--}}
        {{--            exportOptions: {columns: ':visible'}--}}
        {{--        }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis', 'pageLength'],--}}


        {{--        ajax: '{!! route('groups.awaiting_approval_data') !!}',--}}
        {{--        columns: [--}}
        {{--            /* {data: 'checkbox', name: 'checkbox'},*/--}}
        {{--            {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false},--}}

        {{--            {data: 'owner', name: 'customers.lname'},--}}
        {{--            {data: 'product_name', name: 'products.product_name'},--}}
        {{--            {data: 'installments', name: 'products.installments'},--}}
        {{--            {data: 'interest', name: 'products.interest'},--}}
        {{--            {data: 'loan_amount', name: 'loan_amount'},--}}
        {{--            {data: 'phone', name: 'customers.phone'},--}}
        {{--            {data: 'date_created', name: 'date_created'},--}}
        {{--            /*{data: 'approved', name: 'approved'},*/--}}
        {{--            // {data: 'approved_date', name: 'approved_date'},--}}
        {{--            /*{data: 'disbursed', name: 'disbursed'},--}}
        {{--            {data: 'disbursement_date', name: 'disbursement_date'},--}}
        {{--            {data: 'end_date', name: 'end_date'},--}}
        {{--            {data: 'settled', name: 'settled'},*/--}}
        {{--            {data: 'action', name: 'action', orderable: false, searchable: false}--}}

        {{--        ],--}}
        {{--    });--}}


        {{--    /*************************multi approval*********************/--}}
        {{--    $('.approve-btn').keypress(function (e) {--}}
        {{--        if (e.which == 13) { // Checks for the enter key--}}
        {{--            e.preventDefault(); // Stops IE from triggering the button to be clicked--}}
        {{--        }--}}
        {{--    });--}}


        {{--    $(".approve-btn").on('click', function (e) {--}}

        {{--        if ($('input[name^=id]:checked').length <= 0) {--}}
        {{--            swal(--}}
        {{--                "Warning",--}}
        {{--                "You Must check the loan you want to approve",--}}
        {{--                "warning"--}}
        {{--            );--}}
        {{--            return false--}}
        {{--        }--}}


        {{--        $('#form').on('submit', function (e) {--}}
        {{--            var form = this;--}}
        {{--            e.preventDefault();--}}

        {{--            swal({--}}
        {{--                    title: "Please confirm",--}}
        {{--                    text: "Do you want to approve the selected loans?",--}}
        {{--                    type: "warning",--}}
        {{--                    showCancelButton: true,--}}
        {{--                    confirmButtonColor: '#96d25f',--}}
        {{--                    confirmButtonText: 'Yes, approve',--}}
        {{--                    cancelButtonText: "Cancel",--}}
        {{--                    closeOnConfirm: true,--}}
        {{--                    closeOnCancel: true--}}
        {{--                },--}}
        {{--                function () {--}}
        {{--                    form.submit();--}}
        {{--                });--}}
        {{--        });--}}

        {{--    });--}}

        {{--    $('body').on('click', 'a.approve', function (e) {--}}
        {{--        var form = $(this).attr("href");--}}
        {{--        e.preventDefault();--}}
        {{--       // console.log(form);--}}

        {{--        swal({--}}
        {{--                title: "Please confirm",--}}
        {{--                text: "Do you want to approve this loan?",--}}
        {{--                type: "warning",--}}
        {{--                showCancelButton: true,--}}
        {{--                confirmButtonColor: '#96d25f',--}}
        {{--                confirmButtonText: 'Yes, approve',--}}
        {{--                cancelButtonText: "Cancel",--}}
        {{--                closeOnConfirm: true,--}}
        {{--                closeOnCancel: true--}}
        {{--            },--}}
        {{--            function () {--}}
        {{--                window.location = form;--}}
        {{--            });--}}
        {{--    });--}}

        {{--    $('body').on('click', 'a.ldelete', function (e) {--}}
        {{--        var form = $(this).attr("href");--}}
        {{--        e.preventDefault();--}}
        {{--        // console.log(form);--}}

        {{--        swal({--}}
        {{--                title: "Please confirm",--}}
        {{--                text: "Do you want to delete this loan?",--}}
        {{--                type: "warning",--}}
        {{--                showCancelButton: true,--}}
        {{--                confirmButtonColor: '#96d25f',--}}
        {{--                confirmButtonText: 'Yes, delete',--}}
        {{--                cancelButtonText: "Cancel",--}}
        {{--                closeOnConfirm: true,--}}
        {{--                closeOnCancel: true--}}
        {{--            },--}}
        {{--            function () {--}}
        {{--                window.location = form;--}}
        {{--            });--}}
        {{--    });--}}

        {{--})--}}


    </script>


@stop
