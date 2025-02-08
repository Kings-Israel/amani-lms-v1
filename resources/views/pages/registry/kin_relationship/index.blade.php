@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">


            <div class="card">
                <div class="card-header">
                    {{--<h5>Add new Branch</h5>--}}

                    <div class="col-md-3 row">
                        <a class="btn btn-primary" href="{{route('kin.create')}}">Add New</a>
                    </div>

                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="branches"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>

                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
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
        $('#branches').DataTable({
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('kin.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'rname', name: 'rname'},
                /*{data: 'status', name: 'status'},*/
                {data: 'action', name: 'action', orderable: false, searchable: false}

            ],
        });
    </script>


@stop
