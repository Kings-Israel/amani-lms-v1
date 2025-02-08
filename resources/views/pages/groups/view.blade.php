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
                                    <p class="m-b-5">Total Loans</p>
                                    <h4 class="m-b-0">{{number_format($loanCount)}}</h4>
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
                                    <p class="m-b-5">Total Loan Amount</p>
                                    <h4 class="m-b-0">Ksh. {{number_format($totalAmount)}}</h4>
                                </div>
                                <div class="col col-auto text-right">
                                    <i class="feather icon-bookmark f-50 text-c-yellow"></i>
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

            </div>
            <div class="row">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active"><a href="#">Group Leader Details</a></li>
                                <li class="list-group-item"><a href="#"><b>Name:</b> {{$leader->fname}} {{$leader->lname}}</a></li>
                                <li class="list-group-item"><a href="#"><b>Phone:</b> {{$leader->phone}}</a></li>
                            </ul>
                        </div>

                        <div class="card-block">
                            <ul class="list-group list-contacts">
                                <li class="list-group-item active"><a href="#">Group Loans Overview</a></li>
                                <li class="list-group-item"><a href="#"><b>Active Loans:</b> </a> {{number_format($activeloanCount)}}</li>
                                <li class="list-group-item"><a href="#"><b>Active Loan Amount:</b> </a> Ksh. {{number_format($activetotalAmount)}}</li>
                                <li class="list-group-item"><a href="#"><b>Active Paid Amount:</b> </a> Ksh. {{number_format($activepaidAmount)}}</li>

                                <li class="list-group-item"><a href="#"><b>Loans in Arrears Count:</b> </a> {{$arrearsCount}}</li>
                                <li class="list-group-item"><a href="#"><b>Loan Arrears Amount:</b> </a> Ksh. {{ number_format($arrearsAmount)}}</li>

                                <li class="list-group-item"><a href="#"><b>Rolled Over Loans</b> </a> {{$rolledOverCount}} </li>
                                <li class="list-group-item"><a href="#"><b>Rolled Over Amount:</b> </a> Ksh. {{number_format($rolledOverAmount)}}</li>

                                <li class="list-group-item"><a href="#"><b>Non Performing Loans</b> </a> {{$nonPerfCount}} </li>
                                <li class="list-group-item"><a href="#"><b>Non Performing Amount:</b> </a> Ksh. {{number_format($nonPerfAmount)}}</li>
                            </ul>
                        </div>

                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="row">
                        <div class="col-sm-12">

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-header-text">Group Members</h5>
                                </div>
                                <div class="card-block contact-details">
                                    @if($group->approved != true)
                                    <div class=" ">
                                        <button type="button" class="btn-primary btn" data-toggle="modal"
                                                data-target="#default-Modal1">Add Member
                                        </button>
                                    </div>
                                    @elseif(auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager') || auth()->user()->hasRole('field_agent'))
                                        <div class=" ">
                                            <button type="button" class="btn-primary btn" data-toggle="modal"
                                                    data-target="#default-Modal1">Add Member
                                            </button>
                                        </div>
                                    @endif
                                    <br>
                                    <div class="data_table_main table-responsive dt-responsive">
                                        <table id="simpletable"
                                               class="table  table-striped table-bordered nowrap">
                                            <thead>
                                            <tr>
{{--                                                <th>#</th>--}}
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Role</th>
                                                <th>Date Registered</th>
                                                <th>Group Loans</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
{{--                                                <th>#</th>--}}
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Role</th>
                                                <th>Date Registered</th>
                                                <th>Group Loans</th>
                                                <th></th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="card">
            <div class="card-header">
                <h5 class="card-header-text">Loans taken under {{$group->name}}</h5>
            </div>
            <div class="card-block">
                <div class="dt-responsive table-responsive">
                    <table id="cbtn-selectors1"
                           class="table table-striped table-bordered nowrap">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Owner</th>
                            <th>Product</th>
                            <th>Installments</th>
                            <th>% Interest</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                            {{--<th>Disbursed</th>
                            <th>Date Disbursed</th>--}}
                            <th>Payment Date</th>
                            <th>Settled</th>
                            <th>Action</th>

                        </tr>
                        </thead>

                        <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Owner</th>
                            <th>Product</th>
                            <th>Installments</th>
                            <th>% Interest</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                            {{--<th>Disbursed</th>
                            <th>Date Disbursed</th>--}}
                            <th>Payment Date</th>
                            <th>Settled</th>
                            <th>Action</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="default-Modal1" tabindex="-1"
         role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">LITSA CREDIT Customers</h4>
                    <button type="button" class="close"
                            data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="dt-responsive table-responsive">
                        <table id="customers"
                               class="table table-striped table-bordered nowrap" style="width: 100%">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>ID</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>ID</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-default waves-effect "
                            data-dismiss="modal">Close
                    </button>
                </div>
            </div>
        </div>
    </div>


@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#customers').DataTable({

                ajax: '{!! route('groups.customer_data', ['id' => encrypt($group->id)]) !!}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'fullName', name: 'fullName'},
                    {data: 'phone', name: 'phone'},
                    {data: 'branch', name: 'branch'},
                    {data: 'loan_status', name: 'loan_status'},
                    {data: 'id_no', name: 'id_no'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}

                ],
            });
        });

        $('#simpletable').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('groups.members', ['id' => encrypt($group->id)]) !!}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'role', name: 'role'},
                {data: 'created_at', name: 'created_at'},
                {data: 'group_loan', name: 'group_loan'},
                {data: 'action', name: 'action'},

            ],
        });

        $('#cbtn-selectors1').DataTable({
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',

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



            ajax: '{!! route('groups.group_loans_data', ['id' => encrypt($group->id)])  !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'owner', name: 'customers.lname', },
                {data: 'product_name', name: 'products.product_name'},
                {data: 'installments', name: 'products.installments'},
                {data: 'interest', name: 'products.interest'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'total', name: 'total'},
                {data: 'amount_paid', name: 'amount_paid'},
                {data: 'balance', name: 'balance'},
                {data: 'end_date', name: 'end_date'},
                {data: 'settled', name: 'settled'},
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ],
        });
    </script>

    @stop
