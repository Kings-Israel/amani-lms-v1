@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="row justify-content-end">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col">
                                    <p class="m-b-5">Total Customer SMS</p>
                                    <h4 class="m-b-0">{{number_format($count)}}</h4>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-message-square f-50 text-c-pink"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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


            ajax: '{!! route('admin.customer_sms.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'phone', name: 'phone'},
                {data: 'sms', name: 'sms'},
                {data: 'created_at', name: 'created_at'},
            ],
        });
    </script>


@stop
