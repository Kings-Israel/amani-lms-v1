@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="panel-btns">
                                <a href="#" class="panel-close">&times;</a>
                                <a href="#" class="minimize">&minus;</a>
                            </div>
                            <h4 class="panel-title">Send Multiple Complementary Tickets</h4>
                        </div>
                        <div class="panel-body">

                            <p>Import the Customer Details in a couple of simple steps: </p>

                            <ol>
                                <li>Download the Customer upload template below</li>
                                <li>Fill in the customer information</li>
                                <li>Save the completed customer upload template to your desktop</li>
                                <li>click <strong>Choose File</strong>, Select your completed customer upload template, and
                                    click <strong>Upload</strong>.
                                </li>
                            </ol>

                            {{-- <p><strong>NOTE: </strong>Event_id Column value: <strong>{{$event->id}}</strong></p>--}}

                            <div class="col-sm-6 col-sm-offset-3 row" style="padding-bottom: 20px">
                                <a href="{{route('prospects.get_template')}}" class="btn btn-success btn-flat btn-lg">Download
                                    the Customer Upload Template</a>
                            </div>

                            <div class="col-sm-4 col-sm-offset-3 row">

                                <form role="form" method="post" action="{{route('prospects.post_template')}}"
                                      enctype="multipart/form-data">
                                    {{ csrf_field() }}

                                    <div class="col-sm-12">
                                        <div

                                            class="form-group col-sm-6  has-feedback{{ $errors->has('template') ? ' has-error' : ''}}">
                                            <input type="file" name="template" required>
                                            @if($errors->has('template'))
                                                <span class="help-block">
                                    <strong>{{ $errors->first('template') }}</strong>
                                </span>
                                            @endif
                                        </div>
                                        <div class="col-sm-6">
                                            <div style="padding-left: 20px">
                                                <input type="submit" class="btn btn-success btn-flat btn-file"
                                                       value="Upload">
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>


                        </div>

                    </div>


                </div>
                <div class="card-footer">
                    <p>Add system Prospects</p>
                </div>


            </div>

        </div>
    </div>

@stop
