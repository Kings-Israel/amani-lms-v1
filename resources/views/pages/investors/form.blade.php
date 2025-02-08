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
                        <form action="{{route('investors.update', ['id' => encrypt($investor->id)])}}" method="post">
                            <input type="hidden" name="_method" value="PUT">


                        @else
                    <form action="{{route('investors.store')}}" method="post">
                        @endif
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="name">Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="name" name="name" value="{{ old('name', isset($investor->name) ? $investor->name : '')}}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('name'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="phone">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone', isset($investor->phone) ? $investor->phone : '')}}" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('ophone'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="email">Email</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                    <input type="text" name="email" value="{{ old('email', isset($investor->email) ? $investor->email : '')}}" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('email'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="branch_id">Branch</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                    <select id="branch_id" class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                        @foreach($branches as $branch)

                                            <option
                                                value="{{$branch->id}}" {{ isset($investor->branch_id) ? (($investor->branch_id == $branch->id) ? 'selected' : '') : $branch->id }}>
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
                                <label for="role">Role</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-key"></i></span>
                                    <select class="form-control{{ $errors->has('role') ? ' is-invalid' : '' }}" name="role" required>
                                        @foreach($roles as $role)

                                            <option
                                                value="{{$role->name}}" {{ isset($user) ? (($user->roles()->pluck('name')->implode(' ') == $role->name) ? 'selected' : '') : $branch->id }}>
                                                {{$role->name}}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                                @if ($errors->has('role'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('role') }}</strong>
                                    </span>
                                @endif
                            </div>


                            @if(!$is_edit)

                            <div class="col-md-6">
                                <label for="amount">Investment Amount</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">A</span>
                                    <input id="amount" type="number" name="amount" value="{{ old('amount', isset($investor->amount) ? $investor->amount : '')}}" class="form-control{{ $errors->has('amount') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('amount'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                            </div>
                                @endif




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
