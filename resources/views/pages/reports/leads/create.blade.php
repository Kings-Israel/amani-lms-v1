@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form action="{{route('lead_post')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="name">Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                    <input type="text" id="name" name="name" value="{{ old('name')}}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>

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
                                    <input type="text" id="phone" name="phone" value="{{ old('phone')}}" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required>
                                </div>
                                @if ($errors->has('phone'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="email">Type of Business</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">B</span>
                                    <input type="text" name="business" value="{{ old('business')}}"  class="form-control{{ $errors->has('business') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('business'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('business') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="location">Location</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">L</span>
                                    <input type="text" name="location" value="{{ old('location')}}"  class="form-control{{ $errors->has('location') ? ' is-invalid' : '' }}" required>


                                </div>
                                @if ($errors->has('location'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('location') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <style>
                                .hide{
                                    display: none;
                                }
                            </style>



                            <div class="col-md-6">
                                <label for="amount">Qualified Amount</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">A</span>
                                    <input type="number" name="amount" value="{{ old('amount')}}"  class="form-control{{ $errors->has('amount') ? ' is-invalid' : '' }}" >


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
                    <p>Add system Lead</p>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a class="btn btn-success" href="{{ route('import_lead') }}">Import Excel</a>
                        </div>
                        <div>
                            <a href="{{ route('leads') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
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
