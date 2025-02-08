@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">

@stop

@section("content")
  {{--  <div class="row justify-content-center">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-yellow f-w-600" id="disbursed_amount"></h4>
                            <h6 class="text-muted m-b-0">Disbursed Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="feather icon-bar-chart f-28"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-yellow">
                    <div class="row align-items-center">
                        <div class="col-6">
                            --}}{{--                            <p class="text-white m-b-0">Due <span id="due_interactions"></span></p>--}}{{--
                        </div>
                        <div class="col-6 text-right">

                            --}}{{--                                                        <i class="feather icon-trending-up text-white f-16"></i>--}}{{--
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-green f-w-600" id="total_amount"></h4>
                            <h6 class="text-muted m-b-0">Total Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="feather icon-file-text f-28"></i>

                            --}}{{--                            <h4 class="text-c-green f-w-600" id="success_rate"></h4>--}}{{--

                            --}}{{--                            <h6 class="text-muted m-b-0">Success</h6>--}}{{--
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-green">
                    <div class="row align-items-center">
                        --}}{{--                        <div class="col-6">--}}{{--
                        --}}{{--                            <p class="text-white m-b-0">Closed: <span id="inactive_interactions"></span></p>--}}{{--
                        --}}{{--                        </div>--}}{{--
                        --}}{{--                        <div class="col-6 text-right">--}}{{--
                        --}}{{--                            <p class="text-white m-b-0">Success: <span id="success_interaction"></span></p>--}}{{--
                        --}}{{--                        </div>--}}{{--
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-blue f-w-600" id="collected_amount"></h4>
                            <h6 class="text-muted m-b-0">Collected Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            --}}{{--                            <i class="feather icon-download f-28"></i>--}}{{--
                            <span id="percentage_collection"></span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-blue">
                    <div class="row align-items-center">
                        --}}{{--                        <div class="col-6">--}}{{--
                        --}}{{--                            <p class="text-white m-b-0">Due:<span id="pdue_interactions">152</span></p>--}}{{--
                        --}}{{--                        </div>--}}{{--
                        --}}{{--                        <div class="col-6 text-right">--}}{{--
                        --}}{{--                            <p class="text-white m-b-0">Arrears:<span id="pre_arrears">1124</span></p>--}}{{--
                        --}}{{--                        </div>--}}{{--
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-pink f-w-600" id="defaulted_amount"></h4>
                            <h6 class="text-muted m-b-0">Defaulted Amount</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="feather icon-activity f-28"></i>

                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-pink">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <p class="text-white m-b-0"><span style="text-transform: uppercase"></span> Defaulted Count: <span
                                    id="defaulted_count"></span></p>
                        </div>
                        <!--                        <div class="col-6 text-right">
                                                                                <i class="feather icon-trending-up text-white f-16"></i>

                                                </div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>--}}
 {{-- <div class="row">
      <a class="btn btn-primary" href="http://127.0.0.1:8000/admin/create">Add New</a>

  </div>--}}


    <div class="row">
        <div class="col-sm-12">

            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <a class="btn btn-primary" href="{{route('lead.create')}}">Add New</a>
                    <a class="btn btn-success" href="{{ route('import_lead') }}">Import Excel</a>

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
                                    <option value="all"> All Branches</option>
                                    @foreach($branches as $brach)
                                        <option value="{{$brach->id}}"> {{$brach->bname}} </option>
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
                            <label for="branch">Loan Officer</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select
                                    class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                    name="name" required>
                                    <option value="all">All Credit Officers</option>
                                    @foreach($lfs as $lf)
                                        <option value="{{$lf->id}}"> {{$lf->name}} - ({{$lf->branch}})</option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('name'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                            @endif
                        </div>
<!--
                        <div class="col-md-2">
                            <label for="month">Select Month <small> - ({{\Carbon\Carbon::now()->format('Y')}}
                                    )</small></label>
                            <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i
                                                class="icofont icofont-calendar"></i></span>
                                <select id="month" name="month"
                                        class="form-control{{ $errors->has('month') ? ' is-invalid' : '' }}">

                                </select>
                            </div>
                            @if ($errors->has('branch'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label for="collection_year">Select Year </label>
                            <div class="input-group">
                                                <span class="input-group-addon" id="basic-addon1"><i
                                                        class="icofont icofont-calendar"></i></span>
                                <select id="collection_year" name="collection_year"
                                        class="form-control{{ $errors->has('collection_year') ? ' is-invalid' : '' }}">

                                </select>
                            </div>
                            @if ($errors->has('collection_year'))
                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('collection_year') }}</strong>
                                                </span>
                            @endif
                        </div>

-->

                        <div class="col-md-2">
                            <input class="view btn btn-grd-primary" type="submit" value="Submit" onClick="mySubmit1()">

                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Business</th>
                                <th>Location</th>
                                <th>Qualified Amount</th>
                                <th>Officer</th>
                                <th>Branch</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Business</th>
                                <th>Location</th>
                                <th>Qualified Amount</th>
                                <th>Officer</th>
                                <th>Branch</th>

                                <th>Action</th>
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
    <script type="text/javascript" src="{{asset('bower_components/sweetalert/js/sweetalert.min.js')}}"></script>

    <script>
        var $_base = '{{env('APP_URL')}}';
        var $_current_url = '{{env('APP_URL')}}';

        var oTable = $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{extend: 'copyHtml5'}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'},
            },
                {
                    extend: 'pdfHtml5', /*exportOptions: {columns: ':visible'}*/
                    orientation: 'landscape',
                    pageSize: 'TABLOID'
                },
                'colvis', 'pageLength'],
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],

            ajax: {
                url: '{!! route('leads_data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();
                    d.lf = $('select[name=name]').val();

                }
            },
            columns: [

                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'phone_number', name: 'phone_number'},
                {data: 'type_of_business', name: 'type_of_business'},
                {data: 'location', name: 'location'},
                {data: 'estimated_amount', name: 'estimated_amount'},
                {data: 'officer', name: 'officer'},
                {data: 'branch', name: 'branch'},



                {data: 'action', name: 'action'},
            ],
            order: [0, 'desc']
        });


        $('#search').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });




    </script>

@stop
