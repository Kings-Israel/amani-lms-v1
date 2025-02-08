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
                        <form action="{{route('products.update', ['id' => $product->id])}}" method="post">
                            <input name="_method" type="hidden" value="PUT">
                        @else
                    <form action="{{route('products.store')}}" method="post">
                        @endif
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="product_name">Product Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-briefcase-alt-2"></i></span>
                                    <input type="text" id="product_name" name="product_name" value="{{ old('product_name', isset($product->product_name) ? $product->product_name : '')}}" class="form-control{{ $errors->has('product_name') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('product_name'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('product_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="installments">Installments <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bill-alt"></i></span>
                                    <input type="number" id="installments" name="installments" value="{{ old('installments', isset($product->installments) ? $product->installments : '')}}" class="form-control{{ $errors->has('installments') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('installments'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('installments') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="interest">Interest <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-coins"></i></span>
                                    <input type="number" name="interest" value="{{ old('interest', isset($product->interest) ? $product->interest : '')}}" class="form-control{{ $errors->has('interest') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('interest'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interest') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="duration">Duration in Days <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-tasks"></i></span>
                                    <input type="number" name="duration" value="{{ old('duration', isset($product->duration) ? $product->duration : '')}}" class="form-control{{ $errors->has('duration') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('duration'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('duration') }}</strong>
                                    </span>
                                @endif
                            </div>


                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add Loan Product</p>
                </div>


            </div>

        </div>
    </div>

@stop
