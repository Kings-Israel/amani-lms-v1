@extends("layouts.master")
@section("css")

@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')

            <div class="card">
                <div class="card-block">
                    <form action="{{ route('import_leads') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="file">Upload Excel File</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Import Leads</button>
                    </form>

                    <div class="mt-3">
                        <a href="{{ asset('sample_leads.xlsx') }}" class="btn btn-success">
                            Download Sample Excel
                        </a>
                    </div>

                    <div class="mt-5 text-right">
                        <a href="{{ route('leads') }}" class="btn btn-secondary">
                            Back
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop
