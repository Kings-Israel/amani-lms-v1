@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form action="{{route('kin.store')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="rname">Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="rname" name="rname" value="{{ old('rname', isset($kin->rname) ? $brach->rname : '')}}" class="form-control{{ $errors->has('rname') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('rname'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('rname') }}</strong>
                                    </span>
                                @endif

                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add new Kin relationship</p>
                </div>


            </div>

        </div>
    </div>

@stop
