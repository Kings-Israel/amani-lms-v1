@extends("layouts.master")
@section('css')
    <link rel="stylesheet" href=".{{asset("bower_components/chartist/css/chartist.css")}}" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">

    <style>
        #update-nav {
            width: 100%;
            height: 30px;
        }
        #range-selector {
            width: 50%;
            float: left;
        }
        #date-selector {
            width: 50%;
            float: right;
        }
    </style>
@stop

@section("content")
    <div class="row mb-1">
        <div class="col-md-10">
            @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin'))
                <a style="float: left" class="btn btn-sm btn-outline-success" href="{{route('admin.view_users_last_seen')}}"><b>{{$online_count}} Online System Users <i class="feather icon-user-check"></i></b></a>
            @endif
        </div>
        <div class="col-md-2">
            <p style="float: right" class="text-black-50 m-b-5 m-t-5 m-r-15"><i class="feather icon-clock text-black-50 f-14 m-r-5"></i>updated : {{ date("H:i") }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-yellow update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white">{{$customers}}</h4>
                            <h6 class="text-white m-b-0">Total Customers</h6>
                        </div>
                        <div class="col-4 text-right">
                            {{--<canvas id="update-chart-1" height="50"></canvas>--}}
                            <i class="icofont icofont-users-alt-5 icon-dashboard" ></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0"><i class="feather icon-user-check text-white f-14 m-r-10"></i>New Customers, {{$customer_info[0][0]}} : {{$customer_info[0][1]}}</h6>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-pink update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white">{{number_format($totalAmount)}}</h4>
                            <h6 class="text-white m-b-0">Loans Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-coins icon-dashboard"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0"><i class="feather icon-clipboard text-white f-14 m-r-10"></i>Active Loans: {{$loans}}</h6></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-orenge update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white">{{number_format($arrears_total)}}</h4>
                            <h6 class="text-white m-b-0">Total Arrears</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-stock-mobile icon-dashboard"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0"><i class="icofont icofont-briefcase-alt-2 text-white m-r-10"></i>Arrears Due Count: {{$arrears_count}}</h6>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-lite-green update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white">{{$due_today_count}}</h4>
                            <h6 class="text-white m-b-0">Loans Due Today</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-meeting-add icon-dashboard"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0"><i class="icofont icofont-bill-alt text-white m-r-10"></i>Amount: Ksh. {{number_format($due_today_amount)}}</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center m-l-0">
                        <div class="col-auto">

                            <i class="icofont icofont-book-alt f-30 text-c-pink"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">MTD Disbursed Loans</h6>
                            <h6 class="m-b-0"> Count: {{$mtd_loans}}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center m-l-0">
                        <div class="col-auto">
                            {{--<i class="ion-connection-bars f-30 text-c-blue"></i>--}}
                            <i class="icofont icofont-signal f-30 text-c-blue"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">MTD Disbursed Amount</h6>

                            <h6 class="m-b-0">Ksh. {{number_format($mtd_loan_amount)}}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
    {{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-block">--}}
{{--                            <div class="row align-items-center m-l-0">--}}
{{--                                <div class="col-auto">--}}
{{--                                    <i class="ion-connection-bars f-30 text-c-blue"></i>--}}
{{--                                    <i class="icofont icofont-address-book f-30  text-c-pink"></i>--}}
{{--                                </div>--}}
{{--                                <div class="col-auto">--}}
{{--                                    <h6 class="text-muted m-b-10">Non-Performing</h6>--}}

{{--                                    <h6 class="m-b-0">Count: {{$non_performing_count}}</h6>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-block">--}}
{{--                            <div class="row align-items-center m-l-0">--}}
{{--                                <div class="col-auto">--}}
{{--                                    <i class="ion-connection-bars f-30 text-c-blue"></i>--}}
{{--                                    <i class="icofont icofont-abacus-alt f-30 text-c-red"></i>--}}
{{--                                </div>--}}
{{--                                <div class="col-auto">--}}
{{--                                    <h6 class="text-muted m-b-10">Non-Performing</h6>--}}

{{--                                    <h6 class="m-b-0">Ksh. {{number_format($non_performing_balance)}}</h6>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('accountant'))

        <div class="col-md-6" style="margin-bottom: 10px">
            <form method="post" action="{{route('home.post')}}">
                @csrf
                <label for="'branch">Select Branch</label>
            <select name='branch' id='branch' class="form-control" onchange="this.form.submit()">
                <option value="all">All</option>
                @foreach($branches as $branch)
                <option value="{{$branch->id}}" {{($branch->id == $current_branch ) ? 'selected' : ''}}>{{$branch->bname}}</option>
                    @endforeach
            </select>
            </form>
        </div>

        <div class="col-md-6" style="margin-bottom: 10px">
            <form method="post" action="{{route('home.post.loan.officer.filter')}}">
                @csrf
                <label for="field_agent">Select Credit-Officer</label>
                <select name='field_agent' id="field_agent" class="form-control" onchange="this.form.submit()" >
                    <option value="all">All</option>
                    @foreach($field_agents as $field_agent)
                        <option value="{{$field_agent->id}}" {{($field_agent->id == $current_officer ) ? 'selected' : ''}}>{{$field_agent->name}} - ({{$field_agent->branch}} Branch)</option>
                    @endforeach
                </select>
            </form>
        </div>

        @endif

    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Monthly Disbursement Data : {{\Carbon\Carbon::now()->format('F - Y')}}</h5>
                    <span>Graph displaying Loan Disbursements and Total Disbursed Amount</span>
                    <div class="card-header-right">
                        <ul class="list-unstyled card-option">
                            <li><a href="{{route('home.disb_filter')}}" title="Filter records by past months" class="btn btn-primary">Filter<i class="feather icon-search text-white"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-block">
                    <canvas id="myAreaChart" width="100%" height="40"></canvas>
                </div>

            </div>
        </div>
    </div>
    <div class="row justify-content-center">

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-5">Loans Pending Approval</h6>
                            <h4 class="m-b-0">{{$pending_approval}}</h4>
                        </div>
                        <div class="col col-auto text-right">
                          <i class="feather icon-user f-50 text-c-yellow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-5">Loans Pending Disbursements</h6>
                            <h4 class="m-b-0">{{$pending_disbursements}}</h4>
                        </div>
                        <div class="col col-auto text-right">
                             <i class="feather icon-credit-card f-40 text-c-orenge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-5">Repayment Rate</h6>
                            <h4 class="m-b-0">{{$repayment_rate}}%</h4>
                        </div>
                        <div class="col col-auto text-right">
                            <i class="feather icon-book f-40 text-c-blue"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-5">PAR percentage</h6>
                            <h4 class="m-b-0">{{$PAR}}%</h4>
                        </div>
                        <div class="col col-auto text-right">
                            <i class="icofont icofont-chart-bar-graph f-40 text-c-orenge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Monthly Loan Repayment Data : {{\Carbon\Carbon::now()->format('F - Y')}}</h5>
                    <span>Graph displaying Loan Repayments and Total Loan Repayment Amounts</span>
                    <span><b>Repayments Include both loan settlements and loan processing fees</b></span>
                    <div class="card-header-right">
                        <ul class="list-unstyled card-option">
                            <li><a href="{{route('home.repayments_filter')}}" title="Filter loan repayment records by past months" class="btn btn-primary">Filter<i class="feather icon-search text-white"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-block">
                    <canvas id="myRepaymentsChart" width="100%" height="40"></canvas>
                </div>

            </div>
        </div>
    </div>

    <div class="row justify-content-center">
{{--            <div class="col-md-9">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-header">--}}
{{--                        <h5>Customers</h5>--}}
{{--                        <span>Customer Statistics</span>--}}
{{--                    </div>--}}
{{--                    <div class="card-block">--}}
{{--                        <div id="morris-extra-areaa"></div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>



    @stop

@section('js')
{{--
    <script type="text/javascript" src="{{ asset("bower_components/chart.js/js/Chart.js") }}"></script>
--}}

    <script src="{{asset("bower_components/raphael/js/raphael.min.js")}}"></script>
    <script src="{{asset("bower_components/morris.js/js/morris.js")}}"></script>
    <script src="{{asset("charts/Chart.min.js")}}"></script>
    <script>
        ( function ( $ ) {

            var charts = {
                init: function () {
                    // -- Set new default font family and font color to mimic Bootstrap's default styling
                    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
                    Chart.defaults.global.defaultFontColor = '#292b2c';

                    this.ajaxGetPostMonthlyData();

                },

                ajaxGetPostMonthlyData: function () {
                    var month_array = <?php echo $month_array ; ?>;
                    console.log( month_array );
                    charts.createCompletedJobsChart( month_array );
                },

                /**
                 * Created the Completed Jobs Chart
                 */
                createCompletedJobsChart: function ( month_array ) {

                    var ctx = document.getElementById("myAreaChart");
                    var myLineChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: month_array.month, // The response got from the ajax request containing all month names in the database
                            datasets: [{
                                label: "Loans Disbursed count",
                                type: 'line',
                                lineTension: 0.3,
                                backgroundColor: "rgba(244,176,64,0.78)",
                                borderColor: "rgba(219,141,13, 0.8)",
                                pointBorderColor: "#fff",
                                pointBackgroundColor: "rgba(219,141,13, 0.8)",
                                pointRadius: 5,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: "rgba(219,141,13, 0.8)",
                                pointHitRadius: 20,
                                pointBorderWidth: 2,
                                yAxisID: 'A',
                                data: month_array.post_count_data // The response got from the ajax request containing data for the completed jobs in the corresponding months
                            },
                                {
                                    label: "Loans Amount (Ksh.)",
                                    type: 'bar',
                                    backgroundColor: "rgba(111,163,58, 0.8)",
                                    borderColor: "rgba(85,125,45, 0.8)",
                                    pointRadius: 5,
                                    pointBackgroundColor: "rgba(111,163,58, 0.8)",
                                    pointBorderColor: "rgba(255,255,255,0.8)",
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: "rgba(111,163,58, 0.8)",
                                    pointHitRadius: 20,
                                    pointBorderWidth: 2,
                                    yAxisID: 'B',
                                    data: month_array.loan_amount // The response got from the ajax request containing data for the completed jobs in the corresponding months
                                }
                            ],
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    time: {
                                        unit: 'date'
                                    },
                                    gridLines: {
                                        display: false
                                    },
                                    ticks: {
                                        maxTicksLimit: 7
                                    }
                                }],
                                yAxes: [{
                                    id:'A',
                                    position: 'right',
                                    ticks: {
                                        min: 0,
                                        max: month_array.max_disbursement, // The response got from the ajax request containing max limit for y axis
                                        maxTicksLimit: 5
                                    },
                                    gridLines: {
                                        display:false
                                    }
                                },
                                    {id:'B',
                                        position: 'left',
                                        ticks: {
                                            min: 0,
                                            max: month_array.max_amount, // The response got from the ajax request containing max limit for y axis
                                            maxTicksLimit: 5
                                        },
                                        gridLines: {
                                            color: "rgba(0, 0, 0, .125)",
                                        }
                                    },
                                ],
                            },
                            legend: {
                                display: true
                            }
                        }
                    });
                }
            };
            charts.init();

        } )( jQuery );
    </script>

    <script>
        ( function ( $ ) {
            var repaymentsChart = {
                init: function () {
                    // -- Set new default font family and font color to mimic Bootstrap's default styling
                    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
                    Chart.defaults.global.defaultFontColor = '#292b2c';
                    this.ajaxGetPaymentsMonthlyData();
                },

                ajaxGetPaymentsMonthlyData: function () {
                    var payments_month_array = <?php echo $payments_month_array ; ?>;
                    console.log( payments_month_array );
                    repaymentsChart.createCompletedPaymentsChart( payments_month_array );
                },

                /**
                 * Created the Completed Payments Chart
                 */
                createCompletedPaymentsChart: function ( payments_month_array ) {
                    var ctx = document.getElementById("myRepaymentsChart");
                    var myLineChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: payments_month_array.month, // The response got from the ajax request containing all month names in the database
                            datasets: [{
                                label: "Loans Repayments count",
                                type: 'line',
                                lineTension: 0.3,
                                backgroundColor: "rgba(111,163,58, 0.8)",
                                borderColor: "rgba(85,125,45, 0.8)",
                                pointBorderColor: "#fff",
                                pointBackgroundColor: "rgba(111,163,58, 0.8)",
                                pointRadius: 5,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: "rgba(111,163,58, 0.8)",
                                pointHitRadius: 20,
                                pointBorderWidth: 2,
                                yAxisID: 'A',
                                data: payments_month_array.post_count_data // The response got from the ajax request containing data for the completed jobs in the corresponding months
                            },
                                {
                                    label: "Loan Repayments Amount (Ksh.)",
                                    type: 'bar',
                                    backgroundColor: "rgba(219,141,13, 0.8)",
                                    borderColor: "rgba(244,176,64,0.78)",
                                    pointRadius: 5,
                                    pointBackgroundColor: "rgba(219,141,13, 0.8)",
                                    pointBorderColor: "rgba(255,255,255,0.8)",
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: "rgba(219,141,13, 0.8)",
                                    pointHitRadius: 20,
                                    pointBorderWidth: 2,
                                    yAxisID: 'B',
                                    data: payments_month_array.loan_amount // The response got from the ajax request containing data for the completed jobs in the corresponding months
                                }
                            ],
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    time: {
                                        unit: 'date'
                                    },
                                    gridLines: {
                                        display: false
                                    },
                                    ticks: {
                                        maxTicksLimit: 7
                                    }
                                }],
                                yAxes: [{
                                    id:'A',
                                    position: 'right',
                                    ticks: {
                                        min: 0,
                                        max: payments_month_array.max_payment, // The response got from the ajax request containing max limit for y axis
                                        maxTicksLimit: 5
                                    },
                                    gridLines: {
                                        display:false
                                    }
                                },
                                    {id:'B',
                                        position: 'left',
                                        ticks: {
                                            min: 0,
                                            max: payments_month_array.max_amount, // The response got from the ajax request containing max limit for y axis
                                            maxTicksLimit: 5
                                        },
                                        gridLines: {
                                            color: "rgba(0, 0, 0, .125)",
                                        }
                                    },
                                ],
                            },
                            legend: {
                                display: true
                            }
                        }
                    });
                }
            };
            repaymentsChart.init();
        } )( jQuery );
    </script>
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script type="text/javascript" src="{{ asset("assets/pages/dashboard/custom-dashboard.js") }}"></script>

    @stop
