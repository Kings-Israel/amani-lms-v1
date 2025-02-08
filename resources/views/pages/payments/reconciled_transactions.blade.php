{{-- @extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-header">
                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>


                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Reconciled By</th>
                                <th>Transaction No</th>
                                <th>Channel</th>
                                <th>Phone</th>
                                <th>Customer</th>

                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Reconciled By</th>
                                <th>Transaction No</th>
                                <th>Channel</th>
                                <th>Phone</th>
                                <th>Customer</th>

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
            "processing": true,
            "serverSide": true,
            "ordering": false,
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('reconciled_transactions.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'amount', name: 'amount'},
                {data: 'date_paid', name: 'date_paid'},
                {data: 'name', name: 'users.name'},
                {data: 'transaction_id', name: 'transaction_id'},
                {data: 'channel', name: 'channel'},
                {data: 'phone_number', name: 'phone_number'},
                {data: 'fname', name: 'fname'},

            ],
        });
    </script>


@stop --}}

@extends("layouts.master")
@section("css")
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
@stop

@section("content")

<div class="row">
    <div class="col-sm-12">
        @include('layouts.alert')
        <div class="card">
            <div class="card-header">

            </div>

            <div class="card-block">
                <form id="search" class="form-inline row" method="post" action="">
                    @csrf

                    <div class="col-md-2">
                        <label for="date">Date</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i
                                class="icofont icofont-bank-alt"></i></span>
                                <input type="text" id="start_date" name="start_date" autocomplete="off" class="form-control datetimepicker" placeholder="YYYY-MM-DD">
                            </div>
                    </div>

                    <div class="col-md-2">
                        <label for="date">Date</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i
                                class="icofont icofont-bank-alt"></i></span>
                                <input type="text" id="end_date" name="end_date" autocomplete="off" class="form-control datetimepicker" placeholder="YYYY-MM-DD">
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
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Reconciled By</th>
                                <th>Transaction No</th>
                                <th>Channel</th>
                                <th>Phone</th>
                                <th>Customer</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Reconciled By</th>
                                <th>Transaction No</th>
                                <th>Channel</th>
                                <th>Phone</th>
                                <th>Customer</th>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.datetimepicker').datetimepicker({
            format: 'Y-m-d',
            timepicker: false
        });

        var oTable = $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            processing: true,
            serverSide: true,
            ordering: false,
            buttons: [
                {extend: 'copyHtml5'},
                {extend: 'excelHtml5', exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6, 7]}},
                {extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'TABLOID'},
                'colvis', 'pageLength'
            ],
            ajax: {
                url: '{!! route('reconciled_transactions.data') !!}',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.reconciled_by = $('select[name="reconciled_by"]').val();
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'amount', name: 'amount'},
                {data: 'date_paid', name: 'date_paid'},
                {data: 'name', name: 'users.name'},
                {data: 'transaction_id', name: 'transaction_id'},
                {data: 'channel', name: 'channel'},
                {data: 'phone_number', name: 'phone_number'},
                {data: 'fname', name: 'fname'}
            ]
        });

        $('#search').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });
    });
</script>
@stop
