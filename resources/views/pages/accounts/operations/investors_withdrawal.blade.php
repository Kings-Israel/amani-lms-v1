@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css"
          href="{{asset('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')}}">

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            <div class="card">
                <div class="card-block">
                    <form action="{{route('investment_withdrawal_post')}}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label for="amount">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-key"></i></span>
                                    <input type="number" id="amount" name="amount" value="{{ old('amount')}}"
                                           class="form-control{{ $errors->has('amount') ? ' is-invalid' : '' }}"
                                           required>

                                </div>
                                @if ($errors->has('amount'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="branch_id">Branch</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-bank-alt"></i></span>
                                    <select id="branch_id" class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}"
                                            name="branch_id" required>
                                        <option selected disabled>--Choose Branch--</option>
                                        @foreach($branches as $branch)

                                            <option
                                                value="{{$branch->id}}" {{old('branch_id')}}>
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


                            {{--<div class="col-md-6 form-group">--}}
                                {{--<label for="expense_type_id">Description</label>--}}
                                {{--<textarea rows="5" name="description" class="form-control"--}}
                                          {{--id="compose-textarea2" placeholder="Type your comment..."--}}
                                          {{--required> {!! old('description', isset($et->description) ? html_entity_decode($et->description ) : '')!!}</textarea>--}}


                            {{--</div>--}}

                            <div class="col-md-6">
                                <label for="expense_type_id">Investor</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i
                                            class="icofont icofont-businessman"></i></span>
                                    <select id="investor"
                                            class="form-control{{ $errors->has('investor') ? ' is-invalid' : '' }}"
                                            name="investor" required>
                                        {{--@foreach($investors as $investor)
                                            <option value="{{$investor->id}}">{{$investor->name}}</option>
                                        @endforeach--}}


                                    </select>

                                </div>
                                @if ($errors->has('investor'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('investor') }}</strong>
                                    </span>
                                @endif
                            </div>


                        </div>
                        <button class="btn btn-primary float-left">Submit</button>

                    </form>
                </div>
                <div class="card-footer">
                    <p>Add Expense to your branch</p>
                </div>


            </div>

        </div>
    </div>

@stop

@section('js')
    <script type="text/javascript"
            src="{{asset('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js')}}"></script>
    <script>
        $("body").on("change","#branch_id",function(){
            var baseurl = '{{ url('ajax_investors/') }}';

            var val = $(this).val(); //get new value

           // alert(val)
            var x =$(this).closest('.row').find('#investor');

            $.ajax({
                // url: baseurl +'/'+ $(".info_id").val(),
                url: baseurl +'/'+ val,

                //url: baseurl,
                type: 'GET',
                data: {},
                success: function(data) {
                    console.log(data)
                    if (data.success == true) {
                        //find sibling with class info_area
                        // var x = $(this).closest('.row').find('.info_area');
                        x.html( data.info.toString());
                        //  $(".info_area").html( data.info.toString());


                    } else {
                        alert('Cannot find info');
                    }

                },
                error: function(data) {
                    alert($(".info_id").val())

                }
            });
        });

        $("body").on("change","#investor",function(){
            var baseurl = '{{ url('ajax_investors_investments/') }}';

            var val = $(this).val(); //get new value

            // alert(val)
            var x =$(this).closest('.row').find('#amount');

            $.ajax({
                // url: baseurl +'/'+ $(".info_id").val(),
                url: baseurl +'/'+ val,

                //url: baseurl,
                type: 'GET',
                data: {},
                success: function(data) {
                    console.log(data)
                    if (data.success == true) {
                        //find sibling with class info_area
                        // var x = $(this).closest('.row').find('.info_area');
                        //x.html( data.info.toString());
                        //  $(".info_area").html( data.info.toString());
                        x.val(data.info)


                    } else {
                        alert('Cannot find info');
                    }

                },
                error: function(data) {
                    alert($(".info_id").val())

                }
            });
        });

    </script>


    {{--<script>
        $(function () {
            //Add text editor
            $("#compose-textarea").wysihtml5();

        });

    </script>--}}
@stop

