@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-text">Monthly Loan Collections</h5>
                    <div class="card-header-right">
                        <ul class="list-unstyled card-option">
                            <li><a href="{{route('credit_officer_performance', ['id'=>encrypt($user->id)])}}" class="btn btn-sm btn-primary">Go back to Performance Report</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-block">
                    <form class="form-inline row" method="post">
                        @csrf
                        <div class="col-md-3">
                            <label for="collection_month">Select Month </label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-calendar"></i></span>
                                <select id="collection_month" name="collection_month" class="form-control{{ $errors->has('collection_month') ? ' is-invalid' : '' }}">
                                    @foreach($months as $month)
                                        <option value="{{$month[0]}}" {{($month[0] == $cur_month ) ? 'selected' : ''}}>{{$month[1]}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('collection_month'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('collection_month') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="col-md-3">
                            <label for="collection_year">Select Year </label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-calendar"></i></span>
                                <select id="collection_year" name="collection_year" class="form-control{{ $errors->has('collection_year') ? ' is-invalid' : '' }}">
                                    @foreach($years as $year)
                                        <option value="{{$year}}" {{($year == now()->format('Y') ) ? 'selected' : ''}}>{{$year}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('collection_year'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('collection_year') }}</strong>
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
                                <th>Customer</th>
                                <th>Disbursed Amount</th>
                                <th>Loan Balance</th>
                                <th>Loan Amount</th>
                                <th>Interest</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Disbursed Amount</th>
                                <th>Loan Balance</th>
                                <th>Loan Amount</th>
                                <th>Interest</th>
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
        const collection = $('#cbtn-selectors1').DataTable({
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',
            buttons: [
                {extend: 'copyHtml5'},
                {
                    extend: 'excelHtml5',
                    exportOptions: {columns: ':visible'},
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'TABLOID'
                },
                'colvis',
                'pageLength'
            ],
            "lengthMenu": [[10, 15, 30, -1], [10, 15, 30, "All"]],
            ajax: {
                url: '{!! route('credit_officer_monthly_collection_overview_data', ['id'=>$user->id]) !!}',
                data: function(d) {
                    d.collection_month = $('select[name=collection_month]').val();
                    d.collection_year = $('select[name=collection_year]').val();
                },
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'customer_name', name: 'customer_name'},
                {data: 'disbursed_amount', name: 'disbursed_amount'},
                {data: 'loan_balance', name: 'loan_balance'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'interest', name: 'interest'},
            ],
        });
        $('#collection_month').on('change', function(e) {
            collection.clear().draw();
            collection.columns.adjust().draw();
        });
        $('#collection_year').on('change', function(e) {
            collection.clear().draw();
            collection.columns.adjust().draw();
        });
    </script>


@stop
