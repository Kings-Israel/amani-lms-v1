@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">


@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            
                <div class="card">
                    <div class="card-block">
                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf
                    


                        <div class="col-md-4">
                            <label for="branch">BRANCH</label>

                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                <option value="all">All</option>
                                    @foreach($branches as $brach)

                                        <option
                                                value="{{$brach->id}}" >
                                            {{$brach->bname}}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                            @if ($errors->has('branch_id'))
                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch_id') }}</strong>
                                    </span>
                            @endif
                        </div>
                    
                        <div class="col-md-4">
                            <button class="btn btn-grd-primary">View Report</button>
                        </div>
                    </form>
                        <div class="dt-responsive table-responsive">
                            <table id="cbtn-selectors1"
                                   class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr>
                                    {{--<th>Check To Approve</th>--}}
                                    <th>Owner</th>
                                    <th>Product</th>
                                    <th>Branch</th>

                                    <th>Installments</th>
                                    <th>% Interest</th>
                                    <th>Amount</th>
                                    <th>Date Created</th>


                                </tr>
                                </thead>

                                <tfoot>
                                <tr>
                                    {{--<th>Check To Approve</th>--}}
                                    <th>Owner</th>
                                    <th>Product</th>
                                    <th>Branch</th>
                                    <th>Installments</th>
                                    <th>% Interest</th>
                                    <th>Amount</th>
                                    <th>Date Created</th>

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
                'colvis','pageLength'],


            //ajax: '{!! route('approve_loans.data') !!}',
            ajax: {
                url: '{!! route('approve_loans.data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();

                }
            },
            columns: [
                /* {data: 'checkbox', name: 'checkbox'},*/
                /*{ data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },*/

                {data: 'owner', name: 'owner'},
                {data: 'product_name', name: 'products.product_name'},
                {data: 'branch', name: 'branch'},
                {data: 'installments', name: 'products.installments'},
                {data: 'interest', name: 'interest'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'date_created', name: 'date_created'},

            ],
        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });


    </script>


@stop
