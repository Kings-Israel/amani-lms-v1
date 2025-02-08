@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')

<div class="card">
    <div class="card-header">
        <h5 class="card-header-text">About Me</h5>
        <button id="edit-btn" type="button"
                class="btn btn-sm btn-primary waves-effect waves-light f-right">
            <i class="icofont icofont-edit"></i>
        </button>
    </div>
    <div class="card-block">
        <div class="view-info">
            <div class="row">
                <div class="col-lg-12">
                    <div class="general-info">
                        <div class="row">
                            <div class="col-lg-12 {{--col-xl-6--}}">
                                <div class="table-responsive">
                                    <table class="table m-0">
                                        <tbody>
                                        <tr>
                                            <th scope="row">Full
                                                Name
                                            </th>
                                            <td>{{$user->name}}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Email</th>
                                            <td>{{$user->email}}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Phone </th>
                                            <td>{{$user->phone}}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Status</th>
                                            <td>@if($user->status == 1)Active @else Inactive @endif</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Branch</th>
                                            <td>{{$user->branch}}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Role</th>
                                            <td>{{$user->roles()->first()->name}}</td>
                                        </tr>
                                        @if($user->roles()->first()->name != 'admin')
                                        <tr>
                                            <th scope="row">Salary</th>
                                            <td>{{number_format($user->salary, 2)}}</td>
                                        </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                           {{-- <div class="col-lg-12 col-xl-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <th scope="row">Email
                                            </th>
                                            <td><a href="#!"><span
                                                        class="__cf_email__"
                                                        data-cfemail="5f1b3a32301f3a273e322f333a713c3032">[email&#160;protected]</span></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Mobile
                                                Number
                                            </th>
                                            <td>(0123) - 4567891
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                Twitter
                                            </th>
                                            <td>@xyz</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Skype
                                            </th>
                                            <td>demo.skype</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                Website
                                            </th>
                                            <td><a href="#!">www.demo.com</a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>--}}

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="edit-info">
            <div class="row">
                <div class="col-lg-12">
                    <div class="general-info">
                        <form action="{{route('admin.update', ['id'=>encrypt($user->id)])}}" method="post">
                            <input type="hidden" name="_method" value="PUT">

                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <label for="name">Name</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                        <input type="text" id="name" name="name" value="{{ old('name', isset($user->name) ? $user->name : '')}}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>

                                        @if ($errors->has('name'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-phone-circle"></i></span>
                                        <input type="text" id="phone" name="phone" value="{{ old('ophone', isset($user->phone) ? $user->phone : '')}}" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required>
                                        @if ($errors->has('phone'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1">@</span>
                                        <input type="text" name="email" value="{{ old('oemail', isset($user->email) ? $user->email : '')}}" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required>
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="branch_id">Branch</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                        <select id="branch_id" class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                            @foreach($branches as $branch)

                                                <option
                                                    value="{{$branch->id}}" {{ isset($user->branch_id) ? (($user->branch_id == $branch->id) ? 'selected' : '') : $branch->id }}>
                                                    {{$branch->bname}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('branch_id'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('branch_id') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <style>
                                    .hide{
                                        display: none;
                                    }
                                </style>

                                <div class="col-md-6 @if(!$user->hasRole('collection_officer')) hide @endif" id="field_agent_id">
                                    <label for="field_agent_id">Loan officers <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                        <select id="ros"  class="form-control{{ $errors->has('field_agent_id') ? ' is-invalid' : '' }}" name="field_agent_id" >
                                            @foreach($los as $lo)

                                                <option
                                                    value="{{$lo->id}}" {{ isset($user->field_agent_id) ? (($user->field_agent_id == $lo->id) ? 'selected' : '') : $lo->id }}>
                                                    {{$lo->name}}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>
                                    @if ($errors->has('field_agent_id'))
                                        <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('field_agent_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
{{--                                @endif--}}


                                <div class="col-md-6">
                                    <label for="role">Role</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-key"></i></span>
                                        <select id="role" class="form-control{{ $errors->has('role') ? ' is-invalid' : '' }}" name="role" required>
                                            @foreach($roles as $role)

                                                <option
                                                    value="{{$role->name}}" {{ isset($user) ? (($user->roles()->pluck('name')->implode(' ') == $role->name) ? 'selected' : '') : $branch->id }}>
                                                    {{$role->name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('role'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('role') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                {{--@if($user->roles()->pluck('name')->implode(' ') != 'admin')--}}

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

                                {{--@endif--}}




                                <div class="col-md-6">
                                    <label for="password">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-key"></i></span>
                                        <input id="password" type="password" class="form-control" name="password" >

                                    @if ($errors->has('password'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                        @endif

                                    </div>
                                </div>





                            </div>
                            <div class="">
                                <button
                                   class="btn btn-primary waves-effect waves-light m-r-20">Save</button>
                                <a href="#!" id="edit-cancel"
                                   class="btn btn-default waves-effect">Cancel</a>
                            </div>

                        </form>



                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
        </div>
    </div>
    @stop


@section('js')
    <script src="{{asset('assets/pages/user-profile.js')}}"></script>
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
