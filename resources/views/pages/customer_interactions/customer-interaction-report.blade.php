@extends("layouts.master")
@section("css")
@stop
<link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
<style>
    .datepicker{ z-index:99999 !important;
</style>

@section("content")
    <div class="row justify-content-center">
        <div class="col-xl-3 col-md-6">
            @include('layouts.alert')
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-xl-4 col-md-6">
            <div class="card  ">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="m-b-5">Total Interactions</p>
                            <h4 class="m-b-0">{{$interactions_count}}</h4>
                        </div>
                        <div class="col col-auto text-right">
                            <i class="feather icon-bar-chart f-50 text-c-pink"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card  ">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="m-b-5">Total Interactions (MTD)</p>
                            <h4 class="m-b-0">{{$interactions_count_mtd}}</h4>
                        </div>
                        <div class="col col-auto text-right">
                            <i class="feather icon-bar-chart f-50 text-c-green"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header"></div>
                <div class="card-block">
                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-3">
                            <label for="branch">Select Branch</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                    <option value="all" > All </option>
                                    @foreach($branches as $branch)
                                        <option value="{{$branch->id}}" > {{$branch->bname}} </option>
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
                            <label for="branch">Select Loan Officer</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-user-alt-3"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('credit_officer') ? ' is-invalid' : '' }}" name="credit_officer" required>
                                    <option value="all">All</option>
                                    @foreach($lfs as $lf)
                                        <option  value="{{$lf->id}}" > {{$lf->name . ' - ' . $lf->branch}} </option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('credit_officer'))
                                <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('credit_officer') }}</strong>
                            </span>
                            @endif
                        </div>

                        <div class="col-md-3" hidden>
                            <label for="branch">Select Interaction Type</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('interaction_type_id') ? ' is-invalid' : '' }}" name="interaction_type_id" required>
                                    <option value="all">All</option>
                                    @foreach($interaction_types as $interaction_type)
                                        <option  value="{{$interaction_type->id}}" > {{$interaction_type->name}} </option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('interaction_type_id'))
                                <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('interaction_type_id') }}</strong>
                            </span>
                            @endif
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
                                <th>Branch</th>
                                <th>Customer Name</th>
                                <th>Phone Number</th>
                                <th>Created By</th>
                                <th>Type</th>
                                <th>Remark</th>
                                <th>Next Scheduled Interaction</th>
                                <th>Created At</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Customer Name</th>
                                <th>Phone Number</th>
                                <th>Created By</th>
                                <th>Type</th>
                                <th>Remark</th>
                                <th>Next Scheduled Interaction</th>
                                <th>Created At</th>
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
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
        $(document).ready(function () {

            const oTable = $('#cbtn-selectors1').DataTable({
                "processing": true,
                "serverSide": true,
                dom: 'Bfrtip',
                buttons: [/*{extend: 'copyHtml5', exportOptions: {columns: ':visible'}},*/ {
                    extend: 'excelHtml5',
                    exportOptions: {columns: ':visible'}
                }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, /*'colvis',*/'pageLength'],
                "lengthMenu": [[15, 30, -1], [15, 30, "All"]],
                ajax:{
                    url:'{!! route('customer-interactions.customer_interactions_report_data') !!}',
                    data: function (d) {
                        d.branch = $('select[name=branch_id]').val();
                        d.lf = $('select[name=credit_officer]').val();
                    }
                } ,
                order: [0, 'desc'],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'customer_branch', name: 'customer_branch', orderable:false, searchable:false},
                    {data: 'customer.fullName', name: 'customer.lname'},
                    {data: 'customer.phone', name: 'customer.phone'},
                    {data: 'user_name', name: 'user_name', orderable:false, searchable:false},
                    {data: 'interaction_type.name', name: 'interaction_type.name'},
                    {data: 'remark', name: 'remark'},
                    {data: 'next_scheduled_interaction', name: 'next_scheduled_interaction'},
                    {data: 'created_at', name: 'created_at'},
                ],
            });

            $('#search').on('submit', function(e) {
                e.preventDefault();
                oTable.draw();
            });

            $('#datetimepicker2').datetimepicker({
                format:'Y-m-d',
                // maxDate: maxDate,
                // minDate:new Date()
            });
        })
        $("body").delegate("#datetimepicker2", "focusin", function () {
            $(this).datepicker();
        });

    </script>
@stop
