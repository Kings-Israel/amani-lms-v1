@extends('layouts.master')
@section('css')
    <link rel="stylesheet" href=".{{ asset('bower_components/chartist/css/chartist.css') }}" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.css') }}">

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

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-4 col-md-6">
            <div class="card  ">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="m-b-5">Total Repayment Transactions</p>
                            <h4 class="m-b-0">{{ $repaymentCount }}</h4>
                        </div>
                        <div class="col col-auto text-right">
                            <i class="feather icon-bar-chart f-50 text-c-pink"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="m-b-5">Total Repayment Amount</p>
                            <h4 class="m-b-0">Ksh. {{ number_format($repaymentAmount) }}</h4>
                        </div>
                        <div class="col col-auto text-right">
                            <i class="feather icon-bookmark f-50 text-c-yellow"></i>
                        </div>
                    </div>
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
                            <h6 class="text-muted m-b-10">Loan Processing Fee</h6>
                            <h6 class="m-b-0"> Count: {{ $loan_processing_fee_count }}</h6>
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
                            {{-- <i class="ion-connection-bars f-30 text-c-blue"></i> --}}
                            <i class="icofont icofont-signal f-30 text-c-blue"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">Loan Processing Fee Amount</h6>

                            <h6 class="m-b-0">Ksh. {{ number_format($loan_processing_fee_amount) }}</h6>
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
                            <i class="ion-connection-bars f-30 text-c-blue"></i>
                            <i class="icofont icofont-address-book f-30  text-c-pink"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">Loan Settlements</h6>

                            <h6 class="m-b-0">Count: {{ $loan_settlements_count }}</h6>
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
                            <i class="ion-connection-bars f-30 text-c-blue"></i>
                            <i class="icofont icofont-abacus-alt f-30 text-c-red"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">Loan Settlement Amounts</h6>
                            <h6 class="m-b-0">Ksh. {{ number_format($loan_settlements_amount) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            @include('layouts.alert')
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Monthly Loan Repayment Data - breakdown for {{ $current }}</h5>
                    <span>Graph displaying Loan Repayments and Total Loan Repayment Amounts</span>
                    <span><b>Repayments Include both loan settlements and loan processing fees</b></span>
                    <div class="card-header-right">
                    </div>
                </div>
                <div class="card-block">
                    @if (!auth()->user()->hasRole('admin') and !auth()->user()->hasRole('accountant'))
                        <div class="col-md-4" style="margin-bottom: 10px">
                            <form method="post" action="{{ route('home.post.repayments_filter') }}">
                                @csrf
                                <label for="'month">Select Month</label>
                                <select name='month' id='month' class="form-control" onchange="this.form.submit()">
                                    <option value="" disabled>Specify Period</option>
                                    @foreach ($months as $month)
                                        <option value="{{ $month }}" {{ $month == $current ? 'selected' : '' }}>
                                            {{ $month }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @elseif(auth()->user()->hasRole('admin') or auth()->user()->hasRole('accountant'))
                        <form class="form-inline row justify-content-center" method="post"
                            action="{{ route('home.post.repayments_filter') }}">
                            @csrf
                            <div class="col-md-3">
                                <label for="'month">Select Month</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-calendar"></i></span>
                                    <select name='month' id='month' class="form-control">
                                        <option value="" disabled>Specify Period</option>
                                        @foreach ($months as $month)
                                            <option value="{{ $month }}"
                                                {{ $month == $current ? 'selected' : '' }}>{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="branch_id">Select Branch</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-bank-alt"></i></span>
                                    <select
                                        class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}"
                                        name="branch_id" required>
                                        <option value="all"> All </option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ $branch->id == $current_branch ? 'selected' : '' }}>
                                                {{ $branch->bname }} </option>
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
                                <label for="lf">Select Field Agent</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-user-suited"></i></span>
                                    <select
                                        class="js-example-basic-single form-control{{ $errors->has('lf') ? ' is-invalid' : '' }}"
                                        name="lf" required>
                                        <option value="all"> All </option>
                                        @foreach ($lfs as $lf)
                                            <option value="{{ $lf->id }}"
                                                {{ $lf->id == $current_lf ? 'selected' : '' }}> {{ $lf->name }} -
                                                {{ $lf->branch }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if ($errors->has('lf'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('lf') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-1">
                                <button class="btn btn-primary">Filter</button>
                            </div>
                        </form>
                    @endif
                    <canvas id="myAreaChart" width="100%" height="40"></canvas>
                </div>

            </div>
        </div>
    </div>

@stop

@section('js')
    <script src="{{ asset('bower_components/raphael/js/raphael.min.js') }}"></script>
    <script src="{{ asset('bower_components/morris.js/js/morris.js') }}"></script>
    <script src="{{ asset('charts/Chart.min.js') }}"></script>
    <script>
        (function($) {

            var charts = {
                init: function() {
                    // -- Set new default font family and font color to mimic Bootstrap's default styling
                    Chart.defaults.global.defaultFontFamily =
                        '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
                    Chart.defaults.global.defaultFontColor = '#292b2c';

                    this.ajaxGetPostMonthlyData();
                },
                ajaxGetPostMonthlyData: function() {
                    var month_array = <?php echo $month_array; ?>;

                    charts.createCompletedJobsChart(month_array);
                },

                /**
                 * Created the Completed Jobs Chart
                 */
                createCompletedJobsChart: function(month_array) {

                    var ctx = document.getElementById("myAreaChart");
                    var myLineChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: month_array
                            .month, // The response got from the ajax request containing all month names in the database
                            datasets: [{
                                    label: "Loan Repayments count",
                                    type: 'line',
                                    lineTension: 0.3,
                                    backgroundColor: "rgba(111,163,58, 0.8)",
                                    borderColor: "rgba(111,163,58, 0.8)",
                                    pointBorderColor: "#fff",
                                    pointBackgroundColor: "rgba(111,163,58, 0.8)",
                                    pointRadius: 5,
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: "rgba(85,125,45, 0.8)",
                                    pointHitRadius: 20,
                                    pointBorderWidth: 2,
                                    yAxisID: 'A',
                                    data: month_array
                                        .post_count_data // The response got from the ajax request containing data for the completed jobs in the corresponding months
                                },
                                {
                                    label: "Loan Repayment Amount (Ksh.)",
                                    type: 'bar',
                                    backgroundColor: "rgba(219,141,13, 0.8)",
                                    borderColor: "rgba(219,141,13, 0.8)",
                                    pointRadius: 5,
                                    pointBackgroundColor: "rgba(219,141,13, 0.8)",
                                    pointBorderColor: "rgba(255,255,255,0.8)",
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: "rgba(219,141,13, 0.8)",
                                    pointHitRadius: 20,
                                    pointBorderWidth: 2,
                                    yAxisID: 'B',
                                    data: month_array
                                        .repayment_amount // The response got from the ajax request containing data for the completed jobs in the corresponding months
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
                                        id: 'A',
                                        position: 'right',
                                        ticks: {
                                            min: 0,
                                            max: month_array
                                            .max_repayment, // The response got from the ajax request containing max limit for y axis
                                            maxTicksLimit: 5
                                        },
                                        gridLines: {
                                            display: false
                                        }
                                    },
                                    {
                                        id: 'B',
                                        position: 'left',
                                        ticks: {
                                            min: 0,
                                            max: month_array
                                            .max_amount, // The response got from the ajax request containing max limit for y axis
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
        })(jQuery);
    </script>
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/pages/dashboard/custom-dashboard.js') }}"></script>
@stop
