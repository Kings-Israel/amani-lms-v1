@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{asset('bower_components/sweetalert/css/sweetalert.css')}}">
@stop

@section("content")
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary update-card">
                <div class="card-block">
                    <div class="row align-items-end">
                        <div class="col-8">
                            <h4 class="text-white">{{$customers}}</h4>
                            <h6 class="text-white m-b-0">Total Customers</h6>
                        </div>
                        <div class="col-4 text-right">
                            <i class="icofont icofont-users-alt-5 icon-dashboard" ></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            @include('layouts.alert')
                @csrf
                <div class="card">
                    <div class="card-block">
                        <div class="dt-responsive table-responsive">
                            <form id="updateCOform" action="{{route('post_update_co')}}" method="post">
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <label for="branch">Select Credit Officer</label>
                                        <div class="input-group">
                                            <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                            <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>
                                                <option value="">Select Credit Officer</option>
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
                                    <div class="col-md-3 mr-0">
                                        <div class="checkbox-fade fade-in-primary d- mt-4 ">
                                            <label>
                                                <input class="form-check-input" type="checkbox" name="select_all" id="select_all" {{ old('select_all') ? 'checked' : '' }}>
                                                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-secondary"></i></span>
                                                <span class="text-inverse">Select All Customers</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 ml-0">
                                        <input value="{{$user->id}}" name="old_co" hidden>
                                        <button type="submit" id="update-co" class="btn btn-primary mt-3">Update Selected</button>
                                    </div>
                                </div>

                                @csrf
                                <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>Customer Name</th>
                                        <th>Phone Number</th>
                                        <th>ID Number</th>
                                        <th>Date Registered</th>
                                    </tr>
                                    </thead>

                                    <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>Customer Name</th>
                                        <th>Phone Number</th>
                                        <th>ID Number</th>
                                        <th>Date Registered</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </form>

                        </div>
                    </div>
                </div>
        </div>
    </div>

@stop
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script type="text/javascript" src="{{asset('bower_components/sweetalert/js/sweetalert.min.js')}}"></script>
    <script>
    var oTable = $('#cbtn-selectors1').DataTable({
        dom: 'Bfrtip',
        "processing": true,
        "serverSide": true,
        buttons: ['pageLength'],
        "lengthMenu": [[50, 100, -1], ["50", "100","All"]],
        ajax:{
            url:'{!! route('changeCOCustomerData', ['credOfficer'=>$user->id]) !!}',
        } ,
        columns: [
            {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false},
            {data: 'customerName', name: 'customerName'},
            {data: 'mobileNumber', name: 'mobileNumber'},
            {data: 'idNo', name: 'idNo'},
            { data: 'createdDate', name: 'createdDate'}
        ],
    });

    $('#update-co').on('click', function (e) {
        $('#updateCOform').on('submit', function (e) {
            var form = this;
            e.preventDefault();
            swal({
                    title: "Please confirm",
                    text: "Do you want to proceed with Updating CO Customers?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#96d25f',
                    confirmButtonText: 'Yes, confirm',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function () {
                    form.submit();
                });
        });

    });

</script>
@stop
