@extends("layouts.master")
@section("css")
    <link rel="stylesheet" href="{{asset('bower_components/select2/css/select2.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')

            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-text">About Guarantor</h5>
                    <button id="edit-btn" type="button"
                            class="btn btn-sm btn-primary waves-effect waves-light f-right">
                        <i class="icofont icofont-edit"></i>
                    </button>
                </div>
                <div class="card-block">
                    <div class="view-info">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="general-info">
                                    <div class="row">
                                        <div class="col-lg-12 {{--col-xl-6--}}">
                                            <div class="table-responsive">
                                                <table class="table m-0">
                                                    <tbody>
                                                    <tr>
                                                        <th scope="row">Full
                                                            Name
                                                        </th>
                                                        <td>{{$guarantor->gname}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Date of Birth</th>
                                                        <td>{{$guarantor->gdob}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Phone </th>
                                                        <td>{{$guarantor->gphone}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Location</th>
                                                        <td>{{$guarantor->location}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Business Category</th>
                                                        <td>{{$guarantor->business}}</td>
                                                    </tr>
                                                   {{-- <tr>
                                                        <th scope="row">Role</th>
                                                        <td>{{$guarantor->roles()->first()->name}}</td>
                                                    </tr>--}}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- <div class="col-lg-12 col-xl-6">
                                             <div class="table-responsive">
                                                 <table class="table">
                                                     <tbody>
                                                     <tr>
                                                         <th scope="row">Email
                                                         </th>
                                                         <td><a href="#!"><span
                                                                     class="__cf_email__"
                                                                     data-cfemail="5f1b3a32301f3a273e322f333a713c3032">[email&#160;protected]</span></a>
                                                         </td>
                                                     </tr>
                                                     <tr>
                                                         <th scope="row">Mobile
                                                             Number
                                                         </th>
                                                         <td>(0123) - 4567891
                                                         </td>
                                                     </tr>
                                                     <tr>
                                                         <th scope="row">
                                                             Twitter
                                                         </th>
                                                         <td>@xyz</td>
                                                     </tr>
                                                     <tr>
                                                         <th scope="row">Skype
                                                         </th>
                                                         <td>demo.skype</td>
                                                     </tr>
                                                     <tr>
                                                         <th scope="row">
                                                             Website
                                                         </th>
                                                         <td><a href="#!">www.demo.com</a>
                                                         </td>
                                                     </tr>
                                                     </tbody>
                                                 </table>
                                             </div>
                                         </div>--}}

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="edit-info">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="general-info">
                                    <form id="guarantor" action="{{route('guarantors.update', ['id' => encrypt($guarantor->id)])}}" method="post">
                                        <input name="_method" type="hidden" value="PUT">
                                        @csrf

                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="gname">Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                                    <input type="text" id="gname" name="gname" value="{{ old('gname', isset($guarantor->gname) ? $guarantor->gname : '')}}" class="form-control{{ $errors->has('gname') ? ' is-invalid' : '' }}" required>

                                                    @if ($errors->has('gname'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('gname') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="gphone">Phone <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                                    <input type="text" id="gphone" name="gphone" value="{{ old('gphone', isset($guarantor->gphone) ? $guarantor->gphone : '')}}" class="form-control{{ $errors->has('gphone') ? ' is-invalid' : '' }}" placeholder="0708000000" required>
                                                    @if ($errors->has('gphone'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('gphone') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="gid">National ID <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                                    <input type="number" name="gid" value="{{ old('gid', isset($guarantor->gid) ? $guarantor->gid : '')}}" class="form-control{{ $errors->has('gid') ? ' is-invalid' : '' }}" required>
                                                    @if ($errors->has('gid'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('gid') }}</strong>
                                    </span>
                                                    @endif

                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">

                                            <div class="col-md-4">
                                                <label for="gdob">Date of Birth <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                                                    <input type="text" id="datetimepicker1" name="gdob" value="{{ old('gdob', isset($guarantor->gdob) ? $guarantor->gdob : '')}}" class="datepicker form-control{{ $errors->has('gdob') ? ' is-invalid' : '' }}" required>

                                                    @if ($errors->has('gdob'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('gdob') }}</strong>
                                    </span>
                                                    @endif

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="location">Location <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-location-pin"></i></span>
                                                    <input type="text" id="location" name="location" value="{{ old('location', isset($guarantor->location) ? $guarantor->location : '')}}" class="form-control{{ $errors->has('location') ? ' is-invalid' : '' }}" required>
                                                    <input type="hidden" id="Lat" name="latitude"
                                                           value="{{ old('latitude', isset($guarantor->latitude) ? $guarantor->latitude : '')}}"/>
                                                    <input type="hidden" id="Lng" name="longitude"
                                                           value="{{ old('longitude', isset($guarantor->longitude) ? $guarantor->longitude : '')}}"/>
                                                    @if ($errors->has('location'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('location') }}</strong>
                                    </span>
                                                    @endif

                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="marital_status">Marital Status <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-users-alt-4"></i></span>
                                                    <select class="form-control{{ $errors->has('marital_status') ? ' is-invalid' : '' }}" name="marital_status" required>
                                                        <option value="single">Single</option>
                                                        <option value="married">Married</option>


                                                    </select>
                                                    @if ($errors->has('marital_status'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('marital_status') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="industry_id">Industry <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-industries-alt-2"></i></span>
                                                    <select class="js-example-basic-single form-control{{ $errors->has('industry_id') ? ' is-invalid' : '' }}" name="industry_id" required>
                                                        @foreach($industries as $industry)

                                                            <option
                                                                value="{{$industry->id}}" {{ isset($guarantor->industry_id) ? (($guarantor->industry_id == $industry->id) ? 'selected' : '') : $industry->id }}>
                                                                {{$industry->iname}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('industry_id'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('industry_id') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="business_id">Business <span class="text-danger">*</span></label>


                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                                    <select class="js-example-basic-single form-control{{ $errors->has('industry_id') ? ' is-invalid' : '' }}" name="business_id" required>
                                                        @foreach($business as $busines)

                                                            <option
                                                                value="{{$busines->id}}" {{ isset($guarantor->business_id) ? (($guarantor->business_id == $busines->id) ? 'selected' : '') : $busines->id }}>
                                                                {{$busines->bname}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('business_id'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('business_id') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- <div class="input-group date input-group-date-custom">
                                                 <input type="text" class="form-control">
                                                 <span class="input-group-addon ">
                 <i class="icofont icofont-clock-time"></i>
                 </span>
                                             </div>--}}




                                        </div>
                                        <button class="btn btn-primary float-left">Submit</button>

                                    </form>



                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>

        <div class="col-sm-12">
            <div class="page-header-title">
                <div class="d-inline">
                    <h4>Guarantor's Customer</h4>
                    <span>Details</span>
                </div>
            </div>

        </div>


        <div class="col-sm-12">
            @include('layouts.alert')

            <div class="card">
                <div class="card-header">
                    <h5 class="card-header-text">About Customer</h5>
                    {{--<button id="edit-btn" type="button"
                            class="btn btn-sm btn-primary waves-effect waves-light f-right">
                        <i class="icofont icofont-edit"></i>
                    </button>--}}
                </div>
                <div class="card-block">
                    <div class="view-info">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="general-info">
                                    <div class="row">
                                        <div class="col-lg-12 {{--col-xl-6--}}">
                                            <div class="table-responsive">
                                               @if(isset($customer))
                                                    <table class="table m-0">
                                                        <tbody>
                                                        <tr>
                                                            <th scope="row">Full
                                                                Name
                                                            </th>
                                                            <td>{{$customer->fname . $customer->lname }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Date of Birth</th>
                                                            <td>{{$customer->dob}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Phone </th>
                                                            <td>{{$customer->phone}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Email </th>
                                                            <td>{{$customer->email}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Location</th>
                                                            <td>{{$customer->location}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Marital Status</th>
                                                            <td>{{$customer->marital_status}}</td>
                                                        </tr>
                                                        {{-- <tr>
                                                             <th scope="row">Role</th>
                                                             <td>{{$guarantor->roles()->first()->name}}</td>
                                                         </tr>--}}
                                                        </tbody>
                                                    </table>

                                                   @else
                                                    <div class="alert alert-warning icons-alert" style="padding: 1.5em">
                                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                            <i class="icofont icofont-close-line-circled"></i>
                                                        </button>
                                                        <p ><strong >No Customer!</strong> </p>
                                                    </div>
                                                   @endif
                                            </div>
                                        </div>

                                        {{-- <div class="col-lg-12 col-xl-6">
                                             <div class="table-responsive">
                                                 <table class="table">
                                                     <tbody>
                                                     <tr>
                                                         <th scope="row">Email
                                                         </th>
                                                         <td><a href="#!"><span
                                                                     class="__cf_email__"
                                                                     data-cfemail="5f1b3a32301f3a273e322f333a713c3032">[email&#160;protected]</span></a>
                                                         </td>
                                                     </tr>
                                                     <tr>
                                                         <th scope="row">Mobile
                                                             Number
                                                         </th>
                                                         <td>(0123) - 4567891
                                                         </td>
                                                     </tr>
                                                     <tr>
                                                         <th scope="row">
                                                             Twitter
                                                         </th>
                                                         <td>@xyz</td>
                                                     </tr>
                                                     <tr>
                                                         <th scope="row">Skype
                                                         </th>
                                                         <td>demo.skype</td>
                                                     </tr>
                                                     <tr>
                                                         <th scope="row">
                                                             Website
                                                         </th>
                                                         <td><a href="#!">www.demo.com</a>
                                                         </td>
                                                     </tr>
                                                     </tbody>
                                                 </table>
                                             </div>
                                         </div>--}}

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                  {{--  <div class="edit-info">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="general-info">
                                    <form id="guarantor" action="{{route('guarantors.update', ['id' => encrypt($customer->id)])}}" method="post">
                                        <input type="hidden" name="_method" value="PUT">
                                        @csrf

                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="gname">Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                                    <input type="text" id="gname" name="gname" value="{{ old('gname', isset($guarantor->gname) ? $guarantor->gname : '')}}" class="form-control{{ $errors->has('gname') ? ' is-invalid' : '' }}" required>

                                                    @if ($errors->has('gname'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('gname') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="gphone">Phone <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                                    <input type="text" id="gphone" name="gphone" value="{{ old('gphone', isset($guarantor->gphone) ? $guarantor->gphone : '')}}" class="form-control{{ $errors->has('gphone') ? ' is-invalid' : '' }}" placeholder="0708000000" required>
                                                    @if ($errors->has('gphone'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('gphone') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="gid">National ID <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                                    <input type="number" name="gid" value="{{ old('gid', isset($guarantor->gid) ? $guarantor->gid : '')}}" class="form-control{{ $errors->has('gid') ? ' is-invalid' : '' }}" required>
                                                    @if ($errors->has('gid'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('gid') }}</strong>
                                    </span>
                                                    @endif

                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">

                                            <div class="col-md-4">
                                                <label for="gdob">Date of Birth <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                                                    <input type="text" id="datetimepicker1" name="gdob" value="{{ old('gdob', isset($guarantor->gdob) ? $guarantor->gdob : '')}}" class="datepicker form-control{{ $errors->has('gdob') ? ' is-invalid' : '' }}" required>

                                                    @if ($errors->has('gdob'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('gdob') }}</strong>
                                    </span>
                                                    @endif

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="location">Location <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-location-pin"></i></span>
                                                    <input type="text" id="location" name="location" value="{{ old('location', isset($guarantor->location) ? $guarantor->location : '')}}" class="form-control{{ $errors->has('location') ? ' is-invalid' : '' }}" required>
                                                    <input type="hidden" id="Lat" name="latitude"
                                                           value="{{ old('latitude', isset($guarantor->latitude) ? $guarantor->latitude : '')}}"/>
                                                    <input type="hidden" id="Lng" name="longitude"
                                                           value="{{ old('longitude', isset($guarantor->longitude) ? $guarantor->longitude : '')}}"/>
                                                    @if ($errors->has('location'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('location') }}</strong>
                                    </span>
                                                    @endif

                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="marital_status">Marital Status <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-users-alt-4"></i></span>
                                                    <select class="form-control{{ $errors->has('marital_status') ? ' is-invalid' : '' }}" name="marital_status" required>
                                                        <option value="single">Single</option>
                                                        <option value="married">Married</option>


                                                    </select>
                                                    @if ($errors->has('marital_status'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('marital_status') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="industry_id">Industry <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-industries-alt-2"></i></span>
                                                    <select class="js-example-basic-single form-control{{ $errors->has('industry_id') ? ' is-invalid' : '' }}" name="industry_id" required>
                                                        @foreach($industries as $industry)

                                                            <option
                                                                value="{{$industry->id}}" {{ isset($guarantor->industry_id) ? (($guarantor->industry_id == $industry->id) ? 'selected' : '') : $industry->id }}>
                                                                {{$industry->iname}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('industry_id'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('industry_id') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="business_id">Business <span class="text-danger">*</span></label>


                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                                    <select class="js-example-basic-single form-control{{ $errors->has('industry_id') ? ' is-invalid' : '' }}" name="business_id" required>
                                                        @foreach($business as $busines)

                                                            <option
                                                                value="{{$busines->id}}" {{ isset($guarantor->business_id) ? (($guarantor->business_id == $busines->id) ? 'selected' : '') : $busines->id }}>
                                                                {{$busines->bname}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('business_id'))
                                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('business_id') }}</strong>
                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            --}}{{-- <div class="input-group date input-group-date-custom">
                                                 <input type="text" class="form-control">
                                                 <span class="input-group-addon ">
                 <i class="icofont icofont-clock-time"></i>
                 </span>
                                             </div>--}}{{--




                                        </div>
                                        <button class="btn btn-primary float-left">Submit</button>

                                    </form>



                                </div>

                            </div>

                        </div>

                    </div>--}}

                </div>

            </div>
        </div>
    </div>
@stop


@section('js')
    <script src="{{asset('assets/pages/user-profile.js')}}"></script>
    <script type="text/javascript" src="{{asset('bower_components/select2/js/select2.full.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/pages/advance-elements/select2-custom.js')}}"></script>
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>
    <script src="{{asset('bower_components/jquery-validation/js/jquery.validate.js')}}"></script>
    <script src="{{asset('assets/pages/form-validation/validate.js')}}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCLIqgCJnFDqQkRY0XTLi_COw_VGJzxCg&libraries=places"></script>





    <script>
        jQuery(document).ready(function () {

            "use strict";
            jQuery('#datetimepicker1').datetimepicker({

                //format:'Y-m-d H:i'
                format:'Y-m-d'

            });

            jQuery("#guarantor").validate({
                ignore: [],
                rules: {

                    latitude: {
                        required: true,
                    },
                    /* longitude: {
                         required: true,
                     },*/


                },
                messages: {
                    latitude: {
                        required: 'please provide a valid place',
                    },
                    /* longitude: {
                         required: 'please provide a valid place',
                     },*/


                },


                highlight: function (element) {
                    jQuery(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    jQuery(element).closest('.form-group').removeClass('has-error');
                }
            });


            google.maps.event.addDomListener(window, 'load', initAutocomplete);

            function initAutocomplete() {
                var input = document.getElementById('location');
                var autocomplete = new google.maps.places.Autocomplete(input);
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();
                    //document.getElementById('city2').value = place.name;
                    document.getElementById('Lat').value = place.geometry.location.lat();
                    document.getElementById('Lng').value = place.geometry.location.lng();
                    console.log(place.geometry.location.lat());
                    console.log(place.geometry.location.lng());
                    //alert("This function is working!");
                    //alert(place.name);
                    // alert(place.address_components[0].long_name);

                });


            }

        })

    </script>


@stop
