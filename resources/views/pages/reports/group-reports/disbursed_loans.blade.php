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
                    <div class="col-md-3">
                        <label for="branch">BRANCH</label>

                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                            <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                @foreach($branches as $brach)

                                    <option
                                            value="{{$brach->id}}" >
                                        {{$brach->bname}}
                                    </option>
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
                                <input type="text" value="" id="start_date" autocomplete="off" name="start_date" class="datepicker form-control" required>
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
                                <input value="" type="text" id="end_date" autocomplete="off" name="end_date" class="datepicker form-control" required>
                            </div>
                            @if ($errors->has('end_date'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('end_date') }}</strong>
                                </span>
                            @endif
                        </div>
                            <div class="col-md-3">
                                <button class="btn btn-sm btn-primary">Filter</button>
                            </div>

                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Credit Officer</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Principal Amount</th>
                                <th>Total</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Date Created</th>
                                <th>Date Approved</th>
                                <th>Date Disbursed</th>
                                <th>End Date</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Credit Officer</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Principal Amount</th>
                                <th>Total</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Date Created</th>
                                <th>Date Approved</th>
                                <th>Date Disbursed</th>
                                <th>End Date</th>
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

           ajax: {
                url: '{!! route('group_disbursed_loans_data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();
                    d.start_date = $('input[name=start_date]').val();
                    d.end_date = $('input[name=end_date]').val();

                }
            },
            order: [0, 'desc'],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'branch', name: 'branch'},
                {data: 'credit_officer', name: 'credit_officer'},
                {data: 'owner', name: 'customers.lname'},
                {data: 'phone', name: 'customers.phone'},
                {data: 'product_name', name: 'products.product_name'},
                {data: 'installments', name: 'products.installments'},
                {data: 'interest', name: 'products.interest'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'total', name: 'total'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'balance', name: 'balance'},
                {data: 'date_created', name: 'date_created'},
                {data: 'approved_date', name: 'approved_date'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'end_date', name: 'end_date'},





            ],
        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });

        jQuery(document).ready(function () {

                jQuery('#start_date').datetimepicker({

                    //format:'Y-m-d H:i'
                    format:'Y-m-d'

                });

                jQuery('#end_date').datetimepicker({

                    //format:'Y-m-d H:i'
                    format:'Y-m-d'

                });
})
    </script>


@stop
