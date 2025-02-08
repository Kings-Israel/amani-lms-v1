@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            <div class="card z-depth-bottom-2">
                <div class="card-block">
                    <form class="form-inline row" method="post" action="{{route('loan_collections_per_month')}}">
                        @csrf
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                <select name="year" id="year" class="form-control{{ $errors->has('year') ? ' is-invalid' : '' }}">
                                    @foreach($years as $year)
                                        <option @if($current_year == $year) selected @endif value="{{$year}}">{{$year}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('year'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('year') }}</strong>
                                </span>
                            @endif
                        </div>
                        @hasanyrole('accountant|admin')
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select name="branch" class="form-control{{ $errors->has('branch') ? ' is-invalid' : '' }}">
                                    <option @if($current_branch == 'all') selected @endif value="all">All Branches</option>
                                @foreach($branches as $branch)
                                    <option @if($current_branch == $branch->id) selected @endif value="{{$branch->id}}">{{$branch->bname}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('branch'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch') }}</strong>
                                    </span>
                            @endif
                        </div>
                        @endrole
                        <div class="col-md-4 ">
                            <button class="btn btn-grd-primary ">View Report</button>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>Month Name</th>
                                <th>Repayment Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dt as $data)
                            <tr>
                                <td>{{$data[0]}}</td>
                                <td>{{$data[1]}}</td>
                            </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>TOTAL</th>
                                <th>{{number_format($mtotal, 2)}}</th>
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
        // var start = 2019;
        // var end = new Date().getFullYear();
        // var options = "";
        // for(var year = start ; year <=end; year++){
        //     options += "<option>"+ year +"</option>";
        // }
        // document.getElementById("year").innerHTML = options;
    </script>


@stop
