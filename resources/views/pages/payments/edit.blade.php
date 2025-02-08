@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form action="{{route('settlement_transactions.update')}}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="{{encrypt($tran->id)}}">

                        <div class="row">


                            <div class="col-md-6">
                                <label for="amount">Amount</label>

                                @if($is_other)
                                    <input type="hidden" name="other" value="other">
                                    @endif
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">S</span>
                                    <input type="number" name="amount" value="{{ old('amount', isset($tran->amount) ? $tran->amount : '')}}" class="form-control{{ $errors->has('amount') ? ' is-invalid' : '' }}" >


                                </div>
                                @if ($errors->has('amount'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                            </div>




                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Edit Transaction</p>
                </div>


            </div>

        </div>
    </div>

@stop
