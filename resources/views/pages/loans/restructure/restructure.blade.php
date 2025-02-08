@extends("layouts.master")


@section('css')

@stop

@section("content")
    <div class="row">
        <div class="col-lg-12">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @include('layouts.alert')
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col">
                                    <p class="m-b-5">Total Loan Amount</p>
                                    <h4 class="m-b-0">{{number_format($loanAmount)}}</h4>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-bar-chart f-50 text-c-pink"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col">
                                    <p class="m-b-5">Total Paid Amount</p>
                                    <h4 class="m-b-0">Ksh. {{number_format($paidAmount)}}</h4>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-credit-card f-50 text-secondary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col">
                                    <p class="m-b-5">Total Balance</p>
                                    <h4 class="m-b-0">Ksh. {{number_format($balance)}}</h4>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-anchor f-50 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col">
                                    <p class="m-b-5">Installments</p>
                                    <h6 class="m-b-0">Complete: {{number_format($complete)}}</h6>
                                    <h6 class="m-b-0">Incomplete: {{number_format($incomplete)}}</h6>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-bar-chart-2 f-50 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-block">
                    <form action="{{route('loans.restructure_post', ['id'=>encrypt($loan->id)])}}" method="post">
                        @csrf
                        <div class="row" >
                            <div class="col-md-3">
                                <label for="days">Set Duration in Days</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-calendar"></i></span>
                                    <input type="number" id="days" min="7" max="70" name="days" value="{{ old('days')}}" class="form-control{{ $errors->has('days') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('days'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('days') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary float-left mt-4">Submit</button>
                            </div>
                        </div>
                    </form>
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Principal Amount</th>
                                <th>Interest</th>
                                <th>Total</th>
                                <th>Paid Amount</th>
                                <th>Status</th>
                                <th>Current Installment</th>
                                <th>Being Paid</th>
                                <th>Date Due</th>
                                <th>Last Paid</th>
                                <th>Date Created</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Principal Amount</th>
                                <th>Interest</th>
                                <th>Total</th>
                                <th>Paid Amount</th>
                                <th>Status</th>
                                <th>Current Installment</th>
                                <th>Being Paid</th>
                                <th>Date Due</th>
                                <th>Last Paid</th>
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
        (function() {
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
                ajax:{
                    url:'{!! route('loans.installments_data', ['id'=>$loan->id]) !!}',
                } ,
                columns: [
                    {data: 'position'},
                    {data: 'principal_amount'},
                    {data: 'interest'},
                    {data: 'total', name: 'total'},
                    {data: 'amount_paid', name: 'amount_paid'},
                    {data: 'completed'},
                    {data: 'current'},
                    {data: 'being_paid'},
                    {data: 'due_date'},
                    {data: 'last_payment_date'},
                    {data: 'created_at'},
                ],
            });
        })();
    </script>
@stop
