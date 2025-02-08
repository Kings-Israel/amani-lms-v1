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
                            <label for="branch">Select Branch</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select
                                    class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}"
                                    name="branch_id" required>
                                    <option value="all"> All</option>
                                    @foreach($branches as $brach)
                                        <option value="{{$brach->id}}"> {{$brach->bname}} </option>
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
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select
                                    class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                    name="name" required>
                                    <option value="all">All</option>
                                    @foreach($lfs as $lf)
                                        <option value="{{$lf->id}}"> {{$lf->name}} </option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('name'))
                                <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label for="status">Status</label>

                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select
                                    class="js-example-basic-single form-control{{ $errors->has('status') ? ' is-invalid' : '' }}"
                                    name="status">
                                    <option value="all"> All </option>
                                    <option value="due"> Due </option>
                                    <option value="overdue"> OverDue</option>
                                </select>

                            </div>
                            @if ($errors->has('status'))
                                <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('status') }}</strong>
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
                            <button class="btn fil btn-primary">Filter</button>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Category</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                {{-- <th>Branch</th> --}}
                                <th>Field Agent</th>
                                <th>Remark</th>
                                {{-- <th>Created On</th> --}}
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Category</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                {{-- <th>Branch</th> --}}
                                <th>Field Agent</th>
                                <th>Remark</th>
                                {{-- <th>Created On</th> --}}
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create a New Interaction Record </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('customer-interactions.store')}}" method="post">
                        @csrf
                        <input type="hidden" id="customer_id" name="customer_id">
                        <input type="hidden" id="model_id" name="model_id">
                        <input type="hidden" id="preinteraction_id" name="pre_interaction_id">
                        <div class="form-group row">
                            @if($type == "others")
                            <div class="col-md-4">
                                <label for="interaction_category_id">Interaction Category</label>
                                <select name='interaction_category_id' id='interaction_category_id' class="form-control" required>
                                    <option value="" disabled>Kindly specify the type of interaction</option>
                                    @foreach($interaction_categories as $interaction_category)
                                        <option
                                            value="{{$interaction_category->id}}" {{(old('interaction_type_id') == $interaction_category->id ) ? 'selected' : ''}}>{{$interaction_category->name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('interaction_category_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interaction_category_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                            @else
                                <div class="col-md-4">
                                    <label for="interaction_category_id">Interaction Category</label>

                                    <input id="interaction_category_id" type="text" readonly
                                           value="Arrear Collection"
                                           name="interaction_category_id"
                                           class="form-control {{ $errors->has('interaction_category_id') ? ' is-invalid' : '' }}">

                                @if ($errors->has('interaction_category_id'))
                                        <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interaction_category_id') }}</strong>
                                    </span>
                                    @endif
                                </div>

                            @endif

                            <div class="col-md-4">
                                <label for="interaction_type_id">Interaction Type</label>
                                <select name='interaction_type_id' id='interaction_type_id' class="form-control" required>
                                    <option value="" disabled>Kindly specify the type of interaction</option>
                                    @foreach($interaction_types as $interaction_type)
                                        <option
                                            value="{{$interaction_type->id}}" {{(old('interaction_type_id') == $interaction_type->id ) ? 'selected' : ''}}>{{$interaction_type->name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('interaction_type_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interaction_type_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="datetimepicker2">Next Scheduled Interaction</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">
                                        <i class="icofont icofont-ui-calendar"></i>
                                    </span>
                                    <input id="datetimepicker2" type="date"
                                           value="{{ old('next_scheduled_interaction') }}"
                                           min="{{now()->format('Y-m-d')}}" autocomplete="off"
                                           name="next_scheduled_interaction"
                                           class="form-control {{ $errors->has('next_scheduled_interaction') ? ' is-invalid' : '' }}">
                                </div>
                                @if ($errors->has('next_scheduled_visit'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('next_scheduled_visit') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <label for="remark">Remark</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-ui-message"></i></span>
                                    <textarea type="text" cols="10" rows="10" id="remark" name="remark"
                                              class="form-control {{ $errors->has('remark') ? ' is-invalid' : '' }}"
                                              required>{{ old('remark') }}</textarea>
                                </div>
                                @if ($errors->has('remark'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('remark') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Save</button>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@stop


@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
        jQuery(document).ready(function () {
            "use strict";
            jQuery('#datetimepicker1').datetimepicker({
                format:'Y-m-d'
            });
        })

        $(document).on('click', '.interact', function () {
            $("#customer_id").val($(this).attr("data-customer_id"));
            $("#datetimepicker2").val($(this).attr("data-date"));
            $("#interaction_category_id").val($(this).attr("data-category"));
            $("#remark").text($(this).attr("data-remark"));
            $("#model_id").val($(this).attr("data-model"));
            $("#preinteraction_id").val($(this).attr("data-pre_id"));
        })

        $(document).ready(function () {
            var oTable = $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                "processing": true,
                "serverSide": true,
                buttons: [{extend: 'copyHtml5'},
                    {
                        extend: 'excelHtml5',
                        exportOptions: {columns: ':visible'},
                    },
                    {
                        extend: 'pdfHtml5',
                        orientation: 'landscape',
                        pageSize: 'TABLOID'
                    },
                    'colvis', 'pageLength'],
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],

                ajax: {
                    url: '{!! route('pre_interactions_data') !!}',
                    data: function (d) {
                        d.id = '{{$id}}';
                        d.branch = $('select[name=branch_id]').val();
                        d.lf = $('select[name=name]').val();
                        d.category = $('select[name=category]').val();
                        d.status = $('select[name=status]').val();
                        d.date = $('input[name=date]').val();
                    }
                },
                order: [0, 'desc'],

                columns: [
                    {data: 'id'},
                    {data: 'lname', name: 'customers.lname'},
                    {data: 'phone', name: 'customers.phone'},
                    {data: 'name', name: 'customer_interaction_categories.name'},
                    {data: 'due_date', name: 'due_date'},
                    {data: 'amount'},
                    // {data: 'branch', name: 'branch',  orderable: false, searchable: false},
                    {data: 'LO', name: 'LO',  orderable: false, searchable: false},
                    {data: 'system_remark'},
                    // {data: 'created_at'},
                    {data: 'action', orderable: false, searchable: false}
                ],
            });

            $('#search').on('submit', function (e) {
                oTable.draw();
                e.preventDefault();
            });

        })


    </script>

@stop
