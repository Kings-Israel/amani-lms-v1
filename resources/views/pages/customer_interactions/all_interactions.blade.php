@extends("layouts.master")
@section("css")
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section("content")
    <div class="row justify-content-center">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="text-c-yellow f-w-600" id="total_interactions"></h4>
                            <h6 class="text-muted m-b-0">Total Interactions</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="feather icon-bar-chart f-28"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-yellow">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <p class="text-white m-b-0">Due <span id="due_interactions"></span></p>
                        </div>
                        <div class="col-6 text-right">
                            <p class="text-white m-b-0">Overdue <span id="overdue_interactions"></span></p>

                            {{--                            <i class="feather icon-trending-up text-white f-16"></i>--}}
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
                            <h4 class="text-c-green f-w-600" id="active_interactions"></h4>
                            <h6 class="text-muted m-b-0">Active</h6>
                        </div>
                        <div class="col-4 text-right">
                            {{--                            <i class="feather icon-file-text f-28"></i>--}}

                            <h4 class="text-c-green f-w-600" id="success_rate"></h4>

                            <h6 class="text-muted m-b-0">Success</h6>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-green">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <p class="text-white m-b-0">Closed: <span id="inactive_interactions"></span></p>
                        </div>
                        <div class="col-6 text-right">
                            <p class="text-white m-b-0">Success: <span id="success_interaction"></span></p>
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
                            <h4 class="text-c-pink f-w-600" id="this_month_interactions"></h4>
                            <h6 class="text-muted m-b-0"><span>Month</span>
                                Interactions</h6>
                        </div>
                        <div class="col-4 text-right">
                            <h4 class="text-c-pink f-w-600" id="monthly_success_rate"></h4>

                            <h6 class="text-muted m-b-0">Success</h6>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-pink">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <p class="text-white m-b-0"><span style="text-transform: uppercase"></span> closed: <span
                                    id="this_month_interactions_closed"></span></p>
                        </div>
                        <div class="col-6 text-right">
                            {{--                            <i class="feather icon-trending-up text-white f-16"></i>--}}
                            <select id="month" name="month" required>
                                <option value="1" {{$month == '01' ? "selected" : ""}}> JAN</option>
                                <option value="2" {{$month == '02' ? "selected" : ""}}> FEB</option>
                                <option value="3" {{$month == '03' ? "selected" : ""}}> MAR</option>
                                <option value="4" {{$month == '04' ? "selected" : ""}}> APR</option>
                                <option value="5" {{$month == '05' ? "selected" : ""}}> MAY</option>
                                <option value="6" {{$month == '06' ? "selected" : ""}}> JUN</option>
                                <option value="7" {{$month == '07' ? "selected" : ""}}> JUL</option>
                                <option value="8" {{$month == '08' ? "selected" : ""}}> AUG</option>
                                <option value="9" {{$month == '09' ? "selected" : ""}}> SEP</option>
                                <option value="10" {{$month == '10' ? "selected" : ""}}> OCT</option>
                                <option value="11" {{$month == '11' ? "selected" : ""}}> NOV</option>
                                <option value="12" {{$month == '12' ? "selected" : ""}}> DEC</option>


                            </select>

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
                            <h4 class="text-c-blue f-w-600" id="pre"></h4>
                            <h6 class="text-muted m-b-0">Unattended</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="feather icon-download f-28"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-c-blue">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <p class="text-white m-b-0">Due:<span id="pdue_interactions"></span></p>
                        </div>
                        <div class="col-6 text-right">
                            <p class="text-white m-b-0">Arrears:<span id="pre_arrears"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="row">
                <div class="col-md-12 mb-3">
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                </div>
                <div class="card-block">
                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                        <div class="col-md-3">
                            <label for="branch">Select Branch</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select id="branch_id"
                                        class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}"
                                        name="branch_id" required>
                                    <option value="all"> All</option>
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
                            <label for="branch">Select Officer</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select id="field_agent"
                                        class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                        name="name" required>
                                    <option value="all">All</option>
                                    @foreach($lfs as $lf)
                                        <option value="{{$lf->id}}"> {{$lf->name}} </option>
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
                            <label for="branch">Category</label>

                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select id="category"
                                        class="js-example-basic-single form-control{{ $errors->has('category') ? ' is-invalid' : '' }}"
                                        name="category" required>
                                    <option value="all">All</option>
                                    @foreach($categories as $category)
                                        <option value="{{$category->id}}"> {{$category->name}} </option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('category'))
                                <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('category') }}</strong>
                            </span>
                            @endif
                        </div>

                        <div class="col-md-2">
                            <label for="branch">Status</label>

                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i
                                        class="icofont icofont-bank-alt"></i></span>
                                <select id="status"
                                        class="js-example-basic-single form-control{{ $errors->has('status') ? ' is-invalid' : '' }}"
                                        name="status" required>
                                    <option value="all">All</option>
                                    <option value="1">Open</option>
                                    <option value="2">Closed</option>
                                    <option value="due">Due</option>
                                    <option value="overdue">OverDue</option>
                                </select>

                            </div>
                            @if ($errors->has('status'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('status') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </form>

                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Created By</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Customer</th>
                                <th>Phone</th>

                                <th>Remark</th>
                                <th>Status</th>
                                <th>Target</th>
                                <th>Due</th>


                                <th>Next Scheduled Interaction</th>
                                <th>Created At</th>
                                <th>Action</th>

                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Created By</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Customer</th>
                                <th>Phone</th>

                                <th>Remark</th>
                                <th>Status</th>
                                <th>Target</th>
                                <th>Due</th>


                                <th>Next Scheduled Interaction</th>
                                <th>Created At</th>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        (function () {


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
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],


                ajax: {
                    url: '{!! route('customer-interactions.customer_interactions_data', 'all') !!}',
                    data: function (d) {
                        d.branch = $('select[name=branch_id]').val();
                        d.lf = $('select[name=name]').val();
                        d.status = $('select[name=status]').val();
                        d.category = $('select[name=category]').val();


                    }
                },
                order: [0, 'desc'],

                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'uname', name: 'users.name'},
                    {data: 'tname', name: 'customer_interaction_types.name'},
                    {data: 'cname', name: 'customer_interaction_categories.name'},
                    {data: 'lname', name: 'customers.lname'},
                    {data: 'phone', name: 'customers.phone'},


                    {data: 'remark', name: 'remark'},
                    {data: 'status', orderable: false, searchable: false},
                    {data: 'target', orderable: false, searchable: false},
                    {data: 'instal_due', orderable: false, searchable: false},




                    {data: 'next_scheduled_interaction', name: 'next_scheduled_interaction'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', orderable: false, searchable: false},
                ],
            });

            $('#search').on('submit', function (e) {
                oTable.draw();
                e.preventDefault();
            });

        })();


        // call data on page load

        var $_base = '{{env('APP_URL')}}';
        var $_current_url = '{{env('APP_URL')}}';

        var $current_branch_id = "{{$current_branch}}";
        var $current_field_agent_id = "{{$current_officer}}"
        calls($current_branch_id, $current_field_agent_id);


        $('#field_agent').on('change', function () {
            var $current_field_agent_id = this.value;
            var $month = $("#month option:selected").val();

            calls('all', $current_field_agent_id, $month);


        });


        //branch change
        $('#branch_id').on('change', function () {
            var $current_branch_id = this.value;
            var $month = $("#month option:selected").val();


            calls($current_branch_id, 'all', $month);


        });

        //month on change
        $('#month').on('change', function () {
            var $month = this.value;
            var $current_branch_id = $("#branch_id option:selected").val();
            var $field_agent = $("#field_agent option:selected").val();


            calls($current_branch_id, $field_agent, $month);

            // $.ajax({
            //
            //
            //     headers: {
            //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            //
            //     },
            //     cache: false,
            //     method: "get",
            //     url: $_base + "ajax_interactions/all",
            //     dataType: 'json',
            //     data: {
            //         branch: $current_branch_id,
            //         month: $month,
            //         lf: $current_field_agent_id
            //
            //     },
            //     success: function (json) {
            //         $('#this_month_interactions').text(json.data['this_month_interactions']);
            //         $('#this_month_interactions_closed').text(json.data['this_month_interactions_closed']);
            //
            //     }
            // });


        });


        function calls($current_branch_id, $current_field_agent_id, $month) {

            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    // 'Cache-Control': 'no-cache, no-store, must-revalidate',

                },
                cache: false,
                method: "get",
                url: $_base + "ajax_interactions/all",
                dataType: 'json',
                data: {
                    branch: $current_branch_id,
                    lf: $current_field_agent_id,
                    month: $month


                },
                success: function (json) {
                    if (json.status === "success")
                        //.location.href = json.redirect;
                        $('#total_interactions').text(json.data['interactions']);
                    $('#active_interactions').text(json.data['active']);
                    $('#inactive_interactions').text(json.data['inactive']);
                    $('#due_interactions').text(json.data['due']);
                    $('#overdue_interactions').text(json.data['over_due']);
                    $('#pdue_interactions').text(json.data['pdue']);
                    // $('#poverdue_interactions').text(json.data['poverdue']);

                    $('#this_month_interactions').text(json.data['this_month_interactions']);
                    $('#this_month_interactions_closed').text(json.data['this_month_interactions_closed']);
                    $('#monthly_success_rate').text(json.data['monthly_success_rate']+"%");
                    $('#success_rate').text(json.data['success_rate']+"%");
                    $('#success_interaction').text(json.data['interactions_success']);
                    $('#pre_arrears').text(json.data['pre_arrears']);






                    $('#pre').text(json.data['pre']);


                }
            });
        }


    </script>

@stop
