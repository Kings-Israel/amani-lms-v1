@extends("layouts.master")
@section("css")
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
@stop

@section("content")

<div class="row">
    <div class="col-sm-12">
        @include('layouts.alert')

        <div class="card">
            <div class="card-header">
            </div>
            <div class="card-block">
                <form id="search" class="form-inline row" method="post" action="">
                    @csrf
                    <div class="col-md-2">
                        <label for="branch">Select Branch</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                            <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                <option value="all" > All </option>
                                @foreach($branches as $brach)
                                    <option value="{{$brach->id}}" > {{$brach->bname}} </option>
                                @endforeach
                            </select>

                        </div>
                        @if ($errors->has('branch_id'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('branch_id') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <label for="branch">Select Field Agent</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                            <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                <option value="all">All</option>
                                @foreach($lfs as $lf)
                                    <option  value="{{$lf->id}}" > {{$lf->name}} </option>
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
                        <label for="date">Date</label>
                        <div class="input-group">
                            <span class="input-group-addon" id="basic-addon1"><i
                                class="icofont icofont-bank-alt"></i></span>
                            <input id="datetimepicker1" type="text" autocomplete="off" name="date" class="form-control{{ $errors->has('date') ? ' is-invalid' : '' }}">
                        </div>
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
                                <th>Branch Name</th>
                                <th>Field Agent</th>
                                <th>Customer Name</th>
                                <th>Mobile Number</th>
                                <th>Id No</th>
                                <th>Created Date</th>
                                <th>Registration Fee Paid</th>
                                <th>Active Loan Amount</th>
                                <th>Disbursement Date</th>
                                <th>Loans Applied</th>
                                <th>Referee</th>
                                <th>Location (Ward, Const., County)</th>
                                <th>Business Type</th>
                                <th>Classification</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Branch Name</th>
                                <th>Field Agent</th>
                                <th>Customer Name</th>
                                <th>Mobile Number</th>
                                <th>Id No</th>
                                <th>Created Date</th>
                                <th>Registration Fee Paid</th>
                                <th>Active Loan Amount</th>
                                <th>Disbursement Date</th>
                                <th>Loans Applied</th>
                                <th>Referee</th>
                                <th>Location (Ward, Const., County)</th>
                                <th>Business Type</th>
                                <th>Classification</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Block Modal -->
        <div class="modal fade" id="confirm-modal">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title"><span class="full-name"></span></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <span class="full-name"></span> will no longer be able to access loan services.
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer text-center">
                        <button type="button" class="btn btn-primary" id="block-btn"> <i class="feather icon-slash"></i>
                            Block</button>
                        <button type="button" class="btn" data-dismiss="modal"> <i class="feather icon-corner-up-left"></i>
                            Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unblock Modal -->
        <div class="modal fade" id="unblock-confirm-modal">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title"><span class="full-name"></span></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <span class="full-name"></span> will now be able to access loan services.
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer text-center">
                        <button type="button" class="btn btn-primary" id="block-btn"> <i class="feather icon-slash"></i>
                            Unblock</button>
                        <button type="button" class="btn" data-dismiss="modal"> <i class="feather icon-corner-up-left"></i>
                            Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
<script>
    (function() {
        jQuery(document).ready(function () {
            "use strict";
            jQuery('#datetimepicker1').datetimepicker({
                format:'Y-m-d'
            });
        })

    // BLOCK MODAL DATA
    $('#confirm-modal').on('show.bs.modal', function(e) {
        let modal    = $(this);
        let fullName = e.relatedTarget.dataset.fname + ' ' + e.relatedTarget.dataset.lname;
        let customerId = e.relatedTarget.dataset.customerid;
        modal.find(".full-name").html(fullName);
        let blockbtn = modal.find("#block-btn");

        blockbtn.on('click', function() {
            axios.post(`/registry/${customerId}/block`, {})
            .then(function (response) {
                $("#confirm-modal").modal('hide');
                toastr.options = {
                    positionClass: "toast-top-center"
                };
                toastr.success("Customer blocked successfully!");
                setTimeout(function() {
                    window.location.replace(`registry`);
                }, 1500);
            })
            .catch(function (error) {
                console.log(error);
            });
        });
    });

    // UNBLOCK MODAL DATA
    $('#unblock-confirm-modal').on('show.bs.modal', function(e) {
        let modal    = $(this);
        let fullName = e.relatedTarget.dataset.fname + ' ' + e.relatedTarget.dataset.lname;
        let customerId = e.relatedTarget.dataset.customerid;
        modal.find(".full-name").html(fullName);
        let blockbtn = modal.find("#block-btn");

        blockbtn.on('click', function() {
            axios.post(`/registry/${customerId}/unblock`, {})
            .then(function (response) {
                $("#unblock-confirm-modal").modal('hide');
                toastr.options = {
                    positionClass: "toast-top-center"
                };
                toastr.success("Customer unblocked successfully!");
                setTimeout(function() {
                    window.location.replace(`registry`);
                }, 1500);
            })
            .catch(function (error) {
                console.log(error);
            });
        });
    });

   var oTable = $('#cbtn-selectors1').DataTable({
            dom: 'Bfrtip',
            "processing": true,
            "serverSide": true,
            "ordering": false,
            buttons: [{extend: 'copyHtml5'}, {
                extend: 'excelHtml5',
                exportOptions: {columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]},
            },
                {
                    extend: 'pdfHtml5', /*exportOptions: {columns: ':visible'}*/
                    orientation: 'landscape',
                    pageSize: 'TABLOID'
                },
                'colvis','pageLength'],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],

            ajax:{
                    url:'{!! route('blocked.customers') !!}',
                    data: function (d) {
                        d.branch = $('select[name=branch_id]').val();
                        d.lf = $('select[name=name]').val();
                        d.registration_fee_paid = $('select[name=registration_fee_paid]').val();
                        d.classification = $('select[name=classification]').val();
                        d.date = $('input[name=date]').val();
                    }
                } ,
            columns: [
                {data: 'id'},
                {data: 'branchName'},
                {data: 'loanOfficer'},
                {data: 'customerName', name: 'fname'},
                {data: 'mobileNumber', name: 'phone'},
                {data: 'idNo', name: 'id_no'},
                {data: 'createdDate'},
                {data: 'regPayment'},
                {data: 'activeLoanAmount'},
                {data: 'activeLoanDisbursementDate'},
                {data: 'loansApplied'},
                {data: 'referee', name: 'referee'},
                {data: 'location', name: 'location'},
                {data: 'businessType'},
                {data: 'classification', name: 'classification'},
                {data: 'action', searchable: false}
            ],
        });

        $('#search').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });

})();

</script>

@stop
