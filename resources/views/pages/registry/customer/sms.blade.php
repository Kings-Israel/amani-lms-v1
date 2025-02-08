@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')

            <div class="card">
                <div class="card-block">
                    <form action="{{route('customers_sms_post')}}" method="post">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-6 my-1">
                                <label for="branch_id">Filter by Branch</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                    <select class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                        <option value="all">All</option>
                                        @foreach($branches as $branch)
                                            <option value="{{$branch->id}}" {{ isset($group->branch_id) ? (($group->branch_id == $branch->id) ? 'selected' : '') : $branch->id }}>
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

                            <div class="col-md-6 my-1">
                                <label for="lf">Filter by Credit Officer</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                    <select id="lf" class="form-control{{ $errors->has('lf') ? ' is-invalid' : '' }}" name="lf" required>
                                        <option value="all">All</option>
                                    @foreach($lfs as $field_agent)
                                            <option value="{{$field_agent->id}}" {{ isset($group->field_agent_id) ? (($group->field_agent_id == $field_agent->id) ? 'selected' : '') : $field_agent->id }}>
                                                {{$field_agent->name}} - {{$field_agent->branch}}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>

                                @if ($errors->has('lf'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('lf') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="message">Message</label>
                                <textarea rows="9" name="message" class="form-control"
                                          id="compose-textarea2" placeholder="Type your message..." required > </textarea>
                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Send Sms</p>
                </div>


            </div>

        </div>
    </div>

@stop
