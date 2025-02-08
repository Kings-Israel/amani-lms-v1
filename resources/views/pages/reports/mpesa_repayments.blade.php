@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
@stop


@section("content")

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-block">
                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                <input type="text" value="" id="start_date" autocomplete="off" name="start_date" value="" class="datepicker form-control" required>
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
                                <input value="" type="text" id="end_date" autocomplete="off" name="end_date" value="" class="datepicker form-control" required>
                            </div>
                            @if ($errors->has('end_date'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('end_date') }}</strong>
                                </span>
                            @endif
                        </div>


                        <div class="col-md-3">
                            <label for="branch">BRANCH</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                    <option selected value="all" >
                                        All Branches
                                    </option>
                                    @foreach($branches as $brach)
                                        <option value="{{$brach->id}}" >
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
                            <button class="btn btn-grd-primary">View Statement</button>
                        </div>
                    </form>

                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Amount</th>
                                <th>Loan Account</th>
                                <th>Branch</th>
                                <th>Transaction ID</th>
                                <th>Date Payed</th>


                                {{-- <th>Action</th>--}}
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Amount</th>
                                <th>Loan Account</th>
                                <th>Branch</th>
                                <th>Transaction ID</th>
                                <th>Date Payed</th>

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
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],



           // ajax: '{!! route('mpesa_repayments.data') !!}',
            ajax: {
                url: '{!! route('mpesa_repayments.data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();
                    d.start_date = $('input[name=start_date]').val();
                    d.end_date = $('input[name=end_date]').val();

                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'owner', name: 'owner'},
                {data: 'phone', name: 'phone'},
                {data: 'amount', name: 'amount'},
                {data: 'loan_account', name: 'loan_account'},
                {data: 'branch', name: 'branch'},
                {data: 'transaction_id', name: 'transaction_id'},
                {data: 'date_payed', name: 'date_payed'},

                /* {data: 'branch', name: 'branch'},
                 {data: 'status', name: 'status'},
                 {data: 'role', name: 'role'},*/
                /*  { data: 'action', name: 'action', orderable: false, searchable: false }
  */
            ],


        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });
    </script>

    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
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


