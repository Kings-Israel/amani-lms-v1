@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">


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
                            <label for="branch">Branch</label>

                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                    <option disabled selected>Select Branch</option>
                                    @foreach($branches as $brach)
                                        <option value="{{$brach->id}}" > {{$brach->bname}}
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
                        <div class="col-md-4">
                            <label for="branch">Credit Officer</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                <option disabled selected>Select Credit Officer</option>
                                    @foreach($lfs as $brach)

                                        <option
                                                value="{{$brach->id}}" >
                                            {{$brach->name}}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('name'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                            @endif
                        </div>


                        <div class="col-md-4">
                            <button class="btn btn-sm btn-primary">Filter</button>
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
                                <th>Product</th>
                                <th>Branch</th>
                                <th>Loan Officer</th>
                                <th>Loan Total Amount</th>
                                <th>Total Paid Amount</th>
                                <th>% Paid</th>
                                <th>Balance</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th colspan="6">Total</th>
                                <th>Loan Total Amount</th>
                                <th>Total Paid Amount</th>
                                <th>% Paid</th>
                                <th>Balance</th>
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
    <script type="text/javascript" src="{{asset('bower_components/sweetalert/js/sweetalert.min.js')}}"></script>

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
                },
                'colvis','pageLength'],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],

           ajax: {
                url: '{!! route('group_loans_balance_data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();
                    d.lf = $('select[name=name]').val();

                }
            },
            columns: [
                /* {data: 'checkbox', name: 'checkbox'},*/

                {data: 'id', name: 'id'},
                {data: 'owner', name: 'customers.lname'},
                {data: 'phone', name: 'customers.phone'},
                {data: 'product_name', name: 'products.product_name'},
                {data: 'branch', name: 'branch'},
                {data: 'field_agent', name: 'field_agent'},
                {data: 'total', name: 'total'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'percentage_paid', name: 'percentage_paid'},
                {data: 'balance', name: 'balance'},






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
                    .column( 6 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var TotalPaid = api
                    .column( 7 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var percentagePaid = api
                    .column( 8 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var percentagePaidData = api
                    .column( 8 )
                    .data();

                var balance = api
                    .column( 9 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                //var data2 = this.flatten();




                // Update footer by showing the total with the reference of the column index
                $( api.column( 6 ).footer() ).html(loanTotal);
                $( api.column( 7 ).footer() ).html(TotalPaid);
                $( api.column( 8 ).footer() ).html(percentagePaid/percentagePaidData.count());
                $( api.column( 9 ).footer() ).html(balance);



            },
        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });


    </script>


@stop
