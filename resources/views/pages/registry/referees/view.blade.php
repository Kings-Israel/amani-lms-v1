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
                    <h5 class="card-header-text">About Referee</h5>
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
                                                        <th scope="row">Full Name</th>
                                                        <td>{{ $referee->full_name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Phone</th>
                                                        <td>{{ $referee->phone_number }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">ID NUMBER</th>
                                                        <td>{{ $referee->id_number }}</td>
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="edit-info">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="general-info">
                                    <form id="referee" action="{{ route('referee.update', ['id' => encrypt($referee->id)]) }}" method="post">
                                        <input name="_method" type="hidden" value="PUT">
                                        @csrf

                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="full_name">Full Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name', isset($referee->full_name) ? $referee->full_name : '') }}" class="form-control{{ $errors->has('full_name') ? ' is-invalid' : '' }}" required>
                                                    @if ($errors->has('full_name'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('full_name') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="phone_number">Phone <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', isset($referee->phone_number) ? $referee->phone_number : '') }}" class="form-control{{ $errors->has('phone_number') ? ' is-invalid' : '' }}" placeholder="0708000000" required>
                                                    @if ($errors->has('phone_number'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('phone_number') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="id_number">National ID <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                                    <input type="number" name="id_number" value="{{ old('id_number', isset($referee->id_number) ? $referee->id_number : '') }}" class="form-control{{ $errors->has('id_number') ? ' is-invalid' : '' }}" required>
                                                    @if ($errors->has('id_number'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('id_number') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
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
                                                            <th scope="row">ID Number</th>
                                                            <td>{{$customer->id_no}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Phone </th>
                                                            <td>{{$customer->phone}}</td>
                                                        </tr>
                                                       


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



                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

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
