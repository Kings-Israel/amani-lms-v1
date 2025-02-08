@extends("layouts.master")
@section("css")
@stop
<link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
<style>
    .datepicker{ z-index:99999 !important; }
</style>

@section("content")
    <div class="row justify-content-center">
        <div class="col-xl-3 col-md-6">
            @include('layouts.alert')
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                                Add New Interaction
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Created By</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Remark</th>
                                <th>Status</th>
                                <th>Next Scheduled Interaction</th>
                                <th>Created At</th>
                                <th>Action</th>

                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Created By</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Remark</th>
                                <th>Status</th>
                                <th>Next Scheduled Interaction</th>
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

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create a New Record Under {{$customer->full_name}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('customer-interactions.store')}}" method="post">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{$customer->id}}">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="interaction_category_id">Interaction Category</label>
                                <select name='interaction_category_id' id='interaction_type_id' class="form-control">
                                    <option value="" disabled>Kindly specify the category of interaction</option>
                                    @foreach($interaction_categories as $interaction_category)
                                        <option value="{{$interaction_category->id}}" {{(old('interaction_category_id') == $interaction_category->id ) ? 'selected' : ''}}>{{$interaction_category->name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('interaction_category_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interaction_category_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="interaction_type_id">Interaction Type</label>
                                <select name='interaction_type_id' id='interaction_type_id' class="form-control">
                                    <option value="" disabled>Kindly specify the type of interaction</option>
                                    @foreach($interaction_types as $interaction_type)
                                        <option value="{{$interaction_type->id}}" {{(old('interaction_type_id') == $interaction_type->id ) ? 'selected' : ''}}>{{$interaction_type->name}}</option>
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
                                    <input id="datetimepicker2" type="date" value="{{ old('next_scheduled_interaction') }}" min="{{now()->format('Y-m-d')}}" autocomplete="off" name="next_scheduled_interaction" class="form-control {{ $errors->has('next_scheduled_interaction') ? ' is-invalid' : '' }}" >
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
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-message"></i></span>
                                    <textarea type="text" cols="10" rows="10" id="remark" name="remark"  class="form-control {{ $errors->has('remark') ? ' is-invalid' : '' }}" required>{{ old('remark') }}</textarea>
                                </div>
                                @if ($errors->has('remark'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('remark') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Save </button>

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
    <script>
        $('#cbtn-selectors1').DataTable({
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',
            buttons: [/*{extend: 'copyHtml5', exportOptions: {columns: ':visible'}},*/ {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, /*'colvis',*/'pageLength'],
            "lengthMenu": [[15, 30, -1], [15, 30, "All"]],
            ajax: '{!! route('customer-interactions.customer_interactions_data', encrypt($customer->id)) !!}',
            order: [0, 'desc'],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'user.name', name: 'user.name'},
                {data: 'interaction_type.name', name: 'interaction_type.name'},
                {data: 'interaction_category.name', name: 'interaction_category.name'},

                {data: 'remark', name: 'remark'},
                {data: 'status', orderable: false, searchable: false},


                {data: 'next_scheduled_interaction', name: 'next_scheduled_interaction'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', orderable: false, searchable: false},

            ],
        });
    </script>

    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
        $(document).ready(function () {
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
