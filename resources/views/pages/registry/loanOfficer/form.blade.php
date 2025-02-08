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
                        <form action="{{route('field_agent.update', ['id' => $field_agent->id])}}" method="post">
                            <input type="hidden" name="_method" value="PUT">

                        @else
                    <form action="{{route('field_agent.store')}}" method="post">
                        @endif
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="name" name="name" value="{{ old('name', isset($field_agent->name) ? $field_agent->name : '')}}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('name'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="phone">Phone <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                    <input type="text" id="phone" name="phone" value="{{ old('ophone', isset($field_agent->phone) ? $field_agent->phone : '')}}" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('ophone'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                    <input type="text" name="email" value="{{ old('email', isset($field_agent->email) ? $field_agent->email : '')}}" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('email'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="branch_id">Branch <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                    <select @if($is_edit)disabled @endif class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
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
                                <label for="number">Salary <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">SA</span>
                                    <input type="number" name="salary" value="{{ old('salary', isset($field_agent->salary) ? $field_agent->salary : '')}}" class="form-control{{ $errors->has('salary') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('salary'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('salary') }}</strong>
                                    </span>
                                @endif
                            </div>
{{--
                            <div class="col-md-6">
                                <label for="collection_target">Collections Target</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-angle"></i></span>
                                    <input type="number" name="collection_target" value="{{ old('collection_target', isset($field_agent->collection_target) ? $field_agent->collection_target : '')}}" class="form-control{{ $errors->has('collection_target') ? ' is-invalid' : '' }}">


                                </div>
                                @if ($errors->has('collection_target'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('collection_target') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="disbursement_target">Disbursements Target </label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-mathematical-alt-2"></i></span>
                                    <input type="number" name="disbursement_target" value="{{ old('disbursement_target', isset($field_agent->disbursement_target) ? $field_agent->disbursement_target : '')}}" class="form-control{{ $errors->has('disbursement_target') ? ' is-invalid' : '' }}">


                                </div>
                                @if ($errors->has('disbursement_target'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('disbursement_target') }}</strong>
                                    </span>
                                @endif
                            </div>--}}


                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add system loan officer</p>
                </div>


            </div>

        </div>
    </div>

@stop
