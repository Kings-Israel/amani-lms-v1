@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card z-depth-bottom-2">
                <div class="card-block">
                    <form class="form-inline row" method="post" action="{{route('cash_flow_statement')}}">
                        @csrf
                        <div class="col-md-6">
                            <label>Fiscal Year</label>

                            <div class="input-group">

                                <span class="input-group-addon" id="basic-addon1">
<i class="icofont icofont-ui-calendar"></i>
</span>
                                <select name="year" id="year" class="form-control{{ $errors->has('year') ? ' is-invalid' : '' }}">
                                </select>

                            </div>
                            @if ($errors->has('year'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('year') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label>Month</label>
                            <div class="input-group">

                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select name="month" class="form-control{{ $errors->has('month') ? ' is-invalid' : '' }}">
                                    @foreach($months as $month)
                                        <option value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>

                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('branch'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch') }}</strong>
                                    </span>
                            @endif
                        </div>


                        <div class="col-md-6">
                            <label>Branch</label>

                            <div class="input-group">

                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select name="branch" class="form-control{{ $errors->has('year') ? ' is-invalid' : '' }}">
                                    @foreach($branches as $branch)
                                        {{--<option value="{{$branch->id}}">{{$branch->bname}}</option>--}}
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

                        <div class="col-md-4 ">
                            <button class="btn btn-grd-primary ">View Report</button>
                        </div>


                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table {{--table-striped--}} table-bordered  table-styling">
                            <thead class="table-primary">
                            <tr>
                                                              <th colspan="3">Fiscal Year: {{$current_year}} / <span style="text-transform: uppercase">{{$current_month}}</span></th>


                            </tr>
                            </thead>
                            <tbody>
                          {{-- <tr>
                               <th colspan="3">MARCH</th>
                           </tr>--}}
                            <tr>
                                <th rowspan="6">Cash Inflows</th>
                                <td>Balance b/d</td>
                                <td>{{number_format($balance_bd, 2)}}</td>
                           <tr>
                               <td>Loans Collections</td>
                               <td>{{number_format($total_loan_collections, 2)}}</td>
                           </tr>
                          <tr>
                              <td>Loans Processing Fee</td>
                              <td>{{number_format($total_processing_fee, 2)}}</td>
                          </tr>
                          <tr>
                              <td>Registration Fee</td>
                              <td>{{number_format($total_registration_fee, 2)}}</td>
                          </tr>
                          <tr>
                              <td>Investments</td>
                              <td>{{number_format($investments, 2)}}</td>
                          </tr>
                           <tr>
                               <th>Total Cash Inflows</th>
                               <th>{{number_format($total_cash_inflows, 2)}}</th>
                           </tr>


                            </tr>

                           <tr>
                               <th rowspan="3">Cash Outflows</th>
                               <td>Loan Disbursement</td>
                               <td>{{number_format($getTotalLoanDisbursement, 2)}}</td>
                           <tr>
                               <td>Expenses</td>
                              <td>{{number_format($total_expenses, 2)}}</td>
                           </tr>
                           <tr>
                               <th>Total Cash Outflows</th>
                               <th>{{number_format($total_cash_outflows, 2)}}</th>
                           </tr>
                          <tr style="font-style: italic">
                              <th colspan="2">Net Cashflow</th>
                              <th>{{number_format($net_cash_inflows, 2)}}</th>
                          </tr>


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
    <script>
        var start = 2019;
        var end = new Date().getFullYear();
        var options = "";
        for(var year = start ; year <=end; year++){
            options += "<option>"+ year +"</option>";
        }
        document.getElementById("year").innerHTML = options;
    </script>


@stop
