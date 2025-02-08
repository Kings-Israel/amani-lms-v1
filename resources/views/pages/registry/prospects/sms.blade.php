@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form action="{{route('prospect_sms_post')}}" method="post">
                        @if($is_selected)

                        <input type="hidden" name="selected" value="{{json_encode($selected) }}">
                            @elseif($is_customer)
                            <input type="hidden" name="is_customer" value="{{$is_customer }}">
                            @elseif($is_guarantor)
                            <input type="hidden" name="is_guarantor" value="{{$is_guarantor }}">


                        @endif
                        @csrf

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="message">Name</label>
                                <textarea rows="5" name="message" class="form-control"
                                          id="compose-textarea2" placeholder="Type your message..."
                                          required> </textarea>


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
