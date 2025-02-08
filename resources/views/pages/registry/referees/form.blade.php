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
                    <form id="referee" action="{{route('referee.store')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <label for="gname">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name', isset($referee->full_namne) ? $referee->full_name : '')}}" class="form-control{{ $errors->has('full_name') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('full_name'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('full_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="phone_number">Phone <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', isset($referee->phone_number) ? $referee->phone_number : '')}}" class="form-control{{ $errors->has('phone_number') ? ' is-invalid' : '' }}" placeholder="0708000000" required>

                                </div>
                                @if ($errors->has('phone_number'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('phone_number') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="id_number">National ID <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                    <input type="number" name="id_number" value="{{ old('id_number', isset($referee->id_number) ? $referee->id_number : '')}}" class="form-control{{ $errors->has('id_number') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('id_number'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('id_number') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                <label for="customer_id">Associated Customer  <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-industries-alt-2"></i></span>
                                    <select class="js-example-basic-single form-control{{ $errors->has('customer_id') ? ' is-invalid' : '' }}" name="customer_id" required>
                                        @foreach($customers as $customer)
                                            <option
                                                value="{{$customer->id}}" {{ isset($referee->customer_id) ? (($referee->customer_id == $customer->id) ? 'selected' : '') : $customer->id }}>
                                                {{$customer->fname}}  {{$customer->lname}} ( {{$customer->phone}} )
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if ($errors->has('customer_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('customer_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>
                        <button class="btn btn-primary float-left">Submit</button>
                    </form>
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

           /* jQuery("#referee").validate({
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
