@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/jquery.steps/css/jquery.steps.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/component.css')}}">

@stop

@section('content')
    @include('layouts.alert')
    <div class="card">
        <div class="card-header">
            <h5>Group Loan Registration</h5>
        </div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    <div id="wizard3">
                        <section>
                            @if($is_edit)
                                <form class="wizard-form" id="design-wizard"
                                      action="{{route('loans.update', ['id' => encrypt($loan->id)])}}" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="_method" value="PUT">
                                @else
                            <form class="wizard-form" id="design-wizard"
                                  action="{{route('loans.store_group_loan')}}" method="post" enctype="multipart/form-data">
                                @endif
                                @csrf
                                <input type="hidden" id="customer_id" value="{{ old('customer_id', isset($loan->customer_id) ? $loan->customer_id : '')}}" name="customer_id">
                                <input type="hidden" id="group_id" value="{{ old('customer_id', isset($loan->group_id) ? $loan->group_id : '')}}" name="group_id">
                                <h3></h3>
                                <fieldset>
                                    @if($is_edit)
                                        @else
                                        <div class="">
                                            <button type="button" class="btn-primary btn" data-toggle="modal"
                                                    data-target="#default-Modal1">Search
                                            </button>
                                        </div>
                                        <br>
                                    @endif
                                    <div class="form-group row">

                                        <!-- postal address -->
                                        <div class="col-md-4 my-1">
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
                                        <div class="col-md-4 my-1">
                                            <label class="block">Customer ID</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i
                                                        class="icofont icofont-envelope-open"></i></span>

                                                <input @if($is_edit)@else readonly @endif type="number" id="id_no" name="id_no" value="{{ old('id_no', isset($customer->id_no) ? $customer->id_no : '')}}" class="form-control{{ $errors->has('id_no') ? ' is-invalid' : '' }}" required
                                                       placeholder="ID NO">

                                            </div>
                                            @if ($errors->has('id_no'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('id_no') }}</strong>
                                    </span>
                                            @endif
                                        </div>

                                        <div class="col-md-4 my-1">
                                            <label for="product_id" class="block">Loan Product</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-social-pandora"></i></span>

                                                <select id="product_id" name="product_id" class="js-example-basic-single form-control{{ $errors->has('product_id') ? ' is-invalid' : '' }}" required>

                                                    <option selected readonly>Select Product</option>
                                                    @foreach($products as $product)
                                                        <option
{{--                                                            value="{{$product->id}}" {{ (old("product_id") == $product->id ? "selected":"") }}  data-installments="{{$product->installments}}">{{$product->product_name}}</option>--}}
                                                        <option
                                                            value="{{$product->id}}" {{ isset($loan->product_id) ? (($loan->product_id == $product->id) ? 'selected' : '') : $product->id/*(old("product_id") == $product->id ? "selected":"")*/ }} data-installments="{{$product->installments}}" >
                                                            {{$product->product_name}}
                                                        </option>
                                                    @endforeach
                                                </select>



                                            </div>
                                            @if ($errors->has('product_id'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('product_id') }}</strong>
                                    </span>
                                            @endif


                                        </div>

                                    </div>




                                    <div class="form-group row">

                                        <div class="col-md-4 my-1">
                                            <label for="purpose" class="block">Purpose</label>
                                            <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                            class="icofont icofont-location-pin"></i></span>

                                                <select id="purpose" name="purpose" class="js-example-basic-single form-control{{ $errors->has('purpose') ? ' is-invalid' : '' }}" required>
                                                    <option value="Business Expense" {{ (old("purpose") == "Business Expense" ? "selected":"") }}>Business Expense</option>
                                                    <option value="Start Business" {{ (old("purpose") == "Start Business" ? "selected":"") }}>Start Business</option>

                                                </select>

                                            </div>
                                            @if ($errors->has('purpose'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('purpose') }}</strong>
                                    </span>
                                            @endif
                                        </div>

                                        <div class="col-md-4 my-1">
                                            <label for="loan_amount" class="block">Applied Loan Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-money"></i></span>
                                                <input   type="number" id="loan_amount" value="{{ old('loan_amount', isset($loan->loan_amount) ? $loan->loan_amount : '')}}" name="loan_amount" class="form-control{{ $errors->has('loan_amount') ? ' is-invalid' : '' }}" required
                                                        placeholder="Loan Amount">

                                            </div>
                                            @if ($errors->has('loan_amount'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('loan_amount') }}</strong>
                                    </span>
                                            @endif
                                        </div>

                                        <!-- Negotiated Installments -->
                                        <div class="col-md-4 my-1">
                                            <label for="installments" class="block">Negotiated Installments</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-notebook"></i></span>
                                                <input value="{{ old('installments', isset($loan->product) ? $loan->product()->first()->installments : '')}}" readonly  type="number" id="installments" name="installments" class="form-control{{ $errors->has('installments') ? ' is-invalid' : '' }}" required
                                                       placeholder="Installments">

                                            </div>
                                            @if ($errors->has('installments'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('installments') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                        <div class="col-md-4 my-1">
                                            <label for="phone" class="block">Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-ui-call"></i></span>
                                                <input id="phone" value="{{ old('phone', isset($customer->phone) ? $customer->phone : '')}}" @if($is_edit)@else readonly @endif  type="text"  name="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required placeholder="Phone">

                                            </div>
                                            @if ($errors->has('phone'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                            @endif
                                        </div>

                                        <div class="col-md-4 my-1">
                                            <label for="group_name" class="block">Group Name</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-users-alt-6"></i></span>
                                                <input id="group_name" value="{{ old('group_name', isset($group->name) ? $group->name : '')}}" @if($is_edit)@else readonly @endif  type="text"  name="group_name" class="form-control{{ $errors->has('group_name') ? ' is-invalid' : '' }}" required placeholder="Group Name">

                                            </div>
                                            @if ($errors->has('group_name'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('group_name') }}</strong>
                                    </span>
                                            @endif
                                        </div>

                                        <div class="col-md-4 my-1">
                                            <label for="loan_type" class="block">Loan Type</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-abacus-alt"></i></span>
                                                <select id="loan_type" name="loan_type" class="js-example-basic-single form-control{{ $errors->has('loan_type') ? ' is-invalid' : '' }}" required>
                                                    <option value="" selected disabled readonly>Select Loan Repayment Type</option>
                                                    @foreach($loan_types as $type)
                                                        <option value="{{$type->id}}" {{ isset($loan->loan_type_id) ? (($loan->loan_type_id == $type->id) ? 'selected' : '') : $type->id }} >
                                                            {{$type->name}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($errors->has('loan_type'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('loan_type') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="col-md-4 my-1">
                                            <label for="loan_type" class="block">Loan Application Document <small class="badge badge-danger">.pdf*</small></label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-document-folder"></i></span>
                                                <input type="file" name="loan_form" class="form-control{{ $errors->has('loan_form') ? ' is-invalid' : '' }}" >
                                            </div>
                                            @if ($errors->has('loan_form'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('loan_form') }}</strong>
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
                                <th>Group Name</th>
                                <th>Loan Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Group Name</th>
                                <th>Loan Status</th>
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

                ajax: '{!! route('loans.customer_group_data') !!}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'fullName', name: 'fullName'},
                    {data: 'phone', name: 'phone'},
                    {data: 'group_name', name: 'group_name'},
                    {data: 'loan_status', name: 'loan_status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
            });
            $('#product_id').change(function(){
                var val = $(this).val(); //get new value
                var extra = $('option:selected').attr('data-installments');
                $("#installments").val(extra).text(extra);
            });
            $('body').on('click', '.sel-btn', function() {
                var phone =  $(this).data('phone');
                var idno = $(this).data('idno');
                var amount = $(this).data('amount');
                var fullname = $(this).data('fullname');
                var customer_id = $(this).data('id');
                var group_id = $(this).data('group_id');
                var group_name = $(this).data('group_name');
                $("#phone").val(phone).text(phone);
                $("#id_no").val(idno).text(idno);
                $("#loan_amount").val(amount).text(amount);
                $("#fullname").val(fullname).text(fullname);
                $("#customer_id").val(customer_id).text(customer_id);
                $("#group_id").val(group_id).text(group_id);
                $("#group_name").val(group_name).text(group_name);
            })



        })
    </script>
@stop
