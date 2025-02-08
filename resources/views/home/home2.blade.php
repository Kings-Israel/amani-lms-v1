@extends("layouts.master")
@section('css')
    <link rel="stylesheet" href="{{asset("bower_components/chartist/css/chartist.css")}}" type="text/css" media="all">
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
        .hide {
            display: none;
        }
    </style>
@stop

@section("content")
    {{-- Online Users --}}
    <div class="row mb-1">
        <div class="col-md-10">
            @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin'))
                <a style="float: left" class="btn btn-sm btn-outline-success" href="{{route('admin.view_users_last_seen')}}"><b><span id="online"></span>  Online System Users <i class="feather icon-user-check"></i></b></a>
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
                            {{-- <h4 class="text-white" id="total_customers"> --}}
                            <!-- <h4 class="text-white" id="customer_info_month"> -->
                            <h4 class="text-white" id="customer_info_new">
                                <p class="preloader4" style="margin: 0px">
                                    <span class="double-bounce1"></span>
                                    <span class="double-bounce2"></span>
                                </p>
                            </h4>
                            <h6 class="text-white m-b-0">Total Customers</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-users-alt-5 icon-dashboard" ></i>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <h6 class="text-white m-b-0"><i class="feather icon-user-check text-white f-14 m-r-10"></i>New Customers <span id="customer_info_month"></span>, <span id="customer_info_new"></span></h6>
                </div>
            </div>
        </div>




        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('field_agent'))

        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-pink update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white">{{ number_format($commission) }}</h4>
                            <h6 class="text-white m-b-0">Commission</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-coins icon-dashboard"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0">
                        <i class="feather icon-clipboard text-white f-14 m-r-10"></i>
                        Monthly Commission: <span id="monthly_commission">{{ number_format($commission) }}</span>
                    </h6>
                </div>
            </div>
        </div>

        {{-- <div class="col-xl-3 col-md-6">
            <div class="card bg-c-pink update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white" id="total_commission">
                                <p class="preloader4" style="margin: 0px">
                                    <span class="double-bounce1"></span>
                                    <span class="double-bounce2"></span>
                                </p>
                            </h4>
                            <h6 class="text-white m-b-0">Commission</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-coins icon-dashboard"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0">
                        <i class="feather icon-clipboard text-white f-14 m-r-10"></i>
                        Monthly Commission: <span id="active_loansss"></span>
                    </h6>
                </div>
            </div>
        </div> --}}


        @endif


        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('accountant'))


        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-blue update-card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-white" id="rcustomer_info_month">
                                {{ $repeat_applicants_count }}
                                {{-- <p class="preloader4" style="margin: 0px;">
                                    <span class="double-bounce1"></span>
                                    <span class="double-bounce2"></span>
                                </p> --}}
                            </h4>
                            <h6 class="text-white m-b-0">Reapplicant Customers</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-users-alt-5 icon-dashboard"></i>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <h6 class="text-white m-b-0">
                        <i class="feather icon-user-check text-white f-14 m-r-10"></i>
                        Reapplicant Customers <span id="rcustomer_info_month"></span>,
                        <span id="customer_info_new"></span>
                    </h6>
                </div>
            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-pink update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white" id="mtd_loans_amount">
                                <p class="preloader4" style="margin: 0px">
                                    <span class="double-bounce1"></span>
                                    <span class="double-bounce2"></span>
                                </p>
                            </h4>
                            <h6 class="text-white m-b-0" >Disbursed Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-coins icon-dashboard"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0"><i class="feather icon-clipboard text-white f-14 m-r-10"></i>Active Loans: <span id="active_loans"></span> </h6>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-orenge update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white" id="total_arrears">
                                <p class="preloader4" style="margin: 0px">
                                    <span class="double-bounce1"></span>
                                    <span class="double-bounce2"></span>
                                </p>
                            </h4>
                            <h6 class="text-white m-b-0">Total Arrears</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-stock-mobile icon-dashboard"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0"><i class="icofont icofont-briefcase-alt-2 text-white m-r-10"></i>Arrears Due Count: <span id="arrears_count"></span></h6>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-lite-green update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white" id="loans_due_today"></h4>
                            <h6 class="text-white m-b-0">Loans Due Today</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-meeting-add icon-dashboard"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <h6 class="text-white m-b-0"><i class="icofont icofont-bill-alt text-white m-r-10"></i>Amount: Ksh.
                        <span id="due_today_amount">
                            <p class="preloader4 " style="margin: 0px">
                                <span class="double-bounce1 loader-danger"></span>
                                <span class="double-bounce2 loader-danger"></span>
                            </p>
                        </span>
                    </h6>
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
                            <i class="icofont icofont-chart-bar-graph f-40 text-c-pink"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">Applied Amount</h6>
                            {{-- <span id="applied_amount"></span> --}}

                            <h6 class="m-b-0">Ksh. <span id="applied_amount"></span></h6>
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
                            <i class="icofont icofont-book-alt f-30 text-c-pink"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">MTD Disbursed Loans</h6>
                            <h6 class="m-b-0"> Count: <span id="mtd_loans"></span></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center m-l-0">
                        <div class="col-auto">
                            {{--<i class="ion-connection-bars f-30 text-c-blue"></i>--}}
                            <i class="icofont icofont-signal f-30 text-c-blue"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">Total Balance</h6>
                            <h6 class="m-b-0">Ksh. <span id="loan_amount"></span></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center m-l-0">
                        <div class="col-auto">
                            <i class="icofont icofont-signal f-30 text-c-blue"></i>
                        </div>
                        <div class="col-auto">
                            <h6 class="text-muted m-b-10">Total Balance</h6>
                            <h6 class="m-b-0">Ksh. <span> {{ number_format($loan_balance, 0) }}  </span></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    {{-- <div class="row">
        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('accountant'))
            <div class="col-md-6" style="margin-bottom: 10px">
                <form method="post" action="{{route('home.post')}}">
                    @csrf
                    <label for="'branch">Select Branch</label>
                        <select name='branch' id='branch' class="form-control" >
                        <option value="all">All</option>
                        @foreach($branches as $branch)
                            <option value="{{$branch->id}}" {{($branch->id == $current_branch ) ? 'selected' : ''}}>{{$branch->bname}}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="col-md-6" style="margin-bottom: 10px">
                <form method="post" action="{{route('home.post')}}">
                    @csrf
                    <label for="field_agent">Select Credit-Officer</label>
                    <select name='field_agent' id="field_agent" class="form-control"  >
                        <option value="all">All</option>
                        @foreach($field_agents as $field_agent)
                            <option value="{{$field_agent->id}}" {{($field_agent->id == $current_officer ) ? 'selected' : ''}}>{{$field_agent->name}} - ({{$field_agent->branch}} Branch)</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif
    </div> --}}
    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('accountant'))
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Monthly Disbursement Data : {{\Carbon\Carbon::now()->format('F - Y')}}</h5>
                    <span>Graph displaying Loan Disbursements and Total Disbursed Amount</span>
                    <div class="card-header-right">
                        <ul class="list-unstyled card-option">
                            {{-- <li>
                                <button id="toggleView" class="btn btn-secondary">Switch to Weekly</button>
                            </li> --}}
                            <li>
                                <button id="downloadChart" class="btn btn-success">Download Chart</button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-block">
                    <div class="preloader3 loader-block myAreaChart" style="height: 0">
                        <div class="circ1 loader-danger"></div>
                        <div class="circ2 loader-danger"></div>
                        <div class="circ3 loader-danger"></div>
                        <div class="circ4 loader-danger"></div>
                    </div>
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
                            <h4 class="m-b-0" id="pending_approval"></h4>
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
                            <h4 class="m-b-0" id="pending_disbursements"></h4>
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
                            <h4 class="m-b-0" id="repayment_rate"></h4>
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
                            <h4 class="m-b-0" id="PAR">

                                <p class="preloader4" style="margin: 0px">
                                    <span class="double-bounce1"></span>
                                    <span class="double-bounce2"></span>
                                </p>
                            </h4>
                        </div>
                        <div class="col col-auto text-right">
                            <i class="icofont icofont-chart-bar-graph f-40 text-c-orenge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('accountant'))
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Monthly Loan Repayment Data: {{ \Carbon\Carbon::now()->format('F - Y') }}</h5>
                        <span>Graph displaying Loan Repayments and Total Loan Repayment Amounts</span>
                        <span><b>Repayments Include both loan settlements and loan processing fees</b></span>
                        <div class="card-header-right">
                            <ul class="list-unstyled card-option">
                                <li>
                                    <a href="{{ route('home.repayments_filter') }}"
                                    title="Filter loan repayment records by past months"
                                    class="btn btn-primary">
                                    Filter <i class="feather icon-search text-white"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-block">
                        <div class="preloader3 loader-block myRepaymentsChart" style="height: 0">
                            <div class="circ1 loader-danger"></div>
                            <div class="circ2 loader-danger"></div>
                            <div class="circ3 loader-danger"></div>
                            <div class="circ4 loader-danger"></div>
                        </div>
                        <canvas id="myRepaymentsChart" width="100%" height="40"></canvas>

                        <!-- Download Button for Chart -->
                        <button id="downloadChart" class="btn btn-success mt-3">Download Chart as PNG</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('accountant'))
      <div class="row justify-content-center">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center m-l-0">
                            <div class="col-auto">
                                <h6 class="text-muted m-b-10">Total Collected ({{ now()->format('d M Y') }})</h6>
                                <h5 class="m-b-0"><span id="total-payments"></span></h5>
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
                                <h6 class="text-muted m-b-10">Total Target ({{ now()->format('d M Y') }})</h6>
                                <h5 class="m-b-0"><span id="total-target"></span></h5>
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
                                <h6 class="text-muted m-b-10">Performance ({{ now()->format('d M Y') }})</h6>
                                <h5 class="m-b-0"><span id="total-performance"></span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('accountant') || \Illuminate\Support\Facades\Auth::user()->hasRole('sector_manager'))

        <!-- <div class="card">
            <div class="card-block">
                <h5 class="my-2">Field Agent Collection/Target Ratio</h5>
                <form id="search" class="form-inline row" method="post" action="">
                    @csrf
                    <div class="col-md-3">
                        <label for="branch">BRANCH</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i
                                    class="icofont icofont-bank-alt"></i></span>
                            <select
                                class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}"
                                name="branch_id" required>
                                <option value="all" selected> All Branches </option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @if($branch->id == $active_branch) selected @endif> {{ $branch->bname }}</option>
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
                        <label for="branch">Select Loan Officer</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                            <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                <option value="all" > All </option>
                                @foreach($field_agents as $lf)
                                    <option  value="{{$lf->id}}" > {{$lf->name}} </option>
                                @endforeach
                            </select>
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="col-md-2">
                        <label>Start Date</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1">
                                <i class="icofont icofont-ui-calendar"></i>
                            </span>
                            <input type="text" value="" id="date" autocomplete="off" name="date" value="" class="datepicker form-control">
                        </div>
                        @if ($errors->has('date'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('date') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="col-md-2">
                        <label>End Date</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                            <input value="" type="text" id="end_date" autocomplete="off" name="end_date" value="" class="datepicker form-control">
                        </div>
                        @if ($errors->has('end_date'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('end_date') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary">Filter</button>
                    </div>
                </form>
                <div class="dt-responsive table-responsive">
                    <table id="field-agents-collection-report" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>Field Agent</th>
                                <th>Collected</th>
                                <th>Target</th>
                                <th>Performance</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th>Field Agent</th>
                                <th>Collected</th>
                                <th>Target</th>
                                <th>Performance</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div> -->
        <div class="card">
            <div class="card-block">
                <h5 class="my-2">Field Agent Collection/Target Ratio</h5>
                <form id="search" class="form-inline row" method="post" action="">
                    @csrf
                    <div class="col-md-3">
                        <label for="branch">BRANCH</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                            <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                <option value="all" selected> All Branches </option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @if($branch->id == $active_branch) selected @endif> {{ $branch->bname }}</option>
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
                        <label for="branch">Select Loan Officer</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                            <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                <option value="all"> All </option>
                                @foreach($field_agents as $lf)
                                    <option value="{{$lf->id}}">{{$lf->name}}</option>
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
                        <label for="date">Date</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                            <input type="text" value="" id="date" autocomplete="off" name="date" class="datepicker form-control">
                        </div>
                        @if ($errors->has('date'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('date') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="col-md-1">
                        <button class="btn btn-primary">Filter</button>
                    </div>
                </form>
                <div class="dt-responsive table-responsive">
                    <table id="field-agents-collection-report" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>Field Agent</th>
                                <th>Collected</th>
                                <th>Target</th>
                                <th>Performance</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th>Field Agent</th>
                                <th>Collected</th>
                                <th>Target</th>
                                <th>Performance</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>



        <div class="card">
            <div class="card-block">
                <h5 class="my-2">Branch Collection/Target Ratio</h5>

                <div class="dt-responsive table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Branch Name</th>
                                <th>Daily Target (KES)</th>
                                <th>Daily Achieved (KES)</th>
                                <th>Percentage (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td @if($payment['branch_name'] == 'TOTAL') class="font-weight-bold" @endif>
                                        {{ $payment['branch_name'] }}
                                    </td>
                                    <td @if($payment['branch_name'] == 'TOTAL') class="font-weight-bold" @endif>
                                        {{ number_format($payment['daily_target'], 0) }}
                                    </td>
                                    <td @if($payment['branch_name'] == 'TOTAL') class="font-weight-bold" @endif>
                                        {{ number_format($payment['daily_achieved'], 0) }}
                                    </td>
                                    <td @if($payment['branch_name'] == 'TOTAL') class="font-weight-bold" @endif>
                                        {{ number_format($payment['percentage'], 2) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



    @endif
@stop

@section('js')
    <script src="{{asset("bower_components/raphael/js/raphael.min.js")}}"></script>
    <script src="{{asset("bower_components/morris.js/js/morris.js")}}"></script>
    <script src="{{asset("charts/Chart.min.js")}}"></script>
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>

    <script type="text/javascript">
        var $_base = '{{env('APP_URL')}}';
        var $_redirect = '';
        var $_current_url = '{{env('APP_URL')}}';
        var $_top_tipsters_ipp = 20;
        var  $user = null;

        var $current_branch_id = "{{$current_branch}}";
        var $current_field_agent_id = "{{$current_officer}}"
    </script>

<script>
    (function ($) {
        var charts = {
            currentView: 'monthly', // default view

            init: function () {
                // Set default font family and font color
                Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
                Chart.defaults.global.defaultFontColor = '#292b2c';

                this.bindUIActions();
                this.loadChartData();
            },

            bindUIActions: function () {
                var self = this;

                // Toggle between weekly and monthly data
                $('#toggleView').on('click', function () {
                    if (self.currentView === 'monthly') {
                        self.currentView = 'weekly';
                        $(this).text('Switch to Monthly');
                    } else {
                        self.currentView = 'monthly';
                        $(this).text('Switch to Weekly');
                    }
                    self.loadChartData(); // Reload the chart data
                });

                // Download chart as image
                $('#downloadChart').on('click', function () {
                    self.downloadChart();
                });
            },

            loadChartData: function () {
                var self = this;
                var endpoint = self.currentView === 'monthly' ? 'disbursement_chart_data' : 'disbursement_weekly_chart_data';
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "get",
                    url: $_base + endpoint, // Adjust URL based on the view
                    dataType: 'json',
                    data: {
                        branch: $current_branch_id,
                        field_agent_id: $current_field_agent_id
                    },
                    success: function (json) {
                        $('.myAreaChart').addClass('hide');
                        charts.createCompletedJobsChart(json);
                    }
                });
            },

            createCompletedJobsChart: function (data) {
                var ctx = document.getElementById("myAreaChart");
                var myLineChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.month || data.week, // Use month for monthly, week for weekly
                        datasets: [
                            {
                                label: "Loans Disbursed count",
                                type: 'line',
                                lineTension: 0.3,
                                backgroundColor: "rgba(244,176,64,0.78)",
                                borderColor: "rgba(219,141,13, 0.8)",
                                data: data.post_count_data
                            },
                            {
                                label: "Loans Amount (Ksh.)",
                                type: 'bar',
                                backgroundColor: "rgba(111,163,58, 0.8)",
                                borderColor: "rgba(85,125,45, 0.8)",
                                data: data.loan_amount
                            }
                        ]
                    },
                    options: {
                        scales: {
                            xAxes: [{
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
                                    max: data.max_disbursement,
                                    maxTicksLimit: 5
                                },
                                gridLines: {
                                    display: false
                                }
                            }, {
                                id: 'B',
                                position: 'left',
                                ticks: {
                                    min: 0,
                                    max: data.max_amount,
                                    maxTicksLimit: 5
                                },
                                gridLines: {
                                    color: "rgba(0, 0, 0, .125)"
                                }
                            }]
                        },
                        legend: {
                            display: true
                        }
                    }
                });
            },

            downloadChart: function () {
                var chart = document.getElementById("myAreaChart");
                var link = document.createElement('a');
                link.href = chart.toDataURL('image/png');
                link.download = 'disbursement_chart.png';
                link.click();
            }
        };

        charts.init();
    })(jQuery);
</script>

    <script>
        ( function ( $ ) {
            var repaymentsChart = {
                init: function () {
                    // &#45;&#45; Set new default font family and font color to mimic Bootstrap's default styling
                    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
                    Chart.defaults.global.defaultFontColor = '#292b2c';
                    this.ajaxGetPaymentsMonthlyData();
                },

                ajaxGetPaymentsMonthlyData: function () {
                    var payments_month_array;

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }, method: "get",
                        url: $_base + "ajax_repayment_chart_data",
                        dataType: 'json',
                        data: {
                            branch: $current_branch_id,
                            field_agent_id: $current_field_agent_id
                        },

                        success: function (json) {
                            payments_month_array = json;
                            //console.log(payments_month_array)
                            $('.myRepaymentsChart').addClass('hide')

                            repaymentsChart.createCompletedPaymentsChart( payments_month_array );
                        }
                    });
                   // var payments_month_array = <?php echo $payments_month_array ; ?>;
                },

                /**
                 * Created the Completed Payments Chart
                 */
                createCompletedPaymentsChart: function ( payments_month_array ) {
                    //alert(payments_month_array)
                    //console.log(payments_month_array)
                    var ctx = document.getElementById("myRepaymentsChart");
                    var myLineChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: payments_month_array.month, // The response got from the ajax request containing all month names in the database
                            datasets: [
                                {
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

                // Functionality to download chart as PNG
            document.getElementById('downloadChart').addEventListener('click', function() {
                var canvas = document.getElementById('myRepaymentsChart');
                var image = canvas.toDataURL('image/png', 1.0);
                var link = document.createElement('a');
                link.href = image;
                link.download = 'monthly-loan-repayments-chart.png';
                link.click();
            });

            repaymentsChart.init();
        } )( jQuery );
    </script>
 <script>
        jQuery(document).ready(function () {
            "use strict";
            jQuery('#date').datetimepicker({
                format:'Y-m-d'
            });
        })

        var oTable = $('#field-agents-collection-report').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: ':visible'
                    },
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'TABLOID'
                },
                'colvis', 'pageLength'
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],

            ajax: {
                url: '{!! route('home.field_agent_collection_report') !!}',
                data: function(d) {
                    d.lf = $('select[name=name]').val();
                    d.branch = $('select[name=branch_id]').val();
                    d.date = $('input[name=date]').val();
                }
            },
            order: [0, 'desc'],
            columns: [
                {
                    data: 'field_agent',
                    name: 'field_agent'
                },
                {
                    data: 'collected',
                    name: 'collected'
                },
                {
                    data: 'target',
                    name: 'target'
                },
                {
                    data: 'performance',
                    name: 'performance'
                },
            ],
        });

        $('#search').on('submit', function(e) {
            e.preventDefault();
            oTable.draw();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "get",
                url: "/ajax/field_agents_performance",
                dataType: 'json',
                data: {
                    lf: $('select[name=name]').val(),
                    branch: $('select[name=branch_id]').val(),
                    date: $('input[name=date]').val(),
                },
                success: function (json) {
                    $('#total-payments').text(new Intl.NumberFormat().format(json.payments))
                    $('#total-target').text(new Intl.NumberFormat().format(json.target))
                    $('#total-performance').text(json.performance + '%')
                }
            });
        });
    </script>

    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script type="text/javascript" src="{{ asset("assets/pages/dashboard/custom-dashboard.js") }}"></script>
    <script src="{{asset("assets/js/dashboard.js")}}"></script>
@endsection
