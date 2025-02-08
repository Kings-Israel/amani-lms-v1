@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="">
                <button type="button" class="btn-primary btn" data-toggle="modal"
                        data-target="#default-Modal1">Select Customer
                </button>
            </div>
            <div class="card">

                <div class="card-block">
                    <form action="{{route('single_customer_sms_post')}}" method="post">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" id="leader_id" value="{{ old('leader_id', isset($group->leader_id) ? $group->leader_id : '')}}" name="leader_id">
                            <input type="hidden" id="field_agent_id" value="{{ old('field_agent_id', isset($group->field_agent_id) ? $group->field_agent_id : '')}}" name="field_agent_id">

                            <div class="col-md-3 my-1">
                                <label for="fullname" class="block">Customer Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="icofont icofont-user"></i></span>
                                    <input id="fullname"  readonly type="text" name="fullname" value="{{ old('fullname', isset($customer->fullname) ? $customer->fullname : '')}}" class="form-control{{ $errors->has('fullname') ? ' is-invalid' : '' }}" required
                                           placeholder="Name">


                                </div>
                                @if ($errors->has('fullname'))
                                    <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('fullname') }}</strong>
                                                            </span>
                                @endif
                            </div>

                            <!-- ID -->
                            <div class="col-md-3 my-1">
                                <label class="block">Customer ID</label>
                                <div class="input-group">
                                                <span class="input-group-addon"><i
                                                            class="icofont icofont-envelope-open"></i></span>

                                    <input  readonly  type="number" id="id_no" name="id_no" value="{{ old('id_no', isset($customer->id_no) ? $customer->id_no : '')}}" class="form-control{{ $errors->has('id_no') ? ' is-invalid' : '' }}" required
                                            placeholder="ID NO">

                                </div>
                                @if ($errors->has('id_no'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('id_no') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-3 my-1">
                                <label for="phone" class="block">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="icofont icofont-ui-call"></i></span>
                                    <input id="phone" value="{{ old('phone', isset($customer->phone) ? $customer->phone : '')}}" readonly type="text"  name="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required placeholder="Phone">

                                </div>
                                @if ($errors->has('phone'))
                                    <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('phone') }}</strong>
                                                            </span>
                                @endif
                            </div>

                            <div class="col-md-3 my-1">
                                <label for="field_agent" class="block">Credit Officer</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="icofont icofont-user-suited"></i></span>
                                    <input id="field_agent" value="{{ old('field_agent', isset($customer->field_agent_id) ? $customer->field_agent_id : '')}}" readonly type="text"  name="field_agent" class="form-control{{ $errors->has('field_agent') ? ' is-invalid' : '' }}" required placeholder="Credit Officer">

                                </div>
                                @if ($errors->has('field_agent'))
                                    <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('field_agent') }}</strong>
                                                            </span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="message">Message</label>
                                <textarea rows="9" name="message" class="form-control"
                                          id="compose-textarea2" placeholder="Type your message..." required > </textarea>
                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Send Sms</p>
                </div>


            </div>

        </div>
    </div>
    <div class="modal fade" id="default-Modal1" tabindex="-1"
         role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Customers</h4>
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
                                <th>Credit Officer</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Branch</th>
                                <th>Credit Officer</th>
                                <th>Status</th>
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
                    <button type="button"
                            class="btn btn-primary waves-effect waves-light ">
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        /***************************update installments based on product selected*/
        $(document).ready(function() {
            $('#customers').DataTable({

                ajax: '{!! route('groups.leader_data') !!}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'fullName', name: 'fullName'},
                    {data: 'phone', name: 'phone'},
                    {data: 'branch', name: 'branch'},
                    {data: 'field_agent', name: 'field_agent'},
                    // {data: 'id_no', name: 'id_no'},
                    {data: 'loan_status', name: 'loan_status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}

                ],
            });

            $('#product_id').change(function(){

                var val = $(this).val(); //get new value
                var extra = $('option:selected').attr('data-installments');
                $("#installments").val(extra).text(extra);

            });
            //$('.sel-btn').on('click', function () {
            $('body').on('click', '.sel-btn', function() {
                // alert('clicked')
                // console.log($(this).data('phone'))
                var phone =  $(this).data('phone');
                var field_agent =  $(this).data('field_agent');
                var field_agent_id =  $(this).data('field_agent_id');
                var idno = $(this).data('idno');
                var amount = $(this).data('amount');
                var fullname = $(this).data('fullname');
                var leader_id = $(this).data('id');

                $("#phone").val(phone).text(phone);
                $("#field_agent").val(field_agent).text(field_agent);
                $("#field_agent_id").val(field_agent_id).text(field_agent_id);
                $("#id_no").val(idno).text(idno);
                $("#loan_amount").val(amount).text(amount);
                $("#fullname").val(fullname).text(fullname);
                $("#leader_id").val(leader_id).text(leader_id);
                //  alert(phone)
            })
        })
    </script>
@stop
