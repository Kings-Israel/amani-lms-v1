@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/jquery.steps/css/jquery.steps.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/component.css')}}">



@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Loan Registration</h5>
            {{--   <span>Add class of <code>.form-control</code> with <code>&lt;input&gt;</code> tag</span>--}}
        </div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    <div id="wizard3">
                        <section>
                            <form class="wizard-form" id="design-wizard"
                                  action="#">
                                <h3></h3>
                                <fieldset>
                                    <div class="">
                                        <button type="button" class="btn-info btn" data-toggle="modal"
                                                data-target="#default-Modal">Search
                                        </button>
                                    </div>
                                    <br>
                                    <div class="form-group row">

                                        <!-- postal address -->
                                        <div class="col-md-4 my-1">
                                            <label class="block">Customer Name</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-user"></i></span>
                                                <input disabled="disabled" type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required
                                                       placeholder="Name">
                                                @if ($errors->has('name'))
                                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- ID -->
                                        <div class="col-md-4 my-1">
                                            <label class="block">Customer ID</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i
                                                        class="icofont icofont-envelope-open"></i></span>
                                                <input disabled type="number" id="id_no" name="id_no" class="form-control{{ $errors->has('id_no') ? ' is-invalid' : '' }}" required
                                                       placeholder="ID NO">
                                                @if ($errors->has('id_no'))
                                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('id_no') }}</strong>
                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4 my-1">
                                            <label for="product_id" class="block">Loan Product</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-social-pandora"></i></span>

                                                <select id="product_id" name="product_id" class="js-example-basic-single form-control{{ $errors->has('product_id') ? ' is-invalid' : '' }}" required>
                                                    @foreach($products as $product)
                                                        <option
                                                            value="{{$product->id}}">{{$product->product_name}}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('product_id'))
                                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('product_id') }}</strong>
                                    </span>
                                                @endif


                                            </div>


                                        </div>

                                    </div>




                                        <div class="form-group row">

                                            <div class="col-md-4 my-1">
                                                <label for="purpose" class="block">Purpose</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                            class="icofont icofont-location-pin"></i></span>

                                                    <select id="purpose" name="purpose" class="js-example-basic-single form-control{{ $errors->has('purpose') ? ' is-invalid' : '' }}" required>
                                                            <option value="Business Expense">Business Expense</option>
                                                            <option value="Start Business">Start Business</option>

                                                    </select>
                                                    @if ($errors->has('purpose'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('purpose') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{--Amount Applied--}}
                                            <div class="col-md-4 my-1">
                                                <label for="loan_amount" class="block">Applied Loan Amount</label>
                                                <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-money"></i></span>
                                                    <input  type="number" id="loan_amount" name="loan_amount" class="form-control{{ $errors->has('loan_amount') ? ' is-invalid' : '' }}" required
                                                           placeholder="Loan Amount">
                                                    @if ($errors->has('loan_amount'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('loan_amount') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Negotiated Installments -->
                                            <div class="col-md-4 my-1">
                                                <label for="installments" class="block">Negotiated Installments</label>
                                                <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-notebook"></i></span>
                                                    <input disabled  type="number" id="installments" name="installments" class="form-control{{ $errors->has('loan_amount') ? ' is-invalid' : '' }}" required
                                                            placeholder="Installments">
                                                    @if ($errors->has('installments'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('installments') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>


                                            {{--Phone Number--}}

                                            <div class="col-md-4 my-1">
                                                <label for="installments" class="block">Phone</label>
                                                <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-ui-call"></i></span>
                                                    <input disabled  type="text" id="installments" name="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required
                                                           placeholder="Phone">
                                                    @if ($errors->has('phone'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                </fieldset>
                                <h3></h3>
                                <fieldset>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="name-2" class="block">First
                                                name *</label>
                                        </div>
                                        <div class="col-sm-12">
                                            <input id="name-23" name="name"
                                                   type="text"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="surname-2"
                                                   class="block">Last name
                                                *</label>
                                        </div>
                                        <div class="col-sm-12">
                                            <input id="surname-23"
                                                   name="surname" type="text"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="phone-2" class="block">Phone
                                                #</label>
                                        </div>
                                        <div class="col-sm-12">
                                            <input id="phone-23" name="phone"
                                                   type="number"
                                                   class="form-control phone">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="date" class="block">Date
                                                Of Birth</label>
                                        </div>
                                        <div class="col-sm-12">
                                            <input id="date3"
                                                   name="Date Of Birth"
                                                   type="text"
                                                   class="form-control date-control">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">Select Country
                                        </div>
                                        <div class="col-sm-12">
                                            <select class="form-control required">
                                                <option>Select State</option>
                                                <option>Gujarat</option>
                                                <option>Kerala</option>
                                                <option>Manipur</option>
                                                <option>Tripura</option>
                                                <option>Sikkim</option>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{--modal--}}
    <div class="modal fade" id="default-Modal" tabindex="-1"
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
                                <th>Email</th>
                                <th>ID</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
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
                    {{-- <button type="button"
                             class="btn btn-primary waves-effect waves-light ">
                         Save changes
                     </button>--}}
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script src="{{ asset('bower_components/jquery.cookie/js/jquery.cookie.js') }}"></script>
    <script src="{{ asset('bower_components/jquery.steps/js/jquery.steps.js') }}"></script>
    <script src="{{ asset('bower_components/jquery-validation/js/jquery.validate.js') }}"></script>

    {{--modal--}}
    <script type="text/javascript" src="{{ asset('assets/js/modal.js') }}"></script>


    <script type="text/javascript" src="{{ asset('assets/js/modalEffects.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/classie.js') }}"></script>

    {{--<script src="../../../../cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    <script src="../../../../cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>--}}
    <script type="text/javascript" src="{{ asset('assets/pages/form-validation/validate.js') }}"></script>

    <script src="{{ asset('assets/pages/forms-wizard-validation/form-wizard.js') }}"></script>


    <script>
        $('#customers').DataTable({
            /*   dom: 'Bfrtip',
               buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                   extend: 'excelHtml5',
                   exportOptions: {columns: ':visible'}
               }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis'],
   */

            ajax: '{!! route('customers.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'fullname', name: 'fullname'},
                {data: 'phone', name: 'phone'},
                {data: 'email', name: 'email'},
                {data: 'id_no', name: 'id_no'},
                {data: 'action', name: 'action', orderable: false, searchable: false}

            ],
        });
    </script>
@stop
