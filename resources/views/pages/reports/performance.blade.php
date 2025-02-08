@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">

                <div class="card-block">
                    <form class="form-inline row" method="post">


                        @csrf

                        <div class="col-md-6">
                            <label for="month">Choose Month</label>
                            <div class="input-group">

                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-calendar"></i></span>
                                <select id="month" name="month" class="form-control{{ $errors->has('month') ? ' is-invalid' : '' }}">
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



                    </form>

                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Last Updated At</th>
                                <th>Disbursement Target</th>
                                <th>Actual Disbursement</th>
                                <th>Achieved %</th>
                                <th>Collection Target</th>
                                <th>Actual Collection</th>
                                <th>Achieved %</th>
                                <th>Average Performance %</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="2">Total</th>
                                {{--<th></th>--}}
                                <th>Disbursement Target</th>
                                <th>Actual Disbursement</th>
                                <th>Achieved</th>
                                <th>Collection Target</th>
                                <th>Actual Collection</th>
                                <th>Achieved</th>
                               {{-- <th>Due Collection</th>
                                <th></th>--}}
                                <th>Average Performance</th>
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
        {{--var month = '{{ request('hotelname' }}';--}}

        var oTable = $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
                processing: true,
                serverSide: true,
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis'],


            {{--ajax: '{!! route('ro.performance_data', ['id' => encrypt($user->id)]) !!}',--}}
           {{--/* data: function(d) {--}}
                {{--d.month = $('select[name=month]').val();--}}
            {{--},*/--}}
                ajax: {
                    url: '{!! route('ro.performance_data', ['id' => encrypt($user->id)]) !!}',
                    data: function(d) {
                        d.month = $('select[name=month]').val();
                    },
                },

            columns: [
                {data: 'id'},
                {data: 'updated_at'},
                {data: 'disbursement_target'},
                {data: 'actual_disbursement'},
                {data: 'disbursement_achieved'},
                {data: 'collection_target'},
                {data: 'actual_collection'},
                {data: 'collection_achieved'},
                /*{data: 'due_collection'},
                {data: 'collections_MTD'},*/
                {data: 'average_performance'},
            ],


            "footerCallback": function ( row, data, start, end, display ) {
                var api = this.api(), data;

                // converting to interger to find total
                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '')*1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                // computing column Total of the complete result
                var dis_target = api
                    .column( 2 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var dis_targetData = api
                    .column( 2 )
                    .data();
                var actual_dis = api
                    .column( 3 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var dis_achieved = api
                    .column( 4 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var dis_achievedData = api
                    .column( 4 )
                    .data();


                var CT = api
                    .column( 5 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var AC = api
                    .column( 6 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                var AC_achieved = api
                    .column( 7 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );

                var AC_achievedData = api
                    .column( 7 )
                    .data();
              /*  var due = api
                    .column( 8 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );*/

                var average = api
                    .column( 8 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );

                var averageData = api
                    .column( 8 )
                    .data();
                var dt = dis_target / dis_targetData.count();



                // Update footer by showing the total with the reference of the column index
                $( api.column( 2 ).footer() ).html(dis_target / dis_targetData.count());
                $( api.column( 3 ).footer() ).html(actual_dis);
                $( api.column( 4 ).footer() ).html(Math.round((actual_dis / dt)*100));
                $( api.column( 5 ).footer() ).html(CT);
                $( api.column( 6 ).footer() ).html(AC);
                //$( api.column( 7 ).footer() ).html(AC_achieved / AC_achievedData.count());
                $( api.column( 7 ).footer() ).html(Math.round( AC / CT * 100));

                /* $( api.column( 8 ).footer() ).html(due);*/
                //$( api.column( 8 ).footer() ).html(average / averageData.count());
                $( api.column( 8 ).footer() ).html((Math.floor((actual_dis / dt)*100 + (AC / CT)* 100))/ 2);








            },
        });

        $('#month').on('change', function(e) {

            oTable.clear().draw();
            oTable.columns.adjust().draw();

            //  alert('changed')
           // e.preventDefault();
        });
       /* $('#search-form').on('submit', function(e) {
           // oTable.draw();
           // oTable.rows().invalidate().draw()
            oTable.draw();
           // oTable.draw()

            // alert('here')
            e.preventDefault();
        });*/
    </script>


@stop
