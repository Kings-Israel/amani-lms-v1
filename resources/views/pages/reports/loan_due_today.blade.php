@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') or \Illuminate\Support\Facades\Auth::user()->hasRole('accountant') or \Illuminate\Support\Facades\Auth::user()->hasRole('agent_care'))
                        <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-3">
                            <label for="branch">BRANCH</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch" required>
                                    <option value="all" > All </option>
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
                            <label for="branch">Loan Officer</label>

                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                    <option value="all">All</option>
                                    @foreach($lfs as $lf)
                                        <option  value="{{$lf->id}}" > {{$lf->name.'  -  '.$lf->branch}} </option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('name'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="col-md-3">
                            <label>Due Date</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                <input type="text" id="due_date" autocomplete="off" name="due_date" value="{{now()->format('Y-m-d')}}" class="datepicker form-control" required>
                            </div>
                            @if ($errors->has('due_date'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('due_date') }}</strong>
                                </span>
                            @endif
                        </div>


                        <div class="col-md-2">
                            <button class="btn btn-sm btn-primary">Filter</button>
                        </div>
                    </form>
                    @else
                        <form id="search" class="form-inline row" method="post" action="">
                            @csrf
                             <div class="col-md-3">
                                <label>Due Date</label>
                                <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                    <input type="text" id="due_date" autocomplete="off" name="due_date" value="{{now()->format('Y-m-d')}}" class="datepicker form-control" required>
                                </div>
                                @if ($errors->has('due_date'))
                                    <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('due_date') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-sm btn-primary">Filter</button>
                            </div>
                        </form>
                    @endif
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
                                <th>Installment Due</th>
                                <th>% Interest</th>
                                <th>Principal Amount</th>
                                <th>Amount Paid</th>
                                <th>Disbursed Date</th>
                                <th>RO</th>
                                <th>Loan REF NO:</th>
                                <th>Next Payment</th>


                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Installment Due</th>
                                <th></th>
                                <th>Amount</th>
                                <th>Amount Paid</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>



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
        var table = $('#cbtn-selectors1').DataTable({
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

            {{--ajax: '{!! route('loans_due_today_data') !!}',--}}
            ajax: {
                url: '{!! route('loans_due_today_data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch]').val();
                    d.lf = $('select[name=name]').val();
                    d.due_date = $('input[name=due_date]').val();
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'owner', name: 'customers.lname'},
                {data: 'phone', name: 'customers.phone'},
                {data: 'product_name', name: 'products.product_name'},
                {data: 'installments', name: 'products.installments'},
                {data: 'installment_due', name: 'installment_due'},
                {data: 'interest', name: 'products.interest'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'disbursement_date', name: 'disbursement_date'},
                {data: 'field_agent', name: 'field_agent'},
                {data: 'loan_account', name: 'loan_account'},
                {data: 'next_payment_date', name: 'next_payment_date'},
            ],

            "footerCallback": function ( row, data, start, end, display ) {
                var api = this.api(), data;

                // converting to interger to find total
                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '')*1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                // computing column Total of the complete result
                var loanTotal = api
                    .column( 7 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var TotalPaid = api
                    .column( 8 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
              /*  var percentagePaid = api
                    .column( 8 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var percentagePaidData = api
                    .column( 8 )
                    .data();*/

                var instalment_due = api
                    .column( 5 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                //var data2 = this.flatten();

                // Update footer by showing the total with the reference of the column index
                $( api.column( 7 ).footer() ).html(loanTotal);
                $( api.column( 8 ).footer() ).html(TotalPaid);
                $( api.column( 5 ).footer() ).html(instalment_due);
            },
        });

        $('#search').on('submit', function(e) {
            table.draw();
            e.preventDefault();
        });
    </script>

     <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
        jQuery(document).ready(function () {
            let maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 31);

            let minDate = new Date();
            minDate.setDate(minDate.getDate() - 31);

            jQuery('#due_date').datetimepicker({
                //format:'Y-m-d H:i'
                format:'Y-m-d',
                maxDate: maxDate,
                minDate:minDate
            });
        })

    </script>


@stop
