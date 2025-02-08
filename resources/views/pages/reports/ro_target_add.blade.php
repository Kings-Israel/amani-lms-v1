@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form action="{{route('ro.target_add', ['id' => encrypt($user->id)])}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="disbursement_target_amount">Disbursement Target</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">DT</span>
                                    <input type="number" id="disbursement_target_amount" name="disbursement_target_amount" value="{{ old('disbursement_target_amount', isset($disbursement_target_amount) ? $disbursement_target_amount : '')}}" class="form-control{{ $errors->has('disbursement_target') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('disbursement_target_amount'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('disbursement_target_amount') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="customer_target">Customer Target</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">DT</span>
                                    <input type="number" id="customer_target" name="customer_target" value="{{ old('customer_taget', isset($customer_target) ? $customer_target : '')}}" class="form-control{{ $errors->has('customer_target') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('customer_target'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('customer_target') }}</strong>
                                    </span>
                                @endif
                            </div>
                          {{--  <div class="col-md-6">
                                <label for="disbursed">Actual Disbursement</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">DT</span>
                                    <input type="number" id="disbursed" name="disbursed" value="{{ old('disbursed', isset($disbursed) ? $disbursed : '')}}" class="form-control{{ $errors->has('disbursed') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('disbursed'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('disbursed') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="collection_target">Collection Target</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">CT</span>
                                    <input type="number" id="collection_target" name="collection_target" value="{{ old('collection_target', isset($collection_target) ? $collection_target : '')}}" class="form-control{{ $errors->has('collection_target') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('collection_target'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('collection_target') }}</strong>
                                    </span>
                                @endif
                            </div>--}}





                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add system user</p>
                </div>


            </div>

        </div>
    </div>

@stop
