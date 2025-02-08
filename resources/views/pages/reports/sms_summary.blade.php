@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                    <form id="search" class="form-inline row mt-3 justify-content-center" method="post" action="">
                        @csrf
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                <input type="text" value="" id="start_date" autocomplete="off" name="start_date" value="" class="datepicker form-control" required>
                            </div>
                            @if ($errors->has('start_date'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('start_date') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="col-md-3">
                            <label>End Date</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                                <input value="" type="text" id="end_date" autocomplete="off" name="end_date" value="" class="datepicker form-control" required>
                            </div>
                            @if ($errors->has('end_date'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('end_date') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary bt-sm">Filter</button>
                        </div>
                    </form>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>SMS</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>SMS</th>
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
    <script type="text/javascript" src="{{asset('bower_components/sweetalert/js/sweetalert.min.js')}}"></script>
    <script>
        $(function() {
            $('#start_date').datetimepicker({
                maxDate: new Date(),
                format:'Y-m-d'
            });
            $('#end_date').datetimepicker({
                maxDate: new Date(),
                format:'Y-m-d'
            });
        });
    </script>
    <script>
        var oTable = $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis'],

            ajax: {
                url: '{!! route('sms_summary.data') !!}',
                data: function (d) {
                    d.start_date = $('input[name=start_date]').val();
                    d.end_date = $('input[name=end_date]').val();
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'bname', name: 'bname'},
                {data: 'sms', name: 'sms'},
            ],
        });
        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });
    </script>


@stop
