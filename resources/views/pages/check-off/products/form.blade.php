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
                        <form action="{{route('check-off-products.update', ['id' => $product->id])}}" method="post">
                            @method('PUT')
                        @else
                    <form action="{{route('check-off-products.store')}}" method="post">
                        @endif
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name">Name of Product</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-building"></i></span>
                                    <input type="text" id="name" name="name" value="{{ old('name', isset($product->name) ? $product->name : '')}}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('name'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="interest">Interest To Be Charged</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">%</span>
                                    <input type="number" step="1" min="1" max="99" id="interest" name="interest" value="{{ old('interest', isset($product->interest) ? $product->interest : '')}}" class="form-control{{ $errors->has('interest') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('interest'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interest') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="period">Period <small>(In Days)</small></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-calendar"></i></span>
                                    <input type="number" min="1" max="1000" id="period" name="period" value="{{ old('period', isset($product->period) ? $product->period : '')}}" class="form-control{{ $errors->has('period') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('period'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('period') }}</strong>
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
