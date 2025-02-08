@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-4">
                            <button class="btn btn-primary mb-1">Refresh</button>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Last Seen</th>

                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Last Seen</th>
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
        var oTable = $('#cbtn-selectors1').DataTable({
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',
            buttons: ['pageLength'],
            "lengthMenu": [[25, 50, -1], [25, 50, "All"]],

            ajax: '{!! route('admin.view_users_last_seen_data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'online_status', name: 'online_status'},
                {data: 'last_seen_diff', name: 'last_seen_diff'},
            ],
            order:[3, 'DESC']
        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });
    </script>


@stop

