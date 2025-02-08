@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')

            <div class="card">
                <div class="card-block">
                    <form action="{{route('admin.store')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="name">Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="name" name="name" value="{{ old('name', isset($user->name) ? $user->name : '')}}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>

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
                                    <input type="text" id="phone" name="phone" value="{{ old('phone', isset($user->phone) ? $user->phone : '')}}" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('phone'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="email">Email</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">@</span>
                                    <input type="text" name="email" value="{{ old('email', isset($user->email) ? $user->email : '')}}" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required>
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
                                        <option disabled selected>--Select Branch--</option>
                                        @foreach($branches as $branch)
                                            <option value="{{$branch->id}}" {{ isset($user->branch_id) ? (($user->branch_id == $branch->id) ? 'selected' : '') : $branch->id }}>
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
                                    <select id="role" class="form-control{{ $errors->has('role') ? ' is-invalid' : '' }}" name="role" required>
                                        <option disabled selected>--Select Role--</option>

                                        @foreach($roles as $role)
                                            @php($role_name = explode('_', $role->name))
                                            <option value="{{$role->name}}" {{ isset($user) ? (($user->roles()->pluck('name')->implode(' ') == $role->name) ? 'selected' : '') : $branch->id }}>
                                               <span>{{array_key_exists(1, $role_name) ? Str::title($role_name[0]). ' ' . Str::title($role_name[1]) : Str::title($role_name[0])}}</span>
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

                            {{-- <style>
                                .hide{
                                    display: none;
                                }
                            </style>

                            <div class="col-md-6 hide" id="field_agent_id">
                                <label for="field_agent_id">Field Agents <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                    <select id="ros" @if($is_edit)disabled @endif class="form-control{{ $errors->has('field_agent_id') ? ' is-invalid' : '' }}" name="field_agent_id" >
                                    </select>
                                </div>
                                @if ($errors->has('field_agent_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('field_agent_id') }}</strong>
                                    </span>
                                @endif
                            </div> --}}

                            <div class="col-md-6">
                                <label for="salary">Salary</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">S</span>
                                    <input type="number" name="salary" value="{{ old('salary', isset($user->salary) ? $user->salary : '')}}" class="form-control{{ $errors->has('salary') ? ' is-invalid' : '' }}" >
                                </div>
                                @if ($errors->has('salary'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('salary') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $('#branch_id').change(function(){

            var val = $(this).val(); //get new value

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "GET",
                url: '{!! env('APP_URL') !!}' + "branch_ros/" + val,
                dataType: 'json',

                success: function (json) {

                    if (json.status == 'error') {
                        $this.addClass('error');
                        $('.sub_warning').removeClass('hide')

                    } else if (json.status == 'success') {
                        $('#ros')
                            .find('option')
                            .remove()
                            .end()
                            .append(json.ros);




                    }

                }
            });


        });

        $('#role').change(function(){
            var val = $(this).val(); //get new value
            if(val === 'collection_officer'){
                $('#field_agent_id').removeClass('hide')
            } else{
                $('#field_agent_id').addClass('hide')

            }


        })

    </script>

    @stop
