@extends("layouts.master")
@section("css")
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            @if($interaction->status == 1)
                <button class="btn btn-success btn-skew" style="margin-bottom: 10px">ACTIVE</button>
            @else
                <button class="btn btn-warning btn-skew" style="margin-bottom: 10px">CLOSED</button>
            @endif
            <div class="card">
                <div class="card-header">
                </div>
                <div class="card-block">
                    {{--                    <form id="search" class="form-inline row" method="post" action="">--}}
                    {{--                        @csrf--}}
                    {{--                        <div class="col-md-4">--}}
                    {{--                            <label for="branch">Select Branch</label>--}}
                    {{--                            <div class="input-group">--}}
                    {{--                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>--}}
                    {{--                                <select class="js-example-basic-single form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}" name="branch_id" required>--}}
                    {{--                                    <option value="all" > All </option>--}}
                    {{--                                </select>--}}

                    {{--                            </div>--}}
                    {{--                            @if ($errors->has('branch_id'))--}}
                    {{--                                <span class="text-danger" role="alert">--}}
                    {{--                                        <strong>{{ $errors->first('branch_id') }}</strong>--}}
                    {{--                                    </span>--}}
                    {{--                            @endif--}}
                    {{--                        </div>--}}
                    {{--                        <div class="col-md-4">--}}
                    {{--                            <label for="branch">Select Loan Officer</label>--}}

                    {{--                            <div class="input-group">--}}
                    {{--                                <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bank-alt"></i></span>--}}
                    {{--                                <select class="js-example-basic-single form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required>--}}
                    {{--                                    <option value="all">All</option>--}}
                    {{--                                   --}}
                    {{--                                </select>--}}

                    {{--                            </div>--}}
                    {{--                            @if ($errors->has('name'))--}}
                    {{--                                <span class="text-danger" role="alert">--}}
                    {{--                                <strong>{{ $errors->first('name') }}</strong>--}}
                    {{--                            </span>--}}
                    {{--                            @endif--}}
                    {{--                        </div>--}}


                    {{--                        <div class="col-md-4">--}}
                    {{--                            <button class="btn btn-primary">Filter</button>--}}
                    {{--                        </div>--}}
                    {{--                    </form>--}}
                    <div class="dt-responsive table-responsive">
                        <div class=" col-md-12">
                            <div class="card latest-update-card">
                                <div class="card-header">
                                    <h5>Latest Updates</h5>
                                    <div class="card-header-right">
                                        <ul class="list-unstyled card-option">
                                            <li><i class="fa fa fa-wrench open-card-option"></i></li>
                                            <li><i class="fa fa-window-maximize full-card"></i></li>
                                            <li><i class="fa fa-minus minimize-card"></i></li>
                                            <li><i class="fa fa-refresh reload-card"></i></li>
                                            <li><i class="fa fa-trash close-card"></i></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-block">
                                    <div class="latest-update-box">
                                        <div class="row p-b-30">
                                            <div class="col-auto text-right update-meta">
                                                <p class="text-muted m-b-0 d-inline">{{date_format($interaction->created_at, 'Y-m-d')}}</p>
                                                <i class="feather icon-check {{$interaction->followed_up == 1 ? "bg-success" : "bg-simple-c-yellow"}}  update-icon"></i>
                                            </div>
                                            <div class="col">
                                                <h6>{{$interaction->remark}}</h6>
                                                <p class="text-muted m-b-0">Added By: <span class="text-c-blue">{{$interaction->user->name}} - {{$interaction->user->phone}}</span>
                                                </p>
                                                <p class="text-muted m-b-0">Next Schedule: <span
                                                        class="text-c-blue">{{$interaction->next_scheduled_interaction}}</span>
                                                </p>
                                                @if($interaction->followed_up != 1)

                                                    <a href="{{route('update_follow_up', ['name'=>'interaction', 'id'=>encrypt($interaction->id)])}}"
                                                       class="b-b-primary text-warning">Mark as followed
                                                        up</a>
                                                @endif


                                            </div>
                                        </div>


                                        @foreach($interaction->follow_ups as $follow_up)
                                            <div class="row p-t-20 p-b-30">
                                                <div class="col-auto text-right update-meta">
                                                    <p class="text-muted m-b-0 d-inline">{{date_format($follow_up->created_at, 'Y-m-d')}}</p>
                                                    <i class="feather icon-check {{$follow_up->status == 1 ? "bg-success" : "bg-simple-c-yellow"}} update-icon"></i>
                                                </div>
                                                <div class="col">
                                                    <h6>{{$follow_up->remark}}</h6>
                                                    <p class="text-muted m-b-0">Added By: <span class="text-c-blue">{{$follow_up->followed_by->name}} - {{$follow_up->followed_by->phone}}</span>
                                                    </p>

                                                    <p class="text-muted m-b-0">Next Schedule: <span
                                                            class="text-c-blue">{{$follow_up->next_scheduled_interaction ?? "N/A"}}</span>
                                                    </p>

                                                    @if($follow_up->status != 1)
                                                        <a href="{{route('update_follow_up', ['name'=>'followup', 'id'=>encrypt($follow_up->id)])}}"
                                                           class="b-b-primary text-warning">Mark as followed
                                                            up</a>
                                                    @endif

                                                </div>
                                            </div>
                                        @endforeach
                                        {{--                                        <div class="row p-b-30">--}}
                                        {{--                                            <div class="col-auto text-right update-meta">--}}
                                        {{--                                                <p class="text-muted m-b-0 d-inline">4 hrs ago</p>--}}
                                        {{--                                                <i class="feather icon-briefcase bg-simple-c-pink update-icon"></i>--}}
                                        {{--                                            </div>--}}
                                        {{--                                            <div class="col">--}}
                                        {{--                                                <h6>+ 5 New Products were added!</h6>--}}
                                        {{--                                                <p class="text-muted m-b-0">Congratulations!</p>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                        {{--                                        <div class="row p-b-30">--}}
                                        {{--                                            <div class="col-auto text-right update-meta">--}}
                                        {{--                                                <p class="text-muted m-b-0 d-inline">1 day ago</p>--}}
                                        {{--                                                <i class="feather icon-check bg-simple-c-yellow  update-icon"></i>--}}
                                        {{--                                            </div>--}}
                                        {{--                                            <div class="col">--}}
                                        {{--                                                <h6>Database backup completed!</h6>--}}
                                        {{--                                                <p class="text-muted m-b-0">Download the <span class="text-c-blue">latest backup</span>.</p>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                        {{--                                        <div class="row p-b-0">--}}
                                        {{--                                            <div class="col-auto text-right update-meta">--}}
                                        {{--                                                <p class="text-muted m-b-0 d-inline">2 day ago</p>--}}
                                        {{--                                                <i class="feather icon-facebook bg-simple-c-green update-icon"></i>--}}
                                        {{--                                            </div>--}}
                                        {{--                                            <div class="col">--}}
                                        {{--                                                <h6>+2 Friend Requests</h6>--}}
                                        {{--                                                <p class="text-muted m-b-10">This is great, keep it up!</p>--}}
                                        {{--                                                <div class="table-responsive">--}}
                                        {{--                                                    <table class="table table-hover">--}}
                                        {{--                                                        <tbody><tr>--}}
                                        {{--                                                            <td class="b-none">--}}
                                        {{--                                                                <a href="#!" class="align-middle">--}}
                                        {{--                                                                    <img src="../files/assets/images/avatar-2.jpg" alt="user image" class="img-radius img-40 align-top m-r-15">--}}
                                        {{--                                                                    <div class="d-inline-block">--}}
                                        {{--                                                                        <h6>Jeny William</h6>--}}
                                        {{--                                                                        <p class="text-muted m-b-0">Graphic Designer</p>--}}
                                        {{--                                                                    </div>--}}
                                        {{--                                                                </a>--}}
                                        {{--                                                            </td>--}}
                                        {{--                                                        </tr>--}}
                                        {{--                                                        <tr>--}}
                                        {{--                                                            <td class="b-none">--}}
                                        {{--                                                                <a href="#!" class="align-middle">--}}
                                        {{--                                                                    <img src="../files/assets/images/avatar-1.jpg" alt="user image" class="img-radius img-40 align-top m-r-15">--}}
                                        {{--                                                                    <div class="d-inline-block">--}}
                                        {{--                                                                        <h6>John Deo</h6>--}}
                                        {{--                                                                        <p class="text-muted m-b-0">Web Designer</p>--}}
                                        {{--                                                                    </div>--}}
                                        {{--                                                                </a>--}}
                                        {{--                                                            </td>--}}
                                        {{--                                                        </tr>--}}
                                        {{--                                                        </tbody></table>--}}
                                        {{--                                                </div>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                    </div>
                                    @if($interaction->status == 1)

                                    <div class="text-center">
                                        <button class="btn btn-success btn-round" data-toggle="modal"
                                                data-target="#exampleModal">Add a Follow Up
                                        </button>
                                        <a href="{{route('update_follow_up', ['name'=>'close', 'id'=>encrypt($interaction->id)])}}"
                                           class="btn btn-warning btn-round">Mark as closed</a>

                                    </div>
                                        @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create a New Follow Up</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('customer-interaction_followup.store')}}" method="post">
                        @csrf
                        <input type="hidden" name="follow_up_id" value="{{encrypt($interaction->id)}}">
                        <div class="form-group row">

                            <div class="col-md-4">
                                <label for="interaction_type_id">Interaction Type</label>
                                <select name='interaction_type_id' id='interaction_type_id' class="form-control">
                                    <option value="" disabled>Kindly specify the type of interaction</option>
                                    @foreach($interaction_types as $interaction_type)
                                        <option
                                            value="{{$interaction_type->id}}" {{(old('interaction_type_id') == $interaction_type->id ) ? 'selected' : ''}}>{{$interaction_type->name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('interaction_type_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interaction_type_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="datetimepicker2">Next Scheduled Interaction</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">
                                        <i class="icofont icofont-ui-calendar"></i>
                                    </span>
                                    <input id="datetimepicker2" type="date"
                                           value="{{ old('next_scheduled_interaction') }}"
                                           min="{{now()->format('Y-m-d')}}" autocomplete="off"
                                           name="next_scheduled_interaction"
                                           class="form-control {{ $errors->has('next_scheduled_interaction') ? ' is-invalid' : '' }}">
                                </div>
                                @if ($errors->has('next_scheduled_visit'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('next_scheduled_visit') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <label for="remark">Remark</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-ui-message"></i></span>
                                    <textarea type="text" cols="10" rows="10" id="remark" name="remark"
                                              class="form-control {{ $errors->has('remark') ? ' is-invalid' : '' }}"
                                              required>{{ old('remark') }}</textarea>
                                </div>
                                @if ($errors->has('remark'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('remark') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Save</button>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@stop


@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

@stop
