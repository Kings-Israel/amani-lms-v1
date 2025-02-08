@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            <div class="card z-depth-bottom-2">
                <div class="card-block">
                    <form class="form-inline row" method="post" action="{{route('disbursed_loans_summary')}}">
                        @csrf
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                <input type="text" autocomplete="off" id="datetimepicker1" name="start_date" value="{{ $start_date }}" class="datepicker form-control{{ $errors->has('start_date') ? ' is-invalid' : '' }}" required>
                            </div>
                            @if ($errors->has('start_date'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('start_date') }}</strong>
                                    </span>
                            @endif
                        </div>to
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1">
                                    <i class="icofont icofont-ui-calendar"></i>
                                </span>
                                <input type="text" autocomplete="off" id="datetimepicker2" name="end_date" value="{{ $end_date }}" class="datepicker form-control{{ $errors->has('end_date') ? ' is-invalid' : '' }}" required>
                            </div>
                            @if ($errors->has('end_date'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('end_date') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="col-md-3 ">
                            <button class="btn btn-grd-primary ">View Report</button>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>Branch Name</th>
                                <th>Disbursement Amount</th>
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
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script>
        jQuery(document).ready(function () {
            "use strict";
            jQuery('#datetimepicker1, #datetimepicker2').datetimepicker({
                //format:'Y-m-d H:i'
                format: 'Y-m-d'
            });
        });
    </script>
@stop
