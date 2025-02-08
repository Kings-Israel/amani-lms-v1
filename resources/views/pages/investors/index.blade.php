@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-header row">
                    <div class="col-md-3">
                        <a class="btn btn-primary" href="{{route('investors.create')}}">Add New</a>
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
                                <th>Email</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>Investment</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>Investment</th>

                                <th>Action</th>
                                {{-- <th>Salary</th>--}}
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
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis', 'pageLength'],
            "processing": true,
            "serverSide": true,


            ajax: '{!! route('investors.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'email', name: 'email'},
                {data: 'branch', name: 'branch'},
                {data: 'status', name: 'status'},
                {data: 'investment', name: 'investment'},
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ],
        });
    </script>


@stop
