@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
                @csrf
                <div class="card">
                    <div class="card-block">
                        <div class="dt-responsive table-responsive">
                            <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Credit Officer</th>
                                    <th>Phone Number</th>
                                    <th>Branch</th>
                                    <th>Customers</th>
                                    <th>Action</th>
                                </tr>
                                </thead>

                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th>Credit Officer</th>
                                    <th>Phone Number</th>
                                    <th>Branch</th>
                                    <th>Customers</th>
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
    var oTable = $('#cbtn-selectors1').DataTable({
        dom: 'Bfrtip',
        "processing": true,
        "serverSide": true,
        buttons: ['pageLength'],
        "lengthMenu": [[50, 100, -1], ["50", "100","All"]],
        ajax:{
            url:'{!! route('changeCOData') !!}',
        } ,
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'phone', name: 'phone'},
            {data: 'branch', name: 'branch'},
            {data: 'count', name: 'count'},
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
    });
</script>
@stop
