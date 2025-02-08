@extends("layouts.master")

@section("css")
<link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/themify-icons.css') }}">
@endsection

@section("content")
<div class="row" id="app">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-block">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}

                    @if(session('errorDetails'))
                        <ul>
                            @foreach(session('errorDetails') as $errorDetail)
                                <li>
                                    <strong>Row {{ $errorDetail['row'] }}:</strong>
                                    @if(isset($errorDetail['errors']))
                                        <ul>
                                            @foreach($errorDetail['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span>{{ $errorDetail['error'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div class="mt-4">
                <form action="{{ route('import.registry.data') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="user">Select Relationship Officer:</label>
                        <select name="relationship_officer" class="form-control" required>
                            <option value="">Select a Relationship Officer</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="file">Upload Excel File:</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Import Customers</button>
                </form>

                <div class="mt-3">
                    <a href="{{ asset('sample_customers.xlsx') }}" class="btn btn-info">
                        Download Sample Excel File
                    </a>
                </div>
            </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section("js")
<script src="{{asset('js/app.js?v=4')}}"></script>
@endsection
