@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    @if($is_edit)
                        <form action="{{route('check-off-employers.update', ['id' => $employer->id])}}" method="post">
                            @method('PUT')
                        @else
                    <form action="{{route('check-off-employers.store')}}" method="post">
                        @endif
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name">Name of Institution</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-building"></i></span>
                                    <input type="text" id="name" name="name" value="{{ old('name', isset($employer->name) ? $employer->name : '')}}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('name'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="location">Location of Institution</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-location-pin"></i></span>
                                    <input type="text" id="location" name="location" value="{{ old('location', isset($employer->location) ? $employer->location : '')}}" class="form-control{{ $errors->has('location') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('location'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('location') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="contact_name">Contact Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name', isset($employer->contact_name) ? $employer->contact_name : '')}}" class="form-control{{ $errors->has('contact_name') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('contact_name'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('contact_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="contact_phone_number">Contact Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                    <input type="text" id="contact_phone_number" name="contact_phone_number" value="{{ old('contact_phone_number', isset($employer->contact_phone_number) ? $employer->contact_phone_number : '')}}" class="form-control{{ $errors->has('contact_phone_number') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('contact_phone_number'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('contact_phone_number') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Submit</button>
                        @if($is_edit)
                    </form>
                        @else
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>

@stop
