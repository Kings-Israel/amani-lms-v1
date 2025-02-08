@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            <div class="card z-depth-bottom-2">
                <div class="card-block">
                    <form class="form-inline row" method="post" action="{{route('income_statement_v2')}}">
                        @csrf
                        <div class="col-md-2">
                            <label>Fiscal Year</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                <select name="year" id="year" class="form-control{{ $errors->has('year') ? ' is-invalid' : '' }}">
                                    @foreach($yrs as $yr)
                                        @if(\Carbon\Carbon::now()->format('Y') == $yr)
                                        <option value="{{$yr}}" selected>{{$yr}}</option>
                                        @else
                                            <option value="{{$yr}}">{{$yr}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('year'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('year') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="col-md-2">
                            <label>Month</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                                <select name="month" class="form-control{{ $errors->has('month') ? ' is-invalid' : '' }}">
                                    @foreach($months as $month)
                                        {{--<option value="{{$month[0]}}">{{$month[1]}}</option>--}}
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


                        <div class="col-md-2">
                            <label>Branch</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select name="branch" class="form-control{{ $errors->has('branch') ? ' is-invalid' : '' }}">
                                    <option value="all" {{("all" == $current_branch ) ? 'selected' : ''}}>All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{$branch->id}}" {{($branch->id == $current_branch ) ? 'selected' : ''}}>{{$branch->bname}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('branch'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="col-md-3">
                            <label>Credit Officer</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-users"></i></span>
                                <select name="co_id" class="form-control{{ $errors->has('co_id') ? ' is-invalid' : '' }}" required>
                                    <option value="all" {{("all" == $current_co ) ? 'selected' : ''}}>All</option>
                                    @foreach($credit_officers as $co)
                                        <option value="{{$co->id}}" {{($co->id == $current_co ) ? 'selected' : ''}}>{{$co->name}} - ({{$co->branch}} Branch)</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('co_id'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('co_id') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="col-md-1 ">
                            <button class="btn btn-sm btn-primary ">View Report</button>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped {{--table-bordered--}} nowrap">
                            <thead class="table-primary">
                            <tr>
                                <th colspan="2" style="color: white">Fiscal Year: {{$current_year}} / <span style="text-transform: uppercase">{{$current_month}}</span></th>
                                <th colspan="2" style="color: white">MPESA BALANCE: {{$utility_balance}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th>Gross Margin Group <hr></th>
                                <th>{{$current_month}} <hr></th>
                                <th>YTD <hr></th>
                            </tr>
                            @foreach($product as $data)
                                <tr>
                                    <td>{{$data[0]}} Loans Interest</td>
                                    <td>{{$data[1]}}</td>
                                    <td>{{$data[2]}}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <th>TOTAL Loans Interest</th>
                                <th>{{number_format($total_loan_interest, 2)}}</th>
                                <th>{{number_format($YTD_total_loan_interest, 2)}}</th>
                            </tr>
                            <tr>
                                <th>TOTAL Loans Processing Fee</th>
                                <th>{{number_format($processing_fee, 2)}}</th>
                                <th>{{number_format($YTD_loan_processing_fee, 2)}}</th>
                            </tr>
                            <tr>
                                <th>TOTAL Commission</th>
                                <th>{{number_format($joining_fee, 2)}}</th>
                                <th>{{number_format($YTD_joining_fee, 2)}}</th>
                            </tr>
                            <tr>
                                <th>TOTAL Rollover Fee</th>
                                <th>{{number_format($total_rollover_fee, 2)}}</th>
                                <th>{{number_format($YTD_total_rollover_interest, 2)}}</th>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <th><span style="text-transform: uppercase; font-style: italic">Total Income</span> </th>
                                <th><span style="font-style: italic">({{number_format($total_income, 2)}})</span><hr></th>
                                <th><span style="font-style: italic">({{number_format($YTD_Total_income, 2)}})</span><hr></th>
                            </tr>
                            </tbody>
                            {{--<tfoot>
                            <tr>
                                <th>TOTAL Loans Interest</th>
                                <th>{{number_format($total_loan_interest, 2)}}</th>
                            </tr>
                            </tfoot>--}}
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop


@section('js')
{{--    <script>--}}
{{--        var start = 2019;--}}
{{--        var end = new Date().getFullYear();--}}
{{--        var options = "";--}}
{{--        for(var year = start ; year <=end; year++){--}}
{{--            options += "<option>"+ year +"</option>";--}}
{{--        }--}}
{{--        document.getElementById("year").innerHTML = options;--}}
{{--    </script>--}}


@stop
