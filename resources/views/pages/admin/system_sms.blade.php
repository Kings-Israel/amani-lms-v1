@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Phone</th>
                                <th>SMS</th>
                                <th>Date Sent</th>

                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Phone</th>
                                <th>SMS</th>
                                <th>Date Sent</th>

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
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis', 'pageLength'],


            ajax: '{!! route('admin.system_sms.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'phone', name: 'phone'},
                {data: 'sms', name: 'sms'},
                {data: 'created_at', name: 'created_at'},
            ],
        });
    </script>


@stop

