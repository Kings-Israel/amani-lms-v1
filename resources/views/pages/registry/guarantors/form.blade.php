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
                <div class="card-block">
                    <form id="guarantor" action="{{route('guarantors.store')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <label for="gname">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="gname" name="gname" value="{{ old('gname', isset($guarantor->gname) ? $guarantor->gname : '')}}" class="form-control{{ $errors->has('gname') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('gname'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('gname') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="gphone">Phone <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                    <input type="text" id="gphone" name="gphone" value="{{ old('gphone', isset($guarantor->gphone) ? $guarantor->gphone : '')}}" class="form-control{{ $errors->has('gphone') ? ' is-invalid' : '' }}" placeholder="0708000000" required>

                                </div>
                                @if ($errors->has('gphone'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('gphone') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="gid">National ID <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                    <input type="number" name="gid" value="{{ old('gid', isset($guarantor->gid) ? $guarantor->gid : '')}}" class="form-control{{ $errors->has('gid') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('gid'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('gid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="gdob">Date of Birth <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-calendar"></i></span>
                                    <input type="text" id="datetimepicker1" autocomplete="off" name="gdob" value="{{ old('gdob', isset($guarantor->gdob) ? $guarantor->gdob : '')}}" class="datepicker form-control{{ $errors->has('dob') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('gdob'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('gdob') }}</strong>
                                    </span>
                                @endif
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
                                </div>
                                @if ($errors->has('location'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('location') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="marital_status">Marital Status <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-users-alt-4"></i></span>
                                    <select class="form-control{{ $errors->has('marital_status') ? ' is-invalid' : '' }}" name="marital_status" required>
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                    </select>
                                </div>
                                @if ($errors->has('marital_status'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('marital_status') }}</strong>
                                    </span>
                                @endif
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
                                </div>
                                @if ($errors->has('industry_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('industry_id') }}</strong>
                                    </span>
                                @endif
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
                                </div>
                                @if ($errors->has('business_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('business_id') }}</strong>
                                    </span>
                                @endif
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
                <div class="card-footer">
                    <p>Add system loan officer</p>
                </div>


            </div>

        </div>
    </div>

@stop

@section('js')

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
                format:'Y-m-d'
            });

           /* jQuery("#guarantor").validate({
                ignore: [],
                rules: {

                    latitude: {
                        required: true,
                    },
                    /!* longitude: {
                         required: true,
                     },*!/


                },
                messages: {
                    latitude: {
                        required: 'please provide a valid place',
                    },
                    /!* longitude: {
                         required: 'please provide a valid place',
                     },*!/


                },


                highlight: function (element) {
                    jQuery(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    jQuery(element).closest('.form-group').removeClass('has-error');
                }
            });*/


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
