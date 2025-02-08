@extends("layouts.master")

@section("css")
    <style>
        .video-preview, .video-review {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
            margin-bottom: 15px;
            width: 100%;
            max-width: 500px;
        }

        .record-btn, .stop-btn, .upload-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-right: 10px;
        }

        .stop-btn {
            background-color: #dc3545;
        }

        .upload-btn {
            background-color: #28a745;
        }

        .record-btn:disabled, .stop-btn:disabled, .upload-btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
@stop

@section("content")
    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="card">
                <div class="card-block">
                    <form action="{{ route('customer-documents.update', $customer_document->id) }}" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- Customer Details -->
                            <div class="col-md-12 mb-3">
                                <label for="customer_details">Customer Details <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-briefcase-alt-2"></i></span>
                                    <input type="text" readonly id="customer_details" name="customer_details"
                                        value="{{ old('customer_details', $customer->fullNameUpper . ' ' . $customer->phone) }}"
                                        class="form-control{{ $errors->has('customer_details') ? ' is-invalid' : '' }}" required>
                                </div>
                                @error('customer_details')
                                    <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <!-- Profile Photo Section -->
                            <div class="col-md-6 form-group text-center">
                                @if($customer_document->profile_photo_path)
                                    <img src="{{ Storage::url($customer_document->profile_photo_path) }}"
                                        style="width: 50%; height: auto;" alt="{{ $customer->fullNameUpper }}">
                                @else
                                    <p>No Profile Photo Uploaded</p>
                                @endif
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="profile_photo">Update Profile Photo <span class="text-danger">*</span></label>
                                <input type="file" id="profile_photo" name="profile_photo"
                                    class="form-control{{ $errors->has('profile_photo') ? ' is-invalid' : '' }}">
                                @error('profile_photo')
                                    <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <!-- ID Front Photo Section -->
                            <div class="col-md-6 form-group text-center">
                                @if($customer_document->id_front_path)
                                    <img src="{{ Storage::url($customer_document->id_front_path) }}"
                                        style="width: 50%; height: auto;" alt="{{ $customer->fullNameUpper }}">
                                @else
                                    <p>No ID Front Photo Uploaded</p>
                                @endif
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="id_front">Update ID Front <span class="text-danger">*</span></label>
                                <input type="file" id="id_front" name="id_front"
                                    class="form-control{{ $errors->has('id_front') ? ' is-invalid' : '' }}">
                                @error('id_front')
                                    <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <!-- ID Back Photo Section -->
                            <div class="col-md-6 form-group text-center">
                                @if($customer_document->id_back_path)
                                    <img src="{{ Storage::url($customer_document->id_back_path) }}"
                                        style="width: 50%; height: auto;" alt="{{ $customer->fullNameUpper }}">
                                @else
                                    <p>No ID Back Photo Uploaded</p>
                                @endif
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="id_back">Update ID Back <span class="text-danger">*</span></label>
                                <input type="file" id="id_back" name="id_back"
                                    class="form-control{{ $errors->has('id_back') ? ' is-invalid' : '' }}">
                                @error('id_back')
                                    <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary float-left">Update</button>
                        </div>
                    </form>


                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    @include('layouts.alert')
                    <div class="card">
                        <div class="card-block">
                            <h5>Record and Upload Video</h5>
                            <form action="{{ route('customer-documents.upload-video', $customer->id) }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="col-md-4 my-1">
                                    <label for="video_file" class="block">Upload Video Recording
                                        <small class="badge badge-danger">.mp4, .avi*</small>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="icofont icofont-video-alt"></i>
                                        </span>
                                        <input type="file" name="video_file" id="video_file" accept="video/*" capture="camcorder"
                                            class="form-control{{ $errors->has('video_file') ? ' is-invalid' : '' }}">
                                    </div>
                                    @error('video_file')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror

                                    <!-- Display the uploaded video if available -->
                                    @if ($customer_document->video_path)
                                        <div class="mt-3">
                                            <label>Uploaded Video:</label>
                                            <video width="320" height="240" controls>
                                                <source src="{{ Storage::url($customer_document->video_path) }}" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    @else
                                        <p>No video uploaded.</p>
                                    @endif
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary float-left">Upload Video</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <script>
        const livePreview = document.getElementById('livePreview');
        const videoReview = document.getElementById('videoReview');
        const startPreviewBtn = document.getElementById('startPreviewBtn');
        const recordBtn = document.getElementById('recordBtn');
        const stopBtn = document.getElementById('stopBtn');
        const uploadBtn = document.getElementById('uploadBtn');
        const recordedVideoInput = document.getElementById('recordedVideo');
        const videoDataInput = document.getElementById('videoData');

        let mediaRecorder;
        let chunks = [];
        let stream;

        // Start the camera preview when user clicks "Start Camera Preview"
        startPreviewBtn.addEventListener('click', function() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                    .then(function(localStream) {
                        stream = localStream;
                        livePreview.srcObject = stream;
                        livePreview.style.display = 'block'; // Show the preview
                        livePreview.play();

                        mediaRecorder = new MediaRecorder(stream);

                        mediaRecorder.ondataavailable = function(event) {
                            if (event.data.size > 0) {
                                chunks.push(event.data);
                            }
                        };

                        mediaRecorder.onstop = function() {
                            const blob = new Blob(chunks, { type: 'video/mp4' });
                            chunks = [];

                            // Create a URL for the video and set it to the video review
                            const videoURL = URL.createObjectURL(blob);
                            videoReview.src = videoURL;
                            videoReview.style.display = 'block';
                            livePreview.style.display = 'none'; // Hide live preview once recording is done

                            const file = new File([blob], 'recorded-video.mp4', { type: 'video/mp4' });

                            // Set the video file to the hidden input
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            recordedVideoInput.files = dataTransfer.files;

                            // Enable the upload button
                            uploadBtn.disabled = false;
                        };

                        // Enable the record button
                        recordBtn.disabled = false;
                        startPreviewBtn.disabled = true; // Disable start preview button after it's clicked
                    })
                    .catch(function(error) {
                        console.error("Error accessing media devices.", error);
                    });
            }
        });

        // Start recording the video when the record button is clicked
        recordBtn.addEventListener('click', function() {
            mediaRecorder.start();
            recordBtn.disabled = true;
            stopBtn.disabled = false;
            uploadBtn.disabled = true;
        });

        // Stop recording the video when the stop button is clicked
        stopBtn.addEventListener('click', function() {
            mediaRecorder.stop();
            stream.getTracks().forEach(track => track.stop()); // Stop the camera stream
            recordBtn.disabled = false;
            stopBtn.disabled = true;
        });
    </script>

@stop

