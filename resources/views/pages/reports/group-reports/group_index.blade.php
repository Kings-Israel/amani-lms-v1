@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
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
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',
            buttons: [/*{extend: 'copyHtml5', exportOptions: {columns: ':visible'}},*/ {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, /*'colvis',*/'pageLength'],
            "lengthMenu": [[15, 30, -1], [15, 30, "All"]],
            ajax: '{!! route('group_reports_data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'rname', name: 'rname'},
                {data: 'description', name: 'description'},
                {data: 'route', name: 'route'},
            ],
        });
    </script>


@stop
