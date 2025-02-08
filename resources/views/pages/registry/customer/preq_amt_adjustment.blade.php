@extends("layouts.master")
@section("css")
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section("content")

<div class="row">
    <div class="col-sm-12">
        @include('layouts.alert')
        <div class="row">
        </div>
        <div class="card">
            <div class="card-header">
            </div>
            <div class="card-block">
                    <div class="dt-responsive table-responsive">
                    <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Branch Name</th>
                                <th>Customer Name</th>
                                <th>Mobile Number</th>
                                <th>Initial Amount</th>
                                <th>Proposed Amount</th>
                                <th>Status</th>
                                <th>Initiated By</th>
                                <th>Approved By</th>
                                <th>Date Created</th>
                                <th>Date Approved</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Branch Name</th>
                                <th>Customer Name</th>
                                <th>Mobile Number</th>
                                <th>Initial Amount</th>
                                <th>Proposed Amount</th>
                                <th>Status</th>
                                <th>Initiated By</th>
                                <th>Approved By</th>
                                <th>Date Created</th>
                                <th>Date Approved</th>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    (function() {

   var oTable = $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{extend: 'copyHtml5'}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'},
            },
                {
                    extend: 'pdfHtml5', /*exportOptions: {columns: ':visible'}*/
                    orientation: 'landscape',
                    pageSize: 'TABLOID'
                },
                'colvis','pageLength'],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        // <th>Initial Amount</th>
        // <th>Proposed Amount</th>
        // <th>Status</th>
        // <th>Date Created</th>
        // <th>Date Approved</th>
        // <th>Action</th>

            ajax:{
                    url:'{!! route('preq_amt_adjustment_data') !!}',
                } ,
            columns: [
                {data: 'id'},
                {data: 'branch'},
                {data: 'customer'},
                {data: 'phone', name: 'phone'},
                {data: 'initial_amount', name: 'initial_amount'},
                {data: 'proposed_amount', name: 'proposed_amount'},
                {data: 'status', name: 'status'},
                {data: 'initiated_by', name: 'initiated_by'},
                {data: 'approved_by', name: 'approved_by'},
                {data: 'created_at', name: 'created_at'},
                {data: 'approved_at', name: 'approved_at'},
                { data: 'action', orderable: false, searchable: false }
            ],
        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });

})();

</script>


@stop
