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
                            <a class="btn btn-primary" href="{{route('employers.create')}}">Add New</a>
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
                                <th>Email</th>
                                <th>Manager</th>
                                <th>Accountant</th>
                                <th>Loan</th>
                                <th>Accountant</th>
                                <th>Accountant</th>

                                <th>Action</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Location</th>
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
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('employers.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'ename', name: 'ename'},
                {data: 'ephone', name: 'ephone'},
                {data: 'eemail', name: 'eemail'},
                {data: 'location', name: 'location'},
                /* {data: 'status', name: 'status'},*/
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ],
        });
    </script>


@stop
