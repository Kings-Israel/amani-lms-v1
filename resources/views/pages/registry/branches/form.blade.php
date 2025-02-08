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
                        <form action="{{route('branches.update', ['id' => $brach->id])}}" method="post">
                            <input type="hidden" name="_method" value="PUT">


                        @else
                    <form action="{{route('branches.store')}}" method="post">
                        @endif
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="bname">Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="bname" name="bname" value="{{ old('bname', isset($brach->bname) ? $brach->bname : '')}}" class="form-control{{ $errors->has('bname') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('bname'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('bname') }}</strong>
                                    </span>
                                @endif

                            </div>
                            <div class="col-md-6">
                                <label for="bphone">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                    <input type="number" id="bphone" name="bphone" value="{{ old('ophone', isset($brach->bphone) ? $brach->bphone : '')}}" class="form-control{{ $errors->has('bphone') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('bphone'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('bphone') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="bemail">Email</label>

                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                    <input type="text" id="bemail" name="bemail" value="{{ old('bemail', isset($brach->bemail) ? $brach->bemail : '')}}" class="form-control{{ $errors->has('bemail') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('bemail'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('bemail') }}</strong>
                                    </span>
                                @endif



                            </div>
                            <div class="col-md-6">
                                <label for="paybill">Paybill</label>

                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">P</span>
                                    <input type="text" id="paybill" name="paybill" value="{{ old('paybill', isset($brach->paybill) ? $brach->paybill : '')}}" class="form-control{{ $errors->has('paybill') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('paybill'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('paybill') }}</strong>
                                    </span>
                                @endif



                            </div>


                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add new system branch</p>
                </div>


            </div>

        </div>
    </div>

@stop
