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
                        <a class="btn btn-primary" href="{{route('referee.create')}}">Add New</a>
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
                                <th>NID</th>
                                <th>Customer Name</th>
                                <th>Action</th>

                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>NID</th>
                                <th>Customer Name</th>
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
            buttons: [
                {extend: 'copyHtml5', exportOptions: {columns: ':visible'}},
                {extend: 'excelHtml5', exportOptions: {columns: ':visible'}},
                {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}},
                'colvis', 'pageLength'
            ],
            ajax: '{!! route('referees.data') !!}',
            columns: [
                {
                    data: null,
                    name: 'serial',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {data: 'full_name', name: 'full_name'},
                {data: 'phone_number', name: 'phone_number'},
                {data: 'id_number', name: 'id_number'},
                {data: 'customer_name', name: 'customer_name'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[1, 'desc']] 
        });


    </script>


@stop
