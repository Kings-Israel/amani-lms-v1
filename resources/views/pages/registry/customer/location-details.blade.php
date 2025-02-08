@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <form action="{{route('customer-location-details.update', encrypt($customer->id))}}" method="post" enctype="multipart/form-data">

                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <label for="product_name">Customer Details <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-briefcase-alt-2"></i></span>
                                    <input type="text" readonly id="customer_details" name="customer_details" value="{{ old('customer_details', isset($customer) ? $customer->fullNameUpper .' '.$customer->phone  : '')}}" class="form-control{{ $errors->has('customer_details') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('customer_details'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('customer_details') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- postal address -->
                            <div class="col-md-4 my-1">
                                <label class="block">Postal Address</label>
                                <div class="input-group mb-1">
                                  <span class="input-group-addon">
                                    <i class="icofont icofont-envelope-open"></i>
                                  </span>
                                    <input name="postal_address" type="text" class="form-control" placeholder="Postal Address" value="{{ old('postal_address', $customer->location->postal_address) }}" >
                                </div>
                                @if ($errors->has('postal_address'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('postal_address') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- postal code -->
                            <div class="col-md-4 my-1">
                                <label class="block">Postal Code</label>
                                <div class="input-group mb-1">
                                  <span class="input-group-addon">
                                    <i class="icofont icofont-envelope-open"></i>
                                  </span>
                                    <input  value="{{ old('postal_code', $customer->location->postal_address) }}"
                                        name="postal_code"
                                        type="number"
                                        class="form-control"
                                        placeholder="Postal Code"
                                    >
                                </div>
                                @if ($errors->has('postal_code'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('postal_code') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- physical address -->
                            <div class="col-md-4 my-1">
                                <label class="block">Home Physical Address</label>
                                <div class="input-group mb-1">
                              <span class="input-group-addon">
                                <i class="icofont icofont-location-pin"></i>
                              </span>
                                    <input id="physical_address" value="{{ old('physical_address', $customer->location->physical_address) }}"
                                           name="physical_address"
                                           type="text"
                                           class="form-control"
                                           placeholder="Home Physical Address"
                                    >
                                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $customer->location->latitude) }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $customer->location->longitude) }}">
                                </div>
                                @if ($errors->has('physical_address'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('physical_address') }}</strong>
                                    </span>
                                @endif
                                @if ($errors->has('latitude'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('latitude') }}</strong>
                                    </span>
                                @endif
                                @if ($errors->has('longitude'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('longitude') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- residence type -->
                            <div class="col-md-4 my-1">
                                <label class="block">Residence type</label>
                                <div class="input-group mb-1">
                                  <span class="input-group-addon">
                                    <i class="icofont icofont-ui-home"></i>
                                  </span>
                                    <select id="residence_type" name="residence_type" class="form-control" >
                                        <option value="Rented" {{($customer->location->residence_type == 'Rented') ? 'selected' : ''}} >Rented</option>
                                        <option value="Owned" {{($customer->location->residence_type == 'Owned') ? 'selected' : ''}}>Owned</option>
                                    </select>
                                </div>
                                @if ($errors->has('residence_type'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('residence_type') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- years lived at residence -->
                            <div class="col-md-4 my-1">
                                <label class="block">Years Lived At Residence</label>
                                <div class="input-group mb-1">
                                  <span class="input-group-addon">
                                    <i class="icofont icofont-ui-calendar"></i>
                                  </span>
                                    <input value="{{ old('years_lived_at_residence', $customer->location->years_lived) }}"
                                        name="years_lived"
                                        type="number"
                                        class="form-control"
                                        placeholder="0"
                                    >
                                </div>
                                @if ($errors->has('years_lived_at_residence'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('years_lived_at_residence') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <!-- business address -->
                            <div class="col-md-4 my-1" >
                                <label class="block">Business Physical Address</label>
                                <div class="input-group mb-1">
                                  <span class="input-group-addon">
                                    <i class="icofont icofont-location-pin"></i>
                                  </span>
                                    <input id="business_physical_address" value="{{ old('business_address', $customer->location->business_address) }}"
                                           name="business_address"
                                           type="text"
                                           class="form-control"
                                           placeholder
                                    >
                                    <input type="hidden" name="business_latitude" id="business_latitude" value="{{ old('business_latitude', $customer->location->business_latitude) }}">
                                    <input type="hidden" name="business_longitude" id="business_longitude" value="{{ old('business_longitude', $customer->location->business_longitude) }}">
                                </div>
                                @if ($errors->has('business_physical_address'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('business_physical_address') }}</strong>
                                    </span>
                                @endif
                                @if ($errors->has('business_latitude'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('business_latitude') }}</strong>
                                    </span>
                                @endif
                                @if ($errors->has('business_longitude'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('business_longitude') }}</strong>
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

    <div class="row">
        <div class="col-md-12">
            <div class="card mt-5">
                <div class="row justify-content-center">
                    <div id="map-canvas" style="height: 425px; width: 100%; position: relative; overflow: hidden;"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')

    <script>
        //Google Maps - Home Address
        const autocompleteHome = new google.maps.places.Autocomplete(
            document.getElementById("physical_address"),
        );
        autocompleteHome.setComponentRestrictions({
            country: ["ke"]
        })

        google.maps.event.addListener(autocompleteHome, 'place_changed', ()=> {
            const home_coordinates = autocompleteHome.getPlace();

            document.getElementById('physical_address').value = home_coordinates.name;
            document.getElementById('latitude').value = home_coordinates.geometry.location.lat();
            document.getElementById('longitude').value = home_coordinates.geometry.location.lng();
        });

        //Google Maps - Business Address
        const autocompleteBusiness = new google.maps.places.Autocomplete(
            document.getElementById("business_physical_address"),
        );
        autocompleteBusiness.setComponentRestrictions({
            country: ["ke"]
        })

        google.maps.event.addListener(autocompleteBusiness, 'place_changed',  ()=> {
            const business_coordinates = autocompleteBusiness.getPlace();

            document.getElementById('business_physical_address').value = business_coordinates.name;
            document.getElementById('business_latitude').value = business_coordinates.geometry.location.lat();
            document.getElementById('business_longitude').value = business_coordinates.geometry.location.lng();

        });
    </script>

    <script defer>
        function initialize() {
            var mapOptions = {
                zoom: 5,
                minZoom: 6,
                maxZoom: 17,
                zoomControl:true,
                zoomControlOptions: {
                    style:google.maps.ZoomControlStyle.DEFAULT
                },
                center: new google.maps.LatLng(0.17687, 37.90833),
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                scrollwheel: true,
                panControl:false,
                mapTypeControl:false,
                scaleControl:false,
                overviewMapControl:false,
                rotateControl:false
            }
            var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            var image = new google.maps.MarkerImage("{{ asset('assets/img/pin.png') }}", null, null, null, new google.maps.Size(25,30));
            var places = @json($map_locations);

            for(place in places)
            {
                place = places[place];
                if(place.latitude && place.longitude)
                {
                    var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(place.latitude, place.longitude),
                        icon:image,
                        map: map,
                        title: place.name
                    });
                    var infowindow = new google.maps.InfoWindow();
                    google.maps.event.addListener(marker, 'click', (function (marker, place) {
                        return function () {
                            infowindow.setContent(generateContent(place))
                            infowindow.open(map, marker);
                        }
                    })(marker, place));
                }
            }
        }
        google.maps.event.addDomListener(window, 'load', initialize);

        function generateContent(place)
        {
            var content = `
            <div class="gd-bubble" style="">
                <div class="gd-bubble-inside">
                    <div class="geodir-bubble_desc">
                    <div class="geodir-bubble_image">
                        <div class="geodir-post-slider">
                            <div class="geodir-image-container geodir-image-sizes-medium_large ">
                                <div id="geodir_images_5de53f2a45254_189" class="geodir-image-wrapper" data-controlnav="1">
                                    <ul class="geodir-post-image geodir-images clearfix">
                                        <li>
                                            <div class="geodir-post-title">
                                                <h4 class="geodir-entry-title">
                                                    <a href="#" >`+place.name+`</a>
                                                </h4>
                                            </div>
                                           </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="geodir-bubble-meta-side">
                    <div class="geodir-output-location">
                    <div class="geodir-output-location geodir-output-location-mapbubble">
                        <div class="geodir_post_meta  geodir-field-post_title"><span class="geodir_post_meta_icon geodir-i-text">
                            <i class="fas fa-minus" aria-hidden="true"></i>
                            <span class="geodir_post_meta_title">`+place.address+`</div>
                        <div class="geodir_post_meta  geodir-field-address" itemscope="" itemtype="http://schema.org/PostalAddress">
                            <span class="geodir_post_meta_icon geodir-i-address"><i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                           </div>
                    </div>
                    </div>
                </div>
            </div>
            </div>
            </div>`;

            return content;

        }
    </script>
@stop
