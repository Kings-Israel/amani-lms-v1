@extends('layouts.master')

@section('content')
<div class="container">
    <h3>Documents for Loan: {{ $loan->loan_account }}</h3>

    @if(!$documents['customer_id_front'] && !$documents['customer_id_back'] && !$documents['guarantor_id'] && !$documents['document_path'] && !$documents['audio_path'] && !$documents['video_path'] && !$documents['external_video_link'])
        <p class="alert alert-warning">No documents available for this loan.</p>
    @else
        <ul class="list-group">
            <!-- Customer ID Front -->
            @if($documents['customer_id_front'])
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ asset($documents['customer_id_front']) }}" target="_blank">
                            <img src="{{ asset($documents['customer_id_front']) }}" alt="Customer ID Front" style="width: 150px; height: auto;" class="img-thumbnail">
                        </a>
                        <p>Customer ID Front</p>
                    </div>
                    <a href="{{ asset($documents['customer_id_front']) }}" download class="btn btn-primary">Download</a>
                </li>
            @endif

            <!-- Customer ID Back -->
            @if($documents['customer_id_back'])
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ asset($documents['customer_id_back']) }}" target="_blank">
                            <img src="{{ asset($documents['customer_id_back']) }}" alt="Customer ID Back" style="width: 150px; height: auto;" class="img-thumbnail">
                        </a>
                        <p>Customer ID Back</p>
                    </div>
                    <a href="{{ asset($documents['customer_id_back']) }}" download class="btn btn-primary">Download</a>
                </li>
            @endif

            <!-- Guarantor ID -->
            @if($documents['guarantor_id'])
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ asset($documents['guarantor_id']) }}" target="_blank">
                            <img src="{{ asset($documents['guarantor_id']) }}" alt="Guarantor ID" style="width: 150px; height: auto;" class="img-thumbnail">
                        </a>
                        <p>Guarantor ID</p>
                    </div>
                    <a href="{{ asset($documents['guarantor_id']) }}" download class="btn btn-primary">Download</a>
                </li>
            @endif

            <!-- Loan Document -->
            @if($documents['document_path'])
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ asset($documents['document_path']) }}" target="_blank">View Loan Document</a>
                    </div>
                    <a href="{{ asset($documents['document_path']) }}" download class="btn btn-primary">Download</a>
                </li>
            @endif

            <!-- Audio Document -->
            @if($documents['audio_path'])
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ asset($documents['audio_path']) }}" target="_blank">Listen to Audio</a>
                    </div>
                    <a href="{{ asset($documents['audio_path']) }}" download class="btn btn-primary">Download</a>
                </li>
            @endif

            <!-- Video Document -->
            @if($documents['video_path'])
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <video width="320" height="240" controls>
                            <source src="{{ asset($documents['video_path']) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <p>Loan Video</p>
                    </div>
                    <div>
                        <a href="{{ asset($documents['video_path']) }}" download class="btn btn-primary">Download</a>

                        <!-- Delete Video Button -->
                        <form action="{{ route('loan.deleteVideo', encrypt($loan->id)) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">Delete Video</button>
                        </form>
                    </div>
                </li>
            @elseif($documents['external_video_link'])
                <!-- Display clickable preview image for external video link -->
                <li class="list-group-item">
                    <a href="{{ $documents['external_video_link'] }}" target="_blank">
                        <img src="{{ asset('assets/images/video-preview-pic.jpg') }}" alt="External Video Preview" style="width: 320px; height: auto;" class="img-thumbnail">
                    </a>
                    <p>External Video</p>
                </li>
            @else
                <!-- Input field for external video link if video is deleted -->
                <li class="list-group-item">
                    <form action="{{ route('loan.updateExternalVideoLink', encrypt($loan->id)) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="external_video_link">External Video Link:</label>
                            <input type="url" name="external_video_link" id="external_video_link" class="form-control" placeholder="Enter external video URL">
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Save Link</button>
                    </form>
                </li>
            @endif
        </ul>
    @endif
</div>
@endsection
