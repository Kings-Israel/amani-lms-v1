@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/jquery.steps/css/jquery.steps.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/component.css')}}">
@stop
@section('content')
    @include('layouts.alert')
    <div class="card">
        <div class="card-header">
            <h5>Group Registration</h5>
        </div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    <div id="wizard3">
                        <section>
                            @if($is_edit)
                                <form class="wizard-form" id="design-wizard"
                                      action="{{route('groups.update', ['id' => encrypt($group->id)])}}" method="post">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="prev_leader" value="{{ old('leader_id', isset($group->leader_id) ? $group->leader_id : '')}}">
                                    @else
                                        <form class="wizard-form" id="design-wizard"
                                              action="{{route('groups.store')}}" method="post">
                                            @endif
                                            @csrf

                                            <input type="hidden" id="leader_id" value="{{ old('leader_id', isset($group->leader_id) ? $group->leader_id : '')}}" name="leader_id">
                                            <input type="hidden" id="field_agent_id" value="{{ old('field_agent_id', isset($group->field_agent_id) ? $group->field_agent_id : '')}}" name="field_agent_id">
                                            <h3></h3>
                                            <fieldset>
                                                @if($is_edit)
                                                        @if($group->approved != true)
                                                            <div class="">
                                                                <button type="button" class="btn-primary btn" data-toggle="modal"
                                                                        data-target="#default-Modal1">Change Group Leader
                                                                </button>
                                                            </div>
                                                            <br>
                                                        @elseif(\Illuminate\Support\Facades\Auth::user()->hasRole('admin'))
                                                            <div class="">
                                                                <button type="button" class="btn-primary btn" data-toggle="modal"
                                                                        data-target="#default-Modal1">Change Group Leader
                                                                </button>
                                                            </div>
                                                            <br>
                                                    @endif
                                                @else
                                                    <div class="">
                                                        <button type="button" class="btn-primary btn" data-toggle="modal"
                                                                data-target="#default-Modal1">Select Group Leader
                                                        </button>
                                                    </div>
                                                    <br>
                                                @endif
                                                <div class="form-group row">
                                                    <div class="col-md-4 my-1">
                                                        <label for="name" class="block">Group Name</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-users-alt-6"></i></span>
                                                            <input id="name"  type="text" name="name" value="{{ old('name', isset($group->name) ? $group->name : '')}}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required
                                                                   placeholder="Group Name">
                                                        </div>
                                                        @if ($errors->has('name'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('name') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>


                                                    <div class="col-md-4 my-1">
                                                        <label for="customers_count" class="block">Number of Group Members</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="icofont icofont-clock-time"></i></span>
                                                            <input max="15" min="2" type="number" id="customers_count" value="{{ old('customers_count', isset($group->customers_count) ? $group->customers_count : '')}}" name="customers_count" class="form-control{{ $errors->has('customers_count') ? ' is-invalid' : '' }}" required
                                                                     placeholder="Number of Group Members">

                                                        </div>
                                                        @if ($errors->has('customers_count'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('customers_count') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                        <div class="col-md-4 my-1">
                                                            <label for="branch_id">Select Branch</label>
                                                            <div class="input-group">
                                                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                                                <select @if($is_edit)disabled @endif class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                                                    @foreach($branches as $branch)
                                                                        <option value="{{$branch->id}}" {{ isset($group->branch_id) ? (($group->branch_id == $branch->id) ? 'selected' : '') : $branch->id }}>
                                                                            {{$branch->bname}}
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

                                                    @if($is_edit)
                                                            <div class="col-md-4 my-1">
                                                                <label for="lf">Change Credit Officer</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                                                    <select id="lf" class="form-control{{ $errors->has('lf') ? ' is-invalid' : '' }}" name="lf" required>
                                                                        @foreach($field_agents as $field_agent)
                                                                            <option value="{{$field_agent->id}}" {{ isset($group->field_agent_id) ? (($group->field_agent_id == $field_agent->id) ? 'selected' : '') : $field_agent->id }}>
                                                                                {{$field_agent->name}} @if($group->field_agent_id == $field_agent->id) <span class="badge badge-success">CURRENT</span> @endif
                                                                            </option>
                                                                        @endforeach
                                                                    </select>

                                                                </div>

                                                                @if ($errors->has('lf'))
                                                                    <span class="text-danger" role="alert">
                                                                        <strong>{{ $errors->first('lf') }}</strong>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                    @endif

                                                </div>


                                                    <h6><b>Group Leader Details</b></h6>

                                                <div class="form-group row">

                                                    {{--Group Leader Details--}}

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
                                                        @if ($errors->has('field_agent'))
                                                            <span class="text-danger" role="alert">
                                                                <strong>{{ $errors->first('field_agent') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <h3></h3>
                                            <button type="submit" class="btn btn-info">Submit</button>
                                        </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--modal--}}
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
                                <th>ID</th>
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
                                <th>ID</th>
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
                    {data: 'id_no', name: 'id_no'},
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
