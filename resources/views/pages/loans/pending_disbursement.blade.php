@extends("layouts.master")

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <form action="{{route('loans.post_disburse_multiple')}}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header">
                    </div>
                    <div class="card-block">
                        <div class="dt-responsive table-responsive">
                            <table id="cbtn-selectors1"
                                   class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Owner</th>
                                    <th>Phone</th>
                                    <th>Product</th>
                                    <th>Installments</th>
                                    <th>% Interest</th>
                                    <th>Amount</th>
                                    <th>Date Created</th>
                                    <th>Action</th>
                                </tr>
                                </thead>

                                <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Owner</th>
                                    <th>Phone</th>
                                    <th>Product</th>
                                    <th>Installments</th>
                                    <th>% Interest</th>
                                    <th>Amount</th>
                                    <th>Date Created</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('disburse_loans_pending.data') !!}',
            columns: [

                {data: 'id', name: 'id'},
                {data: 'owner', name: 'owner'},
                {data: 'phone', name: 'phone'},
                {data: 'product', name: 'product'},
                {data: 'installments', name: 'installments'},
                {data: 'interest', name: 'interest'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'created_at', name: 'created_at'},
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ],
        });
    </script>


@stop
