@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form id="employer" action="{{route('employers.store')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="ename">Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="ename" name="ename" value="{{ old('name', isset($employer->ename) ? $employer->ename : '')}}" class="form-control{{ $errors->has('ename') ? ' is-invalid' : '' }}" required>

                                    @if ($errors->has('ename'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('ename') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="ephone">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                    <input type="text" id="ephone" name="ephone" value="{{ old('ephone', isset($employer->ephone) ? $employer->ephone : '')}}" class="form-control{{ $errors->has('ephone') ? ' is-invalid' : '' }}" required>
                                    @if ($errors->has('ophone'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('ophone') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="eemail">Email</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                    <input type="email" name="eemail" value="{{ old('oemail', isset($employer->eemail) ? $employer->eemail : '')}}" class="form-control{{ $errors->has('eemail') ? ' is-invalid' : '' }}" required>
                                    @if ($errors->has('eemail'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('eemail') }}</strong>
                                    </span>
                                    @endif

                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="location">Location</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-location-pin"></i></span>
                                    <input type="text" name="location" id="location" value="{{ old('location', isset($employer->location) ? $employer->location : '')}}" class="form-control{{ $errors->has('location') ? ' is-invalid' : '' }}" required>
                                    <input type="hidden" id="Lat" name="latitude"
                                           value="{{ old('latitude', isset($employer->latitude) ? $employer->latitude : '')}}"/>
                                    <input type="hidden" id="Lng" name="longitude"
                                           value="{{ old('longitude', isset($employer->longitude) ? $employer->longitude : '')}}"/>



                                @if ($errors->has('location'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('location') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>


                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add employer to the system</p>
                </div>


            </div>

        </div>
    </div>

@stop

@section('js')
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCLIqgCJnFDqQkRY0XTLi_COw_VGJzxCg&libraries=places"></script>
    <script src="{{asset('bower_components/jquery-validation/js/jquery.validate.js')}}"></script>


    <script>
        jQuery(document).ready(function () {

            "use strict";

            jQuery("#employer").validate({
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
