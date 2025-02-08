@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-block">
                    @if(Auth::user()->hasRole('admin'))

                    <form id="search" class="form-inline row" method="post" action="">
                        @csrf



                        <div class="col-md-3">
                            <label for="branch">BRANCH</label>

                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
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


                        <div class="col-md-3">
                            <button class="btn btn-grd-primary">View</button>
                        </div>
                    </form>
                    @endif

                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Category</th>
                                <th>No of Loans</th>
                                <th>Total Loans Amount</th>
                                <th>Amount in arrears</th>

                                <th>PAR</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Category</th>
                                <th>No of Loans</th>
                                <th>Total Loans Amount</th>
                                <th>Amount in arrears</th>

                                <th>PAR</th>

                                {{-- <th>Action</th>--}}
                                {{-- <th>Salary</th>--}}
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
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
             // ajax: '{!! route('par_summary.data') !!}',



            ajax: {
                url: '{!! route('par_summary.data') !!}',
                data: function (d) {
                    d.branch = $('select[name=branch_id]').val();

                }
            },

            columns: [
                {data: 'id', name: 'id'},
                {data: 'bname', name: 'bname'},
                {data: 'name', name: 'name'},

                {data: 'loan_count', name: 'loan_count'},
                {data: 'loan_total', name: 'loan_total'},
                {data: 'total_arrears', name: 'total_arrears'},
                {data: 'par', name: 'par'},
                /*  { data: 'action', name: 'action', orderable: false, searchable: false }
  */
            ],
        });
        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });
    </script>


@stop
