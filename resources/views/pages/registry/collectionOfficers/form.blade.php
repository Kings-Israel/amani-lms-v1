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
                        <form action="{{route('collection_officer.update', ['id' => $field_agent->id])}}" method="post">
                            <input type="hidden" name="_method" value="PUT">

                        @else
                    <form action="{{route('collection_officer.store')}}" method="post">
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
                                    <input type="text" id="phone" name="phone" value="{{ old('phone', isset($field_agent->phone) ? $field_agent->phone : '')}}" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required>

                                </div>
                                @if ($errors->has('phone'))
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
                                    <select id="branch_id" @if($is_edit)disabled @endif class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>
                                        <option disabled selected>--Select Branch--</option>
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
                                <label for="field_agent_id">Loan officers <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>
                                    <select id="ros" @if($is_edit)disabled @endif class="form-control{{ $errors->has('field_agent_id') ? ' is-invalid' : '' }}" name="field_agent_id" required>

                                    </select>

                                </div>
                                @if ($errors->has('field_agent_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('field_agent_id') }}</strong>
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


                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add system collection officer</p>
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
</script>
    @stop
