@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css"
          href="{{asset('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')}}">

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form action="{{route('other_settlement_post')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="amount">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-businessman"></i></span>
                                    <input type="number" id="amount" name="amount" value="{{ old('amount')}}"
                                           class="form-control{{ $errors->has('amount') ? ' is-invalid' : '' }}"
                                           required>

                                </div>
                                @if ($errors->has('amount'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="branch_id">Branch</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-bank-alt"></i></span>
                                    <select class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}"
                                            name="branch_id" required>
                                        @foreach($branches as $branch)

                                            <option
                                                value="{{$branch->id}}" {{ isset($field_agent->branch_id) ? (($field_agent->branch_id == $branch->id) ? 'selected' : '') : $branch->id }}>
                                                {{$branch->bname}}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                                @if ($errors->has('branch_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('branch_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label class="control-label" for="description">Event Description
                                            <span
                                                class="asterisk"></span></label>
                                        <textarea rows="5" name="description" class="form-control"
                                                  id="compose-textarea2" placeholder="Type your comment..."
                                                  required> {!! old('description', isset($event->description) ? html_entity_decode($event->description ) : '')!!}</textarea>
                                    </div>
                                </div>


                            <div class="col-md-6">
                                <label for="expense_type_id">Expense Type</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-key"></i></span>
                                    <select id="expense_type_id"
                                            class="form-control{{ $errors->has('expense_type_id') ? ' is-invalid' : '' }}"
                                            name="expense_type_id" required>
                                        @foreach($etype as $et)
                                            <option value="{{$et->id}}">{{$et->expense_name}}</option>
                                        @endforeach

                                    </select>

                                </div>
                                @if ($errors->has('expense_type_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('expense_type_id') }}</strong>
                                    </span>
                                @endif
                            </div>


                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add Expense to your branch</p>
                </div>


            </div>

        </div>
    </div>

@stop

@section('js')
    <script type="text/javascript"
            src="{{asset('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js')}}"></script>


    <script>
        $(function () {
            //Add text editor
            $("#compose-textarea").wysihtml5();

        });

    </script>
@stop

