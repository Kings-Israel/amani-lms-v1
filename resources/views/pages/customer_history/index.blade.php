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
                                Add New Item
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
                                <th>Credit Officer</th>
                                <th>Remark</th>
                                <th>Date Visited</th>
                                <th>Next Scheduled Visit</th>
                                <th>Created At</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Credit Officer</th>
                                <th>Remark</th>
                                <th>Date Visited</th>
                                <th>Next Scheduled Visit</th>
                                <th>Created At</th>
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
                    <form action="{{route('customer-history-thread.store')}}" method="post">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{$customer->id}}">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="datetimepicker1">Date Visited</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">
                                        <i class="icofont icofont-ui-calendar"></i>
                                    </span>
                                    <input id="datetimepicker1" type="date" max="{{now()->format('Y-m-d')}}" autocomplete="off" name="date_visited" value="" class="form-control {{ $errors->has('date_visited') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('date_visited'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('date_visited') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="datetimepicker2">Next Scheduled Visit</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">
                                        <i class="icofont icofont-ui-calendar"></i>
                                    </span>
                                    <input id="datetimepicker2" type="date" min="{{now()->format('Y-m-d')}}" autocomplete="off" name="next_scheduled_visit" value="" class="form-control {{ $errors->has('next_scheduled_visit') ? ' is-invalid' : '' }}" >
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
                                    <textarea type="text" cols="10" rows="10" id="remark" name="remark"  class="form-control {{ $errors->has('next_scheduled_visit') ? ' is-invalid' : '' }}" required></textarea>
                                    @if ($errors->has('remark'))
                                        <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('remark') }}</strong>
                                    </span>
                                    @endif
                                </div>
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
            ajax: '{!! route('list_customer_thread_data', encrypt($customer->id)) !!}',
            order: [0, 'desc'],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'users.name'},
                {data: 'remark', name: 'remark'},
                {data: 'date_visited', name: 'date_visited'},
                {data: 'next_scheduled_visit', name: 'next_scheduled_visit'},
                {data: 'created_at', name: 'created_at'},
            ],
        });
    </script>

    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#datetimepicker1').datetimepicker({
                format:'Y-m-d',
                // maxDate: new Date(),
                // minDate:minDate
            });
            $('#datetimepicker2').datetimepicker({
                format:'Y-m-d',
                // maxDate: maxDate,
                // minDate:new Date()
            });
        })
        $("body").delegate("#datetimepicker1", "focusin", function () {
            $(this).datepicker();
        });
        $("body").delegate("#datetimepicker2", "focusin", function () {
            $(this).datepicker();
        });

    </script>
@stop
