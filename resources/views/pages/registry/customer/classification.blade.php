@extends("layouts.master")

@section('content')

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5>Update Classification for {{ $customer->title }} {{ $customer->fname }} {{ $customer->lname }} ({{ $customer->id_no }})</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Use the form below to update the customer's classification. The classification helps categorize customers for better management and personalized services.</p>

            <div class="alert alert-info">
                <strong>Current Classification:</strong> {{ $customer->classification ?? 'Not Classified Yet' }}
            </div>

            <form action="{{ route('registry.updateClassification', $customer->id) }}" method="POST">
                @csrf
                @method('POST')

                <div class="form-group">
                    <label for="classification" class="font-weight-bold">Select New Classification</label>
                    <select name="classification" id="classification" class="form-control @error('classification') is-invalid @enderror">
                        @foreach ($classifications as $classification)
                            <option value="{{ $classification }}" {{ $customer->classification == $classification ? 'selected' : '' }}>
                                {{ $classification }}
                            </option>
                        @endforeach
                    </select>
                    @error('classification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes" class="font-weight-bold">Additional Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Add any additional notes regarding the classification change..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Update Classification</button>
            </form>

            <div class="mt-4">
                <a href="{{ route('registry.index') }}" class="btn btn-outline-secondary">Back to Customer Details</a>
            </div>
        </div>
    </div>
</div>

@endsection
