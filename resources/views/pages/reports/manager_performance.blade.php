@extends("layouts.master")
@section('css')
@stop
@section("content")
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-xl-4">
                    <div class="card">
                        <small class="text-center mt-2 mb-2">Click on card titles to minimize</small>
                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active" onclick="loanDetails()"><a href="#">Loan Details</a></li>
                                <div id="loanDetails" >
                                    <li class="list-group-item" ><a href="#"><b>Active Loans:</b> </a>{{$loans}}</li>
                                    <li class="list-group-item" ><a href="#"><b>Active Loans Amount:</b> Ksh. {{number_format($totalAmount)}} </a></li>
                                </div>
                            </ul>
                        </div>

                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active" onclick="disbDetails()"><a href="#">Disbursement Details</a></li>
                                <div id="disbDetails" >
                                    <li class="list-group-item" ><a href="#"><b>Total Disbursed Loans:</b> </a>{{$disbCount}}</li>
                                    <li class="list-group-item" ><a href="#"><b>Total Disbursed Amount:</b> Ksh. {{number_format($disbTotalAmount)}} </a></li>
                                    <li class="list-group-item" ><a href="#"><b>Loan Size:</b> {{number_format($loanSize, 2)}} </a></li>
                                    <hr>
                                    <li class="list-group-item" ><a href="#"><b>Disbursed Loans - {{date('M')}}:</b> </a>{{$disbCountMonth}}</li>
                                    <li class="list-group-item" ><a href="#"><b>Disbursed Amount - {{date('M')}}:</b> Ksh. {{number_format($disbTotalAmountMonth)}} </a></li>
                                    <li class="list-group-item" ><a href="#"><b>Avg. Loan Size - {{date('M')}}:</b> {{number_format($loanSizeMonth, 2)}} </a></li>
                                </div>
                            </ul>
                        </div>

                        <div class="card-block groups-contact">
                            <ul class="list-group">
                                <li class="list-group-item active" onclick="dueCollections()"><a href="#" style="color: white">Due Collections</a></li>
                                <div id="dueCollections" >
                                    <li class="list-group-item justify-content-between"> <b>Total Due Amount:</b> Ksh. {{number_format($due)}}</li>
                                    <li class="list-group-item justify-content-between"> <b>Total Collected:</b> Ksh. {{number_format($paid)}}</li>
                                    <li class="list-group-item justify-content-between"> <b>Repayment Rate:</b> {{$repayment_rate}} %</li>
                                </div>

                            </ul>
                        </div>
                        <div class="card-block groups-contact">
                            <ul class="list-group">
                                <li class="list-group-item active" onclick="rolledOver()"><a href="#" style="color: white">Rolled Over Loans</a></li>
                                <div id="rolledOver" >
                                    <li class="list-group-item justify-content-between">
                                        <b>Today:</b> {{$rolled_over_loans_count_today}}  -  Ksh. {{number_format($rolled_over_balance_today)}}
                                    </li>
                                    <li class="list-group-item justify-content-between">
                                        <b>This Month: </b> {{$rolled_over_loans_count}} - Ksh. {{number_format($rolled_over_balance)}} </li>
                                    <li class="list-group-item justify-content-between">
                                        <b>This Year:</b> {{$rolled_over_loans_count_year}} - Ksh. {{number_format($rolled_over_balance_year)}}
                                    </li>
                                </div>
                            </ul>
                        </div>

                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active" onclick="npLoans()"><a href="#">Non Performing Loans</a></li>
                                <div id="npLoans" >
                                    <li class="list-group-item" ><a href="#"><b>Count:</b> </a>{{$non_performing_count}}</li>
                                    <li class="list-group-item" ><a href="#"><b>Total Amount:</b> Ksh. {{number_format($non_performing_balance)}} </a></li>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row justify-content-center">
                                <div class="col-xl-4 col-md-4">
                                    <div class="card">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Customers</p>
                                                    <h4 class="m-b-0">{{$customers}}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-user-check f-50 text-c-pink"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4">
                                    <div class="card ">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Credit Officers</p>
                                                    <h4 class="m-b-0">{{$cred_officers}}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-users f-50 text-dark"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-8 col-md-12">
                                    <div class="card ">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Paid Cumulative Interest / Total Interest <b>( {{\Carbon\Carbon::now()->format('M-Y')}} )</b> </p>
                                                    <h4 class="m-b-0">Ksh. {{number_format($interest_paid)}} / Ksh. {{number_format($total_interest)}}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-bar-chart-2 f-50 text-c-blue"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-header-text">Monthly Loan Collection Percentage</h5>
                                </div>
                                <div class="card-block contact-details">
                                    <form class="form-inline row" method="post">
                                        @csrf
                                        <div class="col-md-3">
                                            <label for="collection_month">Select Month <small> - ({{\Carbon\Carbon::now()->format('Y')}})</small></label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-calendar"></i></span>
                                                <select id="collection_month" name="collection_month" class="form-control{{ $errors->has('collection_month') ? ' is-invalid' : '' }}">
                                                    @foreach($months as $month)
                                                        <option value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($errors->has('collection_month'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('collection_month') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                    </form>
                                    <div class="data_table_main table-responsive dt-responsive">
                                        <table id="collection"
                                               class="table  table-striped table-bordered nowrap">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Loans</th>
                                                <th>Due Count</th>
                                                <th>Complete Count</th>
                                                <th>Percentage</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Loans</th>
                                                <th>Due Count</th>
                                                <th>Complete Count</th>
                                                <th>Percentage</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-header-text">Income Statement</h5>
                                </div>
                                <div class="card-block contact-details">
                                    <form class="form-inline row" method="post">
                                        @csrf
                                        <div class="col-md-3">
                                            <label for="collection_month">Select Month <small> - ({{\Carbon\Carbon::now()->format('Y')}})</small></label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-calendar"></i></span>
                                                <select id="income_month" name="income_month" class="form-control{{ $errors->has('income_month') ? ' is-invalid' : '' }}">
                                                    @foreach($months as $month)
                                                        <option value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($errors->has('income_month'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('income_month') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                    </form>
                                    <div class="data_table_main table-responsive dt-responsive">
                                        <table id="income"
                                               class="table  table-striped table-bordered nowrap">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Income Group</th>
                                                <th>Amount <small>month</small></th>
                                                <th>Amount <small>year</small></th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Income Group</th>
                                                <th>Amount <small>month</small></th>
                                                <th>Amount <small>year</small></th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-header-text">Target Performance</h5>
                        </div>
                        <div class="card-block">
                            <form class="form-inline row" method="post">
                                @csrf
                                <div class="col-md-3">
                                    <label for="month">Select Month <small> - ({{\Carbon\Carbon::now()->format('Y')}})</small></label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-calendar"></i></span>
                                        <select id="month" name="month" class="form-control{{ $errors->has('month') ? ' is-invalid' : '' }}">
                                            @foreach($months as $month)
                                                <option value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>

                                            @endforeach
                                        </select>
                                    </div>
                                    @if ($errors->has('month'))
                                        <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('month') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </form>

                            <div class="dt-responsive table-responsive">
                                <table id="cbtn-selectors1"
                                       class="table table-striped table-bordered nowrap">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cred Officer</th>
                                        <th>Disbursement Target</th>
                                        <th>Actual Disbursement</th>
                                        <th>Achieved %</th>
                                        <th>Collection Target</th>
                                        <th>Actual Collection</th>
                                        <th>Achieved %</th>
                                        <th>Average Performance %</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        {{--<th></th>--}}
                                        <th>Disbursement Target</th>
                                        <th>Actual Disbursement</th>
                                        <th>Achieved</th>
                                        <th>Collection Target</th>
                                        <th>Actual Collection</th>
                                        <th>Achieved</th>
                                        {{-- <th>Due Collection</th>
                                         <th></th>--}}
                                        <th>Average Performance</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


@stop

@section('js')
    <script>
        function loanDetails() {
            var x = document.getElementById("loanDetails");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
        function disbDetails() {
            var x = document.getElementById("disbDetails");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
        function dueCollections() {
            var x = document.getElementById("dueCollections");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
        function rolledOver() {
            var x = document.getElementById("rolledOver");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
        function npLoans() {
            var x = document.getElementById("npLoans");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
        function format ( d ) {
            console.log(d);
            function getCustomerData(){
                //loop through predictions array
                var data =  d.customer_data;
                var dataArray = [];
                for (var index = 0; index < data.length; ++index) {
                    var contact = data[index].contact;
                    var balance = data[index].balance;
                    var amount = data[index].amount;
                    var str = '';
                    var value = str.concat('<tr>', '<td>',contact, '</td>','<td>', balance,'</td>', '<td>',amount);
                    dataArray.push(value);
                }
                return dataArray;
            }
            let output = `
                        <table style="padding-left:50px; margin-bottom: 10px">
                            <tr>
                                <td><b>Total Amount Due:</b></td>
                                <td>${d.total_amount}</td>
                                <td><b>Total Balance:</b></td>
                                <td>${d.total_balance}</td>
                            </tr>
                        </table>
                        <table style="padding-left:50px;">
                            <thead>
                            <tr>
                              <th scope="col">Customer</th>
                              <th scope="col">Loan Balance</th>
                              <th scope="col">Loan Amount <small>(Interest Included)</small></th>
                              <th></th>
                            </tr>
                          </thead>
                           <tbody>
                            ${getCustomerData()}
                            </tbody>
                        </table>
    `;

            return output;
        }
        var collection = $('#collection').DataTable({
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
                url: '{!! route('monthly_collection_performance_data', ['id'=>$user->id]) !!}',
                data: function(d) {
                    d.collection_month = $('select[name=collection_month]').val();
                },
            },
            columns: [
                {
                    className : 'details-control',
                    orderable : false,
                    data : null,
                    defaultContent : ''
                },
                {data: 'loans'},
                {data: 'loans_due'},
                {data: 'loans_complete'},
                {data: 'percentage'},
                /*  { data: 'action', name: 'action', orderable: false, searchable: false }
  */
            ],
        });
        $('#collection_month').on('change', function(e) {
            collection.clear().draw();
            collection.columns.adjust().draw();
        });
        $('#collection tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = collection.row( tr );
            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                // Open this row
                row.child( format(row.data()) ).show();
                tr.addClass('shown');
            }
        });
        var oTable = $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                processing: true,
                serverSide: true,
                buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                    extend: 'excelHtml5',
                    exportOptions: {columns: ':visible'}
                }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis'],
                    ajax: {
                        url: '{!! route('manager_performance_data', ['id' => encrypt($user->id)]) !!}',
                        data: function(d) {
                            d.month = $('select[name=month]').val();
                        },
                    },
                columns: [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'disbursement_target'},
                    {data: 'actual_disbursement'},
                    {data: 'disbursement_achieved'},
                    {data: 'collection_target'},
                    {data: 'actual_collection'},
                    {data: 'collection_achieved'},
                    {data: 'average_performance'},
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
                    var dis_target = api
                        .column( 2 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    var dis_targetData = api
                        .column( 2 )
                        .data();
                    var actual_dis = api
                        .column( 3 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    var dis_achieved = api
                        .column( 4 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    var dis_achievedData = api
                        .column( 4 )
                        .data();
                    var CT = api
                        .column( 5 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    var AC = api
                        .column( 6 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
                    var AC_achieved = api
                        .column( 7 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                    var AC_achievedData = api
                        .column( 7 )
                        .data();
                  /*  var due = api
                        .column( 8 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );*/

                    var average = api
                        .column( 8 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                    var averageData = (Math.floor((actual_dis / dis_target)*100) + Math.floor((AC / CT)* 100))/2;
                    var averageData2 = api
                        .column( 8 )
                        .data();
                    // Update footer by showing the total with the reference of the column index
                    $( api.column( 2 ).footer() ).html(dis_target /*/ dis_targetData.count()*/);
                    $( api.column( 3 ).footer() ).html(actual_dis);
                    $( api.column( 4 ).footer() ).html(Math.round((actual_dis / dis_target)*100));
                    $( api.column( 5 ).footer() ).html(CT);
                    $( api.column( 6 ).footer() ).html(AC);
                   // $( api.column( 7 ).footer() ).html(AC_achieved / AC_achievedData.count());
                    $( api.column( 7 ).footer() ).html(Math.round((AC / CT)* 100));
                   /* $( api.column( 8 ).footer() ).html(averageData);*/
                    //$( api.column( 8 ).footer() ).html(average / averageData2.count());
                    $( api.column( 8 ).footer() ).html((Math.round((actual_dis / dis_target)*100 + (AC / CT)* 100))/ 2);
                },
            });

        $('#month').on('change', function(e) {
            oTable.clear().draw();
            oTable.columns.adjust().draw();
        });

        var income = $('#income').DataTable({
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
                url: '{!! route('co_income_data', ['id'=>$user->id]) !!}',
                data: function(d) {
                    d.income_month = $('select[name=income_month]').val();
                },
            },
            columns: [
                {data: 'id'},
                {data: 'income_group'},
                {data: 'month'},
                {data: 'year'},
                /*  { data: 'action', name: 'action', orderable: false, searchable: false }
  */
            ],
        });
        $('#income_month').on('change', function(e) {
            income.clear().draw();
            income.columns.adjust().draw();
        });
    </script>
@stop
