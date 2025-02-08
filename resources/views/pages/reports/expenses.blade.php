@extends("layouts.master")
@section("css")
@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">

                    {{--<div class="row col-md-3">
                        <a class="btn btn-primary" href="{{route('products.create')}}">Add New</a>
                    </div>--}}

                </div>
                <div class="card-block">
                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-3">
                            <label for="branch">Select Branch</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                    <option @if($check_role) @else disabled @endif value="all" > All </option>
                                    @foreach($branches as $brach)
                                        <option value="{{$brach->id}}" > {{$brach->bname}} </option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('branch_id'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch_id') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                <input type="text" id="start_date" autocomplete="off" name="start_date" value="" class="datepicker form-control" required>
                            </div>
                            @if ($errors->has('start_date'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('start_date') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="col-md-3">
                            <label>End Date</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                                <input type="text" id="end_date" autocomplete="off" name="end_date" value="" class="datepicker form-control" required>
                            </div>
                            @if ($errors->has('end_date'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('end_date') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Expense Type</th>
                                <th>Paid By</th>
                                <th>Branch</th>
                                <th>Description</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date Payed</th>
                                <th>Expense Type</th>
                                <th>Paid By</th>
                                <th>Branch</th>
                                <th>Description</th>



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
        $(function() {
            $('#start_date').datetimepicker({
                maxDate: new Date(),
                format:'Y-m-d'
            });
            $('#end_date').datetimepicker({
                maxDate: new Date(),
                format:'Y-m-d'
            });
        });
    </script>
    <script>

            var oTable = $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                "processing": true,
                "serverSide": true,
                buttons: [{extend: 'copyHtml5'}, {
                    extend: 'excelHtml5',
                    exportOptions: {columns: ':visible'},
                },
                    {
                        extend: 'pdfHtml5',
                        orientation: 'landscape',
                        pageSize: 'TABLOID'
                    },
                    'colvis','pageLength'],
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],

                ajax: {
                    url: '{!! route('others_transactions.data') !!}',
                    data: function (d) {
                        d.branch = $('select[name=branch_id]').val();
                        d.start_date = $('input[name=start_date]').val();
                        d.end_date = $('input[name=end_date]').val();
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'amount', name: 'amount'},
                    {data: 'date_payed', name: 'date_payed'},
                    {data: 'expense_name', name: 'expense_name'},
                    {data: 'paid_by', name: 'paid_by'},
                    {data: 'branch', name: 'branch'},
                    {data: 'description', name: 'description'},
                ],
                order:[0, 'DESC']
            });


            $('#search').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });

   </script>
@stop
