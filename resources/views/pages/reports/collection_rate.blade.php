@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card z-depth-bottom-2">
                <div class="card-block">
                    <form class="form-inline row" method="get" action="{{route('collection_rate_post')}}">
                        @csrf
                        @hasrole('admin')

                        <div class="col-md-4">
                            <div class="input-group">

                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select name="branch" class="form-control{{ $errors->has('year') ? ' is-invalid' : '' }}">

                                    @foreach($branches as $branch)
                                        <option value="{{$branch->id}}" >{{$branch->bname}}</option>
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
                               class="table table-striped table-bordered nowrap ">
                            <thead class="table-primary">
                            <tr>
                                @foreach($data as $dt)

                                <th style="color: white">{{$dt["category"]}}</th>
                                @endforeach



                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($data as $dt)

                                    <th>{{number_format($dt["percentage"], 2)}} %</th>
                                    @endforeach




                                </tr>
                            </tbody>
                            <tfoot>
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
        var start = 2019;
        var end = new Date().getFullYear();
        var options = "";
        for(var year = start ; year <=end; year++){
            options += "<option>"+ year +"</option>";
        }
        document.getElementById("year").innerHTML = options;
    </script>


@stop
