@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-header">
                    <div class="row">
                    <div class="col-md-3">
                        <a class="btn btn-primary" href="{{route('guarantors.create')}}">Add New</a>
                        <a href="{{route('guarantors_sms')}}" class="btn btn-primary">Send All</a>

                    </div>
                    </div>

                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>DOB</th>
                                <th>NID</th>
                                <th>Marital Status</th>
                                <th>Location</th>
                                <th>Business</th>
                                <th>Action</th>

                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>DOB</th>
                                <th>NID</th>
                                <th>Marital Status</th>
                                <th>Location</th>
                                <th>Business</th>
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
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('guarantors.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'gname', name: 'gname'},
                {data: 'gphone', name: 'gphone'},
                {data: 'gdob', name: 'gdob'},
                {data: 'gid', name: 'gid'},
                {data: 'marital_status', name: 'marital_status'},
                {data: 'location', name: 'location'},
                {data: 'business', name: 'business'},
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ],
        });
    </script>


@stop
