@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-header row">
                    <div class="col-md-3">

                        <button type="button" class="btn-primary btn" data-toggle="modal"
                                data-target="#default-Modal">Change
                        </button>
                    </div>

                </div>
                <div class="card-block">
                    @include('layouts.alert')
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>Registration Fee</th>
                                <th>Loan Processing Fee</th>
                                <th>Ksh Rollover Interest</th>
                            </tr>
                            </thead>
                             <tbody>
                             <tr>
                                <td>{{isset($settings->registration_fee) ? $settings->registration_fee : " "}}</td>
                                <td>{{isset($settings->loan_processing_fee) ? $settings->loan_processing_fee : " "}}</td>
                                <td>{{isset($settings->rollover_interest) ? $settings->rollover_interest : " "}}</td>
                             </tr>
                             </tbody>
                            <tfoot>
                            <tr>
                                <th>Registration Fee</th>
                                <th>Loan Processing Fee</th>
                                <th>Ksh. Rollover Interest</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="default-Modal" tabindex="-1"
         role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Settings</h4>
                    <button type="button" class="close"
                            data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="dt-responsive ">
                        <form action="{{route('settings.store')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="registration_fee">Registration Fee</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-businessman"></i></span>
                                        <input type="number" id="registration_fee" name="registration_fee" value="{{ old('registration_fee', isset($settings->registration_fee) ? $settings->registration_fee : '')}}" class="form-control{{ $errors->has('registration_fee') ? ' is-invalid' : '' }}" required>

                                    </div>
                                    @if ($errors->has('registration_fee'))
                                        <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('registration_fee') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="loan_processing_fee">Loan Processing Fee</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-bill-alt"></i></span>
                                        <input type="number" id="loan_processing_fee" name="loan_processing_fee" value="{{ old('loan_processing_fee', isset($settings->loan_processing_fee) ? $settings->loan_processing_fee : '')}}" class="form-control{{ $errors->has('loan_processing_fee') ? ' is-invalid' : '' }}" required>
                                    </div>
                                    @if ($errors->has('loan_processing_fee'))
                                        <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('loan_processing_fee') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="rollover_interest">Ksh Rollover Interest</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-coins"></i></span>
                                        <input type="text" id="rollover_interest" name="rollover_interest" value="{{ old('rollover_interest', isset($settings->rollover_interest) ? $settings->rollover_interest : '')}}" class="form-control{{ $errors->has('rollover_interest') ? ' is-invalid' : '' }}" required>
                                    </div>
                                    @if ($errors->has('rollover_interest'))
                                        <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('rollover_interest') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <button class="btn btn-primary float-left">Submit</button>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-default waves-effect "
                            data-dismiss="modal">Close
                    </button>
                    {{-- <button type="button"
                             class="btn btn-primary waves-effect waves-light ">
                         Save changes
                     </button>--}}
                </div>
            </div>
        </div>
    </div>



@stop


@section('js')
    <script>

    </script>


@stop
