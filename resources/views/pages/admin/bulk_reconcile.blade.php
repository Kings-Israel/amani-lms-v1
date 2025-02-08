@extends("layouts.master")
@section("css")
    <link rel="stylesheet" href="{{asset('bower_components/select2/css/select2.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/datepicker/jquery.datetimepicker.css')}}">
@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')


            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}

                    {{-- Display the reconciliation summary --}}
                    @if (session('summary'))
                        <ul>
                            <li>Total Records: {{ session('summary')['total_records'] }}</li>
                            <li>Records Updated: {{ session('summary')['updated_records'] }}</li>
                            <li>Already Existing Records: {{ session('summary')['found_records'] }}</li>
                            <li>Records with Non-Existing Customers: {{ session('summary')['non_existing_customers'] }}</li>
                            <li>Parsing Errors: {{ session('summary')['parsing_errors'] }}</li>
                            <li>Type Errors: {{ session('summary')['type_errors'] }}</li>
                        </ul>

                        {{-- Display error messages if any --}}
                        @if (session('summary')['error_messages'])
                            @foreach (session('summary')['error_messages'] as $message)
                                <div class="alert alert-danger">
                                    {{ $message }}
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-success">
                    {{ session('success') }}

                    {{-- Display the reconciliation summary --}}
                    @if (session('summary'))

                        {{-- Display error messages if any --}}
                        @if (session('summary')['error_messages'])
                            @foreach (session('summary')['error_messages'] as $message)
                                <div class="alert alert-danger">
                                    {{ $message }}
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            @endif

            <div class="card">
                <div class="card-block">
                <form action="{{ route('reconcile_bulk_data') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="file">Upload Excel File</label>
                        <input type="file" name="file" class="form-control{{ $errors->has('file') ? ' is-invalid' : '' }}" required>
                        @if ($errors->has('file'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('file') }}</strong>
                            </span>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

                </div>
                <div class="card-footer">
                    <p>Reconcile Transactions</p>
                    <a href="{{ asset('sample_transaction_format.xlsx') }}" class="btn btn-info" download>Download Sample Format</a>
                </div>


            </div>

        </div>
    </div>

@stop
@section('js')

    <script type="text/javascript" src="{{asset('bower_components/select2/js/select2.full.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/pages/advance-elements/select2-custom.js')}}"></script>
    <script src="{{ asset('assets/plugins/datepicker/jquery.datetimepicker.full.js') }}"></script>





    <script>
        jQuery(document).ready(function () {

            "use strict";
            jQuery('#datetimepicker1').datetimepicker({

                format:'Y-m-d H:i'
                //format: 'Y-m-d'

            });
        })
        </script>
    @stop
