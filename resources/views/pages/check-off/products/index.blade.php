@extends("layouts.master")
@section("css")
@stop
@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-header row">
                    <div class="col-md-3">
                        <a class="btn btn-primary" href="{{route('check-off-products.create')}}">Add New Product</a>
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
                                <th>Interest</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Interest</th>
                                <th>Period</th>
                                <th>Status</th>
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
            dom: 'Bfrtip',
            buttons: [
                {extend: 'copyHtml5', exportOptions: {columns: ':visible'}},
                {extend: 'excelHtml5', exportOptions: {columns: ':visible'}},
                {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}},
                'colvis', 'pageLength'],
            "processing": true,
            "serverSide": true,
            ajax: '{!! route('check-off.products.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'interest', name: 'interest'},
                {data: 'period', name: 'period'},
                {data: 'status', name: 'status'},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
        });
    </script>
@stop
