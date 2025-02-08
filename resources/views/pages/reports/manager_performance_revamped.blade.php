@extends("layouts.master")
@section('css')
    <style>
        .hide {
            display: none;
        }

        .alert1 {
            background: red;
            color: white;
        }
    </style>

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
                                <li class="list-group-item active" onclick="loanDetails()"><a href="#">Loan Details</a>
                                    <div class="preloader3 active_loans loader-block" style="height: 0">
                                        <div class="circ1 loader-danger"></div>
                                        <div class="circ2 loader-danger"></div>
                                        <div class="circ3 loader-danger"></div>
                                        <div class="circ4 loader-danger"></div>
                                    </div>

                                </li>
                                <div id="loanDetails">
                                    <li class="list-group-item"><a href="#"><b>Active Loans:</b> </a><span
                                            id="active_loans"></span></li>
                                    <li class="list-group-item"><a href="#"><b>Active Loans Amount:</b> Ksh. <span
                                                id="total_amount"></span> </a></li>
                                </div>
                            </ul>
                        </div>
                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active" onclick="disbDetails()"><a href="#">Disbursement
                                        Details</a>
                                    <div class="preloader3 disbursed_loans loader-block" style="height: 0">
                                        <div class="circ1 loader-danger"></div>
                                        <div class="circ2 loader-danger"></div>
                                        <div class="circ3 loader-danger"></div>
                                        <div class="circ4 loader-danger"></div>
                                    </div>

                                </li>
                                <div id="disbDetails">
                                    <li class="list-group-item"><a href="#"><b>Total Disbursed Loans:</b> </a><span
                                            id="disbursed_loans"></span></li>
                                    <li class="list-group-item"><a href="#"><b>Total Disbursed Amount:</b> Ksh. <span
                                                id="disbTotalAmount"></span> </a></li>
                                    <li class="list-group-item"><a href="#"><b>Loan Size:</b> <span
                                                id="loanSize"></span> </a></li>
                                    <hr>
                                    <li class="list-group-item"><a href="#"><b>Disbursed Target - {{date('M')}}:</b>
                                        </a><span id="disbursedMonthTarget"></span></li>
                                    <li class="list-group-item"><a href="#"><b>Disbursed Loans - {{date('M')}}:</b> </a><span
                                            id="disbursedMonth"></span></li>
                                    <li class="list-group-item"><a href="#"><b>Disbursed Amount - {{date('M')}}:</b>
                                            Ksh. <span id="disbursedMonthAmount"></span> </a></li>
                                    <li class="list-group-item"><a href="#"><b>Target Achieved - {{date('M')}}:</b>
                                            <span id="disbursedMonthTargetAchieved"></span> </a></li>

                                    <li class="list-group-item"><a href="#"><b>Avg. Loan Size - {{date('M')}}:</b> <span
                                                id="loanSizeMonth"></span> </a></li>
                                </div>
                            </ul>
                        </div>
                        <div class="card-block groups-contact">
                            <ul class="list-group">
                                <li class="list-group-item active" onclick="dueCollections()"><a href="#"
                                                                                                 style="color: white">Due
                                        Collections</a>
                                    <div class="preloader3 due_today_amount loader-block" style="height: 0">
                                        <div class="circ1 loader-danger"></div>
                                        <div class="circ2 loader-danger"></div>
                                        <div class="circ3 loader-danger"></div>
                                        <div class="circ4 loader-danger"></div>
                                    </div>
                                </li>
                                <div id="dueCollections">
                                    <li class="list-group-item justify-content-between"><b>Total Due Amount:</b> Ksh.
                                        <span id="due_today_amount"></span></li>
                                    <li class="list-group-item justify-content-between"><b>Total Collected:</b> Ksh.
                                        <span id="amount_paid"></span></li>
                                    <li class="list-group-item justify-content-between"><b>Repayment Rate:</b> <span
                                            id="repayment_rate"></span> %
                                    </li>
                                </div>

                            </ul>
                        </div>

                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active" onclick="npLoans()"><a href="#">Non Performing
                                        Loans</a>
                                    <div class="preloader3 non_performing_loans  loader-block" style="height: 0">
                                        <div class="circ1 loader-danger"></div>
                                        <div class="circ2 loader-danger"></div>
                                        <div class="circ3 loader-danger"></div>
                                        <div class="circ4 loader-danger"></div>
                                    </div>
                                </li>
                                <div id="npLoans">
                                    <li class="list-group-item"><a href="#"><b>Count:</b> </a><span
                                            id="non_performing_loans"></span></li>
                                    <li class="list-group-item"><a href="#"><b>Total Amount:</b> Ksh. <span
                                                id="non_performing_balance"></span> </a></li>
                                </div>
                            </ul>
                        </div>


                        <div class="card-block groups-contact">
                            <ul class="list-group">
                                <li class="list-group-item active" onclick="rolledOver()"><a href="#"
                                                                                             style="color: white">Interactions</a>
                                    <div class="preloader3 total_interactions  loader-block" style="height: 0">
                                        <div class="circ1 loader-danger"></div>
                                        <div class="circ2 loader-danger"></div>
                                        <div class="circ3 loader-danger"></div>
                                        <div class="circ4 loader-danger"></div>
                                    </div>
                                </li>
                                <div id="rolledOver">
                                    <li class="list-group-item justify-content-between">
                                        <b>Total:</b> <span id="total_interactions"></span>
                                    </li>
                                    <li class="list-group-item justify-content-between">
                                        <b>Active: </b><span id="active_interactions"></span></li>


                                    <li class="list-group-item justify-content-between">
                                        <b>Closed:</b> <span id="inactive_interactions"></span>
                                    </li>
                                    <li class="list-group-item justify-content-between">
                                        <b>Closed With Success:</b> <span id="interactions_success"></span>
                                    </li>

                                    <li class="list-group-item justify-content-between mytooltip tooltip-effect-1">
                                        <b class="tooltip-item2" style="color: unset"> Active Pre_interactions:
                                            <span class="tooltip-item2"><i
                                                    class="icofont icofont-info-circle"></i></span>
                                        </b><span id="pre_interactions"></span>

                                        <span class="mytooltip tooltip-effect-1">
                                        <span class="tooltip-content4 clearfix">
                                         <span
                                             class="tooltip-text2">    This shows ACTIVE unattended pre interactions</span>
                                        </span>
                                        </span>

                                    </li>

                                    <li class="list-group-item justify-content-between mytooltip tooltip-effect-1">
                                        <b class="tooltip-item2" style="color: unset">Passed Pre_interactions:<span
                                                class="tooltip-item2"><i class="icofont icofont-info-circle"></i></span>
                                        </b><span
                                            id="passed_unttanded_pre_interactions"></span>

                                        <span class="mytooltip tooltip-effect-1">
                                        <span class="tooltip-content4 clearfix">
                                         <span class="tooltip-text3">    This shows passed pre interactions which were never interacted</span>
                                        </span>
                                        </span>

                                    </li>

                                    <li class="list-group-item justify-content-between mytooltip tooltip-effect-1">
                                        <b class="tooltip-item2" style="color: unset">Success Rate:<span
                                                class="tooltip-item2"><i class="icofont icofont-info-circle"></i></span></b>
                                        <span id="success_rate"></span>
                                        <span class="mytooltip tooltip-effect-1">
                                        <span class="tooltip-content4 clearfix">
                                         <span class="tooltip-text3">    Formulae For this Computation: <br>Closed With Success ÷ <br>(Total + Active Pre Interactions + Passed Pre_interactions) * 100</span>
                                        </span>
                                        </span>

                                    </li>
                                    <hr>

                                    <li class="list-group-item justify-content-between mytooltip tooltip-effect-1">
                                        <b class="tooltip-item2" style="color: unset">Monthly Interactions: <span
                                                class="tooltip-item2"><i class="icofont icofont-info-circle"></i></span></b><span
                                            id="this_month_interactions"></span>
                                        <span class="mytooltip tooltip-effect-1">
                                        <span class="tooltip-content4 clearfix">
                                         <span class="tooltip-text3">   These are the interactions created by the pair during this month</span>
                                        </span>
                                        </span>

                                    </li>


                                    <li class="list-group-item justify-content-between">
                                        <b>Monthly Closed:</b> <span id="this_month_interactions_closed"></span>
                                    </li>
                                    <li class="list-group-item justify-content-between">
                                        <b>M Closed With Success:</b> <span id="this_month_interactions_success"></span>
                                    </li>
                                    <li class="list-group-item justify-content-between mytooltip tooltip-effect-1">
                                        <b class="tooltip-item2" style="color: unset">M Success Rate:<span
                                                class="tooltip-item2"><i class="icofont icofont-info-circle"></i></span></b>
                                        <span id="monthly_success_rate"></span>

                                        <span class="mytooltip tooltip-effect-1">
                                        <span class="tooltip-content4 clearfix">
                                         <span class="tooltip-text3">   Formulae For this Computation: <br>M Closed With Success ÷ <br>(Monthly Total Interactions + Monthly Pre Interactions) * 100</span>
                                        </span>
                                        </span>
                                    </li>
                                </div>
                            </ul>
                        </div>


                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active" onclick="loanDetails()"><a href="#">Customer
                                        Monthly</a>
                                    <div class="preloader3 active_loans loader-block" style="height: 0">
                                        <div class="circ1 loader-danger"></div>
                                        <div class="circ2 loader-danger"></div>
                                        <div class="circ3 loader-danger"></div>
                                        <div class="circ4 loader-danger"></div>
                                    </div>

                                </li>
                                <div id="loanDetails">
                                    <li class="list-group-item"><a href="#"><b>Total Onboarded:</b> </a><span
                                            id="this_month_customers"></span></li>
                                    <li class="list-group-item"><a href="#"><b>Target:</b> <span
                                                id="this_month_customers_onborded_target"></span> </a></li>
                                    <li class="list-group-item"><a href="#"><b>Target Achieved:</b> <span
                                                id="this_month_customers_onborded_target_achieved"></span> </a></li>


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
                                                    <h4 class="m-b-0" id="customers"></h4>
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
                                                    <h4 class="m-b-0" id="total_los"></h4>
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
                                                    <p class="m-b-5">Paid Cumulative Interest / Total Interest
                                                        <b>( {{\Carbon\Carbon::now()->format('M-Y')}} )</b></p>

                                                    <h4 class="m-b-0">Ksh. <span id="total_paid_interest"></span> / Ksh.
                                                        <span id="total_interest"></span></h4></div>
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
                                            <label for="collection_month">Select Month <small> -
                                                    ({{\Carbon\Carbon::now()->format('Y')}})</small></label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i
                                                        class="icofont icofont-calendar"></i></span>
                                                <select id="collection_month" name="collection_month"
                                                        class="form-control{{ $errors->has('collection_month') ? ' is-invalid' : '' }}">
                                                    @foreach($months as $month)
                                                        <option
                                                            value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>
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
                                            <label for="collection_month">Select Month <small> -
                                                    ({{\Carbon\Carbon::now()->format('Y')}})</small></label>
                                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i
                                                        class="icofont icofont-calendar"></i></span>
                                                <select id="income_month" name="income_month"
                                                        class="form-control{{ $errors->has('income_month') ? ' is-invalid' : '' }}">
                                                    @foreach($months as $month)
                                                        <option
                                                            value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>
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
                                        <table id="income1"
                                               class="table hide  table-striped table-bordered nowrap"
                                               style="display: none">
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


                                        <table id="cbtn-selectors"
                                               class="table table-striped nowrap">
                                            <thead class="table-primary">
                                            <tr>
                                                <th colspan="3" style="color: white">Fiscal Year: {{date('Y')}} / <span id="current_month"
                                                        style="text-transform: uppercase">{{date('M')}}</span>
                                                    <div class="preloader3 income_statement loader-block"
                                                         style="height: 0">
                                                        <div class="circ1 loader-danger"></div>
                                                        <div class="circ2 loader-danger"></div>
                                                        <div class="circ3 loader-danger"></div>
                                                        <div class="circ4 loader-danger"></div>
                                                    </div>
                                                </th>


                                            </tr>


                                            </thead>
                                            <tbody>
                                            <tr>
                                                <th>Gross Margin Group
                                                    <hr>
                                                </th>
                                                <th>{{date('M')}}
                                                    <hr>
                                                </th>
                                                <th>YTD
                                                    <hr>
                                                </th>
                                            </tr>
                                            @foreach($products as $data)
                                                <tr>
                                                    <td>{{$data->product_name}} Loans Interest</td>
                                                    <td><span id="{{$data->id}}_monthly"></span></td>
                                                    <td><span id="{{$data->id}}YTD"></span></td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <th>TOTAL Loans Interest</th>
                                                <th><span id="total_loan_interest_month"></span></th>
                                                <th><span id="total_loan_interest_year"></span></th>
                                            </tr>
                                            <tr>
                                                <th>TOTAL Loans Processing Fee</th>
                                                <th><span id="total_loan_processing_fee_month"></span></th>
                                                <th><span id="total_loan_processing_fee_year"></span></th>
                                            </tr>
                                            <tr>
                                                <th>Total Commission</th>
                                                <th><span id="joining_fee_month"></span></th>
                                                <th><span id="joining_fee_year"></span></th>
                                            </tr>

                                            <tr>
                                                <td colspan="3"></td>
                                            </tr>
                                            <tr>
                                                <th><span style="text-transform: uppercase; font-style: italic">Total Income</span>
                                                </th>
                                                <th><span style="font-style: italic">(<span
                                                            id="total_income_month"></span>)</span>
                                                    <hr>
                                                </th>
                                                <th><span style="font-style: italic">(<span
                                                            id="total_income_year"></span>)</span>
                                                    <hr>
                                                </th>
                                            </tr>
                                            </tbody>

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
                                    <label for="month">Select Month <small> - ({{\Carbon\Carbon::now()->format('Y')}}
                                            )</small></label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i
                                                class="icofont icofont-calendar"></i></span>
                                        <select id="month" name="month"
                                                class="form-control{{ $errors->has('month') ? ' is-invalid' : '' }}">
                                            @foreach($months as $month)
                                                <option
                                                    value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>

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
                                        <th>Customer Target</th>
                                        <th>Customer Onboarded</th>
                                        <th>Customer Target Achieved</th>
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
                                        <th>Customer Target</th>
                                        <th>Customer Onboarded</th>
                                        <th>Customer Target Achieved</th>
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
        calls()

        function calls() {
            var $_base = '{{env('APP_URL')}}';


            //ajax calls
            //customers
            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_manager_performance/" + '{{$id}}',
                dataType: 'json',
                data: {
                    name: 'customers',


                },
                success: function (json) {
                    //  if (json.status === "success")
                    //.location.href = json.redirect;
                    //    $('#customers').text(json.data);
                    $('#customers').text(json.data['customers']);
                    $('#total_los').text(json.data['total_los']);


                    $('#this_month_customers').text(json.data['this_month_customers']);
                    $('#this_month_customers_onborded_target').text(json.data['this_month_customers_onborded_target']);
                    $('#this_month_customers_onborded_target_achieved').text(json.data['this_month_customers_onborded_target_achieved'] + "%");


                }
            });

            //active loans

            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_manager_performance/" + '{{$id}}',
                dataType: 'json',
                data: {
                    name: 'active_loans',


                },
                success: function (json) {
                    // if (json.status === "success")
                    //.location.href = json.redirect;
                    $('#active_loans').text(json.data);
                    $('.active_loans').addClass('hide')

                }
            });

            // active loans total amount
            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_manager_performance/" + '{{$id}}',
                dataType: 'json',
                data: {
                    name: 'total_amount',


                },
                success: function (json) {
                    //.location.href = json.redirect;
                    $('#total_amount').text(json.data['totalAmount']);
                    $('.total_amount').addClass('hide')


                }
            });

            //disbursed loans
            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_manager_performance/" + '{{$id}}',
                dataType: 'json',
                data: {
                    name: 'disbursed_loans',


                },
                success: function (json) {
                    $('#disbTotalAmount').text(json.data['disbTotalAmount']);
                    $('#disbursedMonth').text(json.data['disbursedMonth']);
                    $('#disbursedMonthAmount').text(json.data['disbursedMonthAmount']);
                    $('#disbursed_loans').text(json.data['disbursed_loans']);
                    if (json.data['exceeded'] === 1) {
                        $('#loanSize').addClass('alert1')
                    }

                    if (json.data['monthyly_eceeded'] === 1) {
                        $('#loanSizeMonth').addClass('alert1')
                    }
                    $('#loanSize').text(json.data['loanSize']);
                    $('#loanSizeMonth').text(json.data['loanSizeMonth']);
                    $('#disbursedMonthTarget').text(json.data['disbursedMonthTarget']);
                    $('#disbursedMonthTargetAchieved').text(json.data['disbursedMonthTargetAchieved'] + "%");

                    $('.disbursed_loans').addClass('hide')


                }
            });


            //due_today_amount
            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_manager_performance/" + '{{$id}}',
                dataType: 'json',
                data: {
                    name: 'due_today_amount',


                },
                success: function (json) {
                    $('#due_today_amount').text(json.data['due_today_amount']);
                    $('#amount_paid').text(json.data['amount_paid']);
                    $('#repayment_rate').text(json.data['repayment_rate']);
                    $('.due_today_amount').addClass('hide')


                }
            });

            //non_performing_loans
            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_manager_performance/" + '{{$id}}',
                dataType: 'json',
                data: {
                    name: 'non_performing_loans',


                },
                success: function (json) {
                    $('#non_performing_loans').text(json.data['non_performing_loans']);
                    $('#non_performing_balance').text(json.data['non_performing_balance']);
                    $('.non_performing_loans').addClass('hide')

                }
            });

            //manager ajax income statement

            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_manager_performance/" + '{{$id}}',
                dataType: 'json',
                data: {
                    name: 'interest_figures',


                },
                success: function (json) {
                    $('#total_interest').text(json.data['total_interest']);
                    $('#total_paid_interest').text(json.data['total_paid_interest']);

                }
            });


            //interactions

            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    // 'Cache-Control': 'no-cache, no-store, must-revalidate',

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_manager_performance/" + '{{$id}}',
                dataType: 'json',
                data: {
                    name: 'interactions',


                },
                success: function (json) {
                    //.location.href = json.redirect;
                    $('#total_interactions').text(json.data['interactions']);
                    $('#active_interactions').text(json.data['active']);
                    $('#inactive_interactions').text(json.data['inactive']);
                    $('#interactions_success').text(json.data['interactions_success']);


                    // $('#due_interactions').text(json.data['due']);
                    // $('#overdue_interactions').text(json.data['over_due']);
                    // $('#pdue_interactions').text(json.data['pdue']);
                    // $('#poverdue_interactions').text(json.data['poverdue']);

                    $('#this_month_interactions').text(json.data['this_month_interactions']);
                    $('#this_month_interactions_closed').text(json.data['this_month_interactions_closed']);
                    $('#this_month_interactions_success').text(json.data['this_month_interactions_success']);

                    $('#monthly_success_rate').text(json.data['monthly_success_rate'] + "%");
                    $('#success_rate').text(json.data['success_rate'] + "%");
                    $('#success_interaction').text(json.data['interactions_success']);
                    $('#pre_interactions').text(json.data['pre']);
                    $('#passed_unttanded_pre_interactions').text(json.data['passed_unttanded_pre_interactions']);


                    $('.total_interactions').addClass('hide')


                }
            });


        }

        incomes();


        $('#income_month').on('change', function () {
            $('.income_statement').removeClass('hide')

            incomes();
        });


        function incomes() {
            var $_base = '{{env('APP_URL')}}';


            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

                },
                cache: false,
                method: "get",
                url: $_base + "co_income_data_ajax/" + '{{$id}}',
                dataType: 'json',
                data: {
                    month: $('select[name=income_month]').val(),


                },
                success: function (json) {


                    $.each(json.data, function (i, value) {
                        if (value['id'] === 1) {
                            console.log(value['1_monthly'])
                            $('#1_monthly').text(value['1_monthly']);
                            $('#1YTD').text(value['1YTD']);


                        } else if (value['id'] === 2) {
                            $('#2_monthly').text(value['2_monthly']);
                            $('#2YTD').text(value['2YTD']);

                        } else if (value['id'] === 3) {
                            $('#3_monthly').text(value['3_monthly']);
                            $('#3YTD').text(value['3YTD']);

                        } else if (value['id'] === 4) {
                            $('#4_monthly').text(value['4_monthly']);
                            $('#4YTD').text(value['4YTD']);

                        } else if (value['id'] === 5) {
                            $('#5_monthly').text(value['5_monthly']);
                            $('#5YTD').text(value['5YTD']);

                        } else if (value['id'] === "total_loan_interest") {
                            $('#total_loan_interest_month').text(value['total_loan_interest_month']);
                            $('#total_loan_interest_year').text(value['total_loan_interest_year']);

                        } else if (value['id'] === "total_loan_processing_fee") {
                            $('#total_loan_processing_fee_month').text(value['total_loan_processing_fee_month']);
                            $('#total_loan_processing_fee_year').text(value['total_loan_processing_fee_year']);

                        } else if (value['id'] === "joining_fee") {
                            $('#joining_fee_month').text(value['joining_fee_month']);
                            $('#joining_fee_year').text(value['joining_fee_year']);

                        } else if (value['id'] === "rollover_fee") {
                            $('#rollover_fee_month').text(value['rollover_fee_month']);
                            $('#rollover_fee_year').text(value['rollover_fee_year']);

                        } else if (value['id'] === "total_income") {
                            $('#total_income_month').text(value['total_income_month']);
                            $('#total_income_year').text(value['total_income_year']);

                        }

                    })

                    $('.income_statement').addClass('hide')
                    $('#current_month').text(json['current_month']);



                    // $('#total_interest').text(json.data['total_interest']);
                    // $('#total_paid_interest').text(json.data['total_paid_interest']);

                }
            });

        }

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
                data: function (d) {
                    d.month = $('select[name=month]').val();
                },
            },
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'disbursement_target_amount'},
                {data: 'actual_disbursement_amount'},
                {data: 'disbursement_achieved'},
                {data: 'collection_target'},
                {data: 'actual_collection'},
                {data: 'collection_achieved'},
                {data: 'customer_target'},
                {data: 'customer_enrolled'},
                {data: 'customer_target_achieved'},
                {data: 'average_performance'},
            ],
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api(), data;

                // converting to interger to find total
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                // computing column Total of the complete result
                var dis_target = api
                    .column(2)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                var dis_targetData = api
                    .column(2)
                    .data();
                var actual_dis = api
                    .column(3)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                var dis_achieved = api
                    .column(4)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                var dis_achievedData = api
                    .column(4)
                    .data();
                var CT = api
                    .column(5)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                var AC = api
                    .column(6)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                var AC_achieved = api
                    .column(7)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var AC_achievedData = api
                    .column(7)
                    .data();
                /*  var due = api
                      .column( 8 )
                      .data()
                      .reduce( function (a, b) {
                          return intVal(a) + intVal(b);
                      }, 0 );*/


                //customer target
                var cust_target = api
                    .column(8)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var cust_target_data = api
                    .column(8)
                    .data();

                var cust_onboarded = api
                    .column(9)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var cust_onboarded_data = api
                    .column(9)
                    .data();

                var cust_target_achieved = api
                    .column(10)
                    .data();

                var cust_target_achieved_data = api
                    .column(10)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var average = api
                    .column(11)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                var averageData = api
                    .column(11)
                    .data();


                var customer_target = cust_target / cust_target_data.count();
                var dt = dis_target / dis_targetData.count();


                // var averageData = (Math.floor((actual_dis / dis_target)*100) + Math.floor((AC / CT)* 100))/2;
                // var averageData2 = api
                //     .column( 8 )
                //     .data();
                // Update footer by showing the total with the reference of the column index
                $(api.column(2).footer()).html(dis_target /*/ dis_targetData.count()*/);
                $(api.column(3).footer()).html(actual_dis);
                $(api.column(4).footer()).html(Math.round((actual_dis / dis_target) * 100));
                $(api.column(5).footer()).html(CT);
                $(api.column(6).footer()).html(AC);
                // $( api.column( 7 ).footer() ).html(AC_achieved / AC_achievedData.count());
                $(api.column(7).footer()).html(Math.round((AC / CT) * 100));

                //$( api.column( 8 ).footer() ).html((Math.round((actual_dis / dis_target)*100 + (AC / CT)* 100))/ 2);
                $(api.column(8).footer()).html(cust_target);
                $(api.column(9).footer()).html(cust_onboarded);
                $(api.column(10).footer()).html(Math.round((cust_onboarded / cust_target) * 100));


                $(api.column(11).footer()).html(Math.round((Math.floor((actual_dis / dis_target) * 100 + (AC / CT) * 100 + (cust_onboarded / cust_target) * 100)) / 3));

            },
        });

        $('#month').on('change', function (e) {
            oTable.clear().draw();
            oTable.columns.adjust().draw();
        });


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

        function format(d) {
            console.log(d);

            function getCustomerData() {
                //loop through predictions array
                var data = d.customer_data;
                var dataArray = [];
                for (var index = 0; index < data.length; ++index) {
                    var contact = data[index].contact;
                    var balance = data[index].balance;
                    var amount = data[index].amount;
                    var str = '';
                    var value = str.concat('<tr>', '<td>', contact, '</td>', '<td>', balance, '</td>', '<td>', amount);
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
                'colvis', 'pageLength'],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            ajax: {
                url: '{!! route('monthly_collection_performance_data', ['id'=>$user->id]) !!}',
                data: function (d) {
                    d.collection_month = $('select[name=collection_month]').val();
                },
            },
            columns: [
                {
                    className: 'details-control',
                    orderable: false,
                    data: null,
                    defaultContent: ''
                },
                {data: 'loans'},
                {data: 'loans_due'},
                {data: 'loans_complete'},
                {data: 'percentage'},
                /*  { data: 'action', name: 'action', orderable: false, searchable: false }
  */
            ],
        });
        $('#collection_month').on('change', function (e) {
            collection.clear().draw();
            collection.columns.adjust().draw();
        });
        $('#collection tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = collection.row(tr);
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
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
                'colvis', 'pageLength'],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            ajax: {
                url: '{!! route('co_income_data', ['id'=>$user->id]) !!}',
                data: function (d) {
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
        $('#income_month').on('change', function (e) {
            income.clear().draw();
            income.columns.adjust().draw();
        });
    </script>
@stop
