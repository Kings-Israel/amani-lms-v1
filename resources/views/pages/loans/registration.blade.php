@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/jquery.steps/css/jquery.steps.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/component.css')}}">
@stop


@section('scripts')
     <script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@latest"></script>
@stop

@section('content')
    @include('layouts.alert')
    <div class="card">
        <div class="card-header">
            <h5>Loan Registration</h5>
            {{--   <span>Add class of <code>.form-control</code> with <code>&lt;input&gt;</code> tag</span>--}}
        </div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    <div id="wizard3">
                        <section>
                            @if($is_edit)
                                <form class="wizard-form" id="design-wizard" action="{{route('loans.update', ['id' => encrypt($loan->id)])}}" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="_method" value="PUT">
                            @else
                                <form class="wizard-form" id="design-wizard" action="{{route('loans.store')}}" method="post" enctype="multipart/form-data">
                            @endif
                                @csrf

                                <input type="hidden" id="customer_id" value="{{ old('customer_id', isset($loan->customer_id) ? $loan->customer_id : '')}}" name="customer_id">
                                <h3></h3>
                                <fieldset>
                                    @if($is_edit)
                                        @else
                                        <div class="">
                                            <button type="button" class="btn-primary btn" data-toggle="modal"
                                                    data-target="#default-Modal1">Search
                                            </button>
                                        </div>
                                        <br>
                                    @endif
                                    <div class="form-group row">

                                        <!-- postal address -->
                                        <div class="col-md-4 my-1">
                                            <label for="fullname" class="block">Customer Name</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-user"></i></span>
                                                <input id="fullname"  readonly type="text" name="fullname" value="{{ old('fullname', isset($customer->fullname) ? $customer->fullname : '')}}" class="form-control{{ $errors->has('fullname') ? ' is-invalid' : '' }}" required
                                                       placeholder="Name">
                                            </div>
                                            @if ($errors->has('fullname'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('fullname') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <!-- ID -->
                                        <div class="col-md-4 my-1">
                                            <label class="block">Customer ID</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i
                                                        class="icofont icofont-envelope-open"></i></span>

                                                <input readonly type="number" id="id_no" name="id_no" value="{{ old('id_no', isset($customer->id_no) ? $customer->id_no : '')}}" class="form-control{{ $errors->has('id_no') ? ' is-invalid' : '' }}" required
                                                       placeholder="ID NO">

                                            </div>
                                            @if ($errors->has('id_no'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('id_no') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="col-md-4 my-1">
                                            <label for="product_id" class="block">Loan Product</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-social-pandora"></i></span>

                                                <select id="product_id" name="product_id" class="js-example-basic-single form-control{{ $errors->has('product_id') ? ' is-invalid' : '' }}" required>

                                                    <option selected readonly>Select Product</option>
                                                    @foreach($products as $product)
                                                        {{--<option--}}
                                                            {{--value="{{$product->id}}" {{ (old("product_id") == $product->id ? "selected":"") }}  data-installments="{{$product->installments}}">{{$product->product_name}}</option>--}}
                                                        <option
                                                            value="{{$product->id}}" {{ isset($loan->product_id) ? (($loan->product_id == $product->id) ? 'selected' : '') : $product->id/*(old("product_id") == $product->id ? "selected":"")*/ }} data-installments="{{$product->installments}}" >
                                                            {{$product->product_name}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($errors->has('product_id'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('product_id') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-4 my-1">
                                            <label for="purpose" class="block">Purpose</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i
                                                    class="icofont icofont-location-pin"></i>
                                                </span>

                                                <select id="purpose" name="purpose" class="js-example-basic-single form-control{{ $errors->has('purpose') ? ' is-invalid' : '' }}" required>
                                                    <option value="Business Expense" {{ (old("purpose") == "Business Expense" ? "selected":"") }}>Business Expense</option>
                                                    <option value="Start Business" {{ (old("purpose") == "Start Business" ? "selected":"") }}>Start Business</option>
                                                </select>
                                            </div>
                                            @if ($errors->has('purpose'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('purpose') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        {{--Amount Applied--}}
                                        <div class="col-md-4 my-1">
                                            <label for="loan_amount" class="block">Applied Loan Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-money"></i></span>
                                                <input  type="number" min="7000" max="81000" id="loan_amount" value="{{ old('loan_amount', isset($loan->total_amount) ? $loan->total_amount : '')}}" name="loan_amount" class="form-control{{ $errors->has('loan_amount') ? ' is-invalid' : '' }}" required
                                                        placeholder="Loan Amount">
                                                {{-- <select id="loan_amount" name="loan_amount" class="js-example-basic-single form-control{{ $errors->has('loan_amount') ? ' is-invalid' : '' }}" required>
                                                    <option value="">Select Loan Amount</option>
                                                    @foreach ($prequalified_amounts as $amount)
                                                        <option value="{{ $amount->amount }}" @if($loan && $loan->total_amount == $amount->amount) selected @endif>{{ number_format($amount->amount) }}</option>
                                                    @endforeach
                                                </select> --}}
                                            </div>
                                            @if ($errors->has('loan_amount'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('loan_amount') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Negotiated Installments -->
                                        <div class="col-md-4 my-1">
                                            <label for="installments" class="block">Negotiated Installments</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-notebook"></i></span>
                                                <input value="{{ old('installments', isset($loan->product) ? $loan->product()->first()->installments : '')}}" readonly  type="number" id="installments" name="installments" class="form-control{{ $errors->has('installments') ? ' is-invalid' : '' }}" required
                                                       placeholder="Installments">

                                            </div>
                                            @if ($errors->has('installments'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('installments') }}</strong>
                                    </span>
                                            @endif
                                        </div>


                                        {{--Phone Number--}}

                                        <div class="col-md-4 my-1">
                                            <label for="phone" class="block">Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-ui-call"></i></span>
                                                <input id="phone" value="{{ old('phone', isset($customer->phone) ? $customer->phone : '')}}" readonly  type="text"  name="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" required placeholder="Phone">

                                            </div>
                                            @if ($errors->has('phone'))
                                                <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                        @if($is_edit)
                                            <div class="col-md-4 my-1">
                                                <label for="loan_form" class="block">Update Loan Documents <small class="badge badge-danger"> .pdf *</small></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="icofont icofont-document-folder"></i></span>
                                                    <input type="file" id="loan_form" name="loan_form" accept=".pdf" class="form-control{{ $errors->has('loan_form') ? ' is-invalid' : '' }}">
                                                </div>
                                                @if ($errors->has('loan_form'))
                                                    <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('loan_form') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                        @endif
                                        @if(!$is_edit)
                                        <div class="col-md-4 my-1">
                                            <label for="loan_type" class="block">Loan Type</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="icofont icofont-abacus-alt"></i></span>
                                                <select id="loan_type" name="loan_type" class="js-example-basic-single form-control{{ $errors->has('loan_type') ? ' is-invalid' : '' }}" required>
                                                    <option value="" selected disabled readonly>Select Loan Repayment Type</option>
                                                    @foreach($loan_types as $type)
                                                        <option value="{{$type->id}}" {{ isset($loan->loan_type_id) ? (($loan->loan_type_id == $type->id) ? 'selected' : '') : $type->id }} >
                                                            {{$type->name}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($errors->has('loan_type'))
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('loan_type') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        {{-- <div class="col-md-4 my-1">
                                            <label for="loan_form" class="block">Loan Documents <small class="badge badge-danger">.pdf*</small></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="icofont icofont-document-folder"></i></span>
                                                    <input type="file" name="loan_form" id="loan_form" class="form-control{{ $errors->has('loan_form') ? ' is-invalid' : '' }}" >
                                                </div>
                                                @if ($errors->has('loan_form'))
                                                    <span class="text-danger" role="alert">
                                                    <strong>{{ $errors->first('loan_form') }}</strong>
                                                </span>
                                                @endif
                                        </div>

                                            <!-- Audio/Video Upload -->
                                            <div class="col-md-4 my-1">
                                                <label for="audio_file" class="block">Upload Audio File <small class="badge badge-danger">.mp3, .wav*</small></label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="icofont icofont-music"></i></span>
                                                    <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav" class="form-control{{ $errors->has('audio_file') ? ' is-invalid' : '' }}">
                                                </div>
                                                @if ($errors->has('audio_file'))
                                                    <span class="text-danger" role="alert">
                                                        <strong>{{ $errors->first('audio_file') }}</strong>
                                                    </span>
                                                @endif
                                            </div> --}}


                                     <!-- Front of Customer ID -->
                                                <div class="col-md-4 my-1">
                                                    <label for="customer_id_front" class="block">Customer ID (Front)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="icofont icofont-document-folder"></i></span>
                                                        <input type="file" name="customer_id_front" id="customer_id_front" accept="image/*" capture="environment" class="form-control{{ $errors->has('customer_id_front') ? ' is-invalid' : '' }}">
                                                    </div>
                                                    @if ($errors->has('customer_id_front'))
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('customer_id_front') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Back of Customer ID -->
                                                <div class="col-md-4 my-1">
                                                    <label for="customer_id_back" class="block">Customer ID (Back)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="icofont icofont-document-folder"></i></span>
                                                        <input type="file" name="customer_id_back" id="customer_id_back" accept="image/*" capture="environment" class="form-control{{ $errors->has('customer_id_back') ? ' is-invalid' : '' }}">
                                                    </div>
                                                    @if ($errors->has('customer_id_back'))
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('customer_id_back') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Guarantor ID -->
                                                <div class="col-md-4 my-1">
                                                    <label for="guarantor_id" class="block">Guarantor ID</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="icofont icofont-document-folder"></i></span>
                                                        <input type="file" name="guarantor_id" id="guarantor_id" accept="image/*" capture="environment" class="form-control{{ $errors->has('guarantor_id') ? ' is-invalid' : '' }}">
                                                    </div>
                                                    @if ($errors->has('guarantor_id'))
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('guarantor_id') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Video File -->
                                                {{-- <div class="col-md-4 my-1">
                                                    <label for="video_file" class="block">Upload Video Recording <small class="badge badge-danger">.mp4, .avi*</small></label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="icofont icofont-video-alt"></i></span>
                                                        <input type="file" name="video_file" id="video_file" accept="video/*" capture="camcorder" class="form-control{{ $errors->has('video_file') ? ' is-invalid' : '' }}">
                                                    </div>
                                                    @if ($errors->has('video_file'))
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('video_file') }}</strong>
                                                        </span>
                                                    @endif
                                                </div> --}}
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
                                                    @if ($errors->has('video_file'))
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('video_file') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>



{{--
                                                <div class="col-md-4 my-1">
                                                    <label for="video_file" class="block">Upload Video Recording <small class="badge badge-danger">.mp4, .avi*</small></label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="icofont icofont-video-alt"></i></span>
                                                        <input type="file" name="video_file" id="video_file" accept="video/*" capture="camcorder" class="form-control{{ $errors->has('video_file') ? ' is-invalid' : '' }}">
                                                    </div>
                                                    @if ($errors->has('video_file'))
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $errors->first('video_file') }}</strong>
                                                        </span>
                                                    @endif
                                                </div> --}}



                                    </div>
                                        @endif
                                </fieldset>
                                <h3></h3>
                               <button type="submit" class="btn btn-info">Submit</button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{--modal--}}
    <div class="modal fade" id="default-Modal1" tabindex="-1"
         role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Customers</h4>
                    <button type="button" class="close"
                            data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="dt-responsive table-responsive">
                        <table id="customers"
                               class="table table-striped table-bordered nowrap" style="width: 100%">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Loan Status</th>
                                <th>ID</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Loan Status</th>
                                <th>ID</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-default waves-effect "
                            data-dismiss="modal">Close
                    </button>
                     <button type="button"
                             class="btn btn-primary waves-effect waves-light ">
                         Save changes
                     </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')


    <script>
        /***************************update installments based on product selected*/
        $(document).ready(function() {
            $('#customers').DataTable({
                ajax: '{!! route('loans.customer_data') !!}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'fullName', name: 'fullName'},
                    {data: 'phone', name: 'phone'},
                    {data: 'loan_status', name: 'loan_status'},
                    {data: 'id_no', name: 'id_no'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
            });

            $('#product_id').change(function(){
                var val = $(this).val(); //get new value
                var extra = $('option:selected').attr('data-installments');
                $("#installments").val(extra).text(extra);

            });
            //$('.sel-btn').on('click', function () {
            $('body').on('click', '.sel-btn', function() {
                var phone =  $(this).data('phone');
                var idno = $(this).data('idno');
                var amount = $(this).data('amount');
                var fullname = $(this).data('fullname');
                var customer_id = $(this).data('id');

                $("#phone").val(phone).text(phone);
                $("#id_no").val(idno).text(idno);
                $("#loan_amount").val(amount).text(amount);
                $("#fullname").val(fullname).text(fullname);
                $("#customer_id").val(customer_id).text(customer_id);
            })
        })
    </script>


<script>
    function compressImage(file, maxWidth, maxHeight, quality, callback) {
        const reader = new FileReader();

        reader.onload = function(event) {
            const img = new Image();
            img.src = event.target.result;

            img.onload = function() {
                let width = img.width;
                let height = img.height;

                // Calculate the new dimensions based on the max size
                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }

                // Create a canvas element to resize the image
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Get the resized image as a Blob and return it
                canvas.toBlob(function(blob) {
                    callback(blob);
                }, 'image/jpeg', quality);
            };
        };

        reader.readAsDataURL(file);
    }

    document.getElementById('customer_id_front').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            compressImage(file, 1024, 1024, 0.7, function(compressedFile) {
                const newFile = new File([compressedFile], file.name, { type: 'image/jpeg' });

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                document.getElementById('customer_id_front').files = dataTransfer.files;
            });
        }
    });

    document.getElementById('customer_id_back').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            compressImage(file, 1024, 1024, 0.7, function(compressedFile) {
                const newFile = new File([compressedFile], file.name, { type: 'image/jpeg' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                document.getElementById('customer_id_back').files = dataTransfer.files;
            });
        }
    });

    document.getElementById('guarantor_id').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            compressImage(file, 1024, 1024, 0.7, function(compressedFile) {
                const newFile = new File([compressedFile], file.name, { type: 'image/jpeg' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                document.getElementById('guarantor_id').files = dataTransfer.files;
            });
        }
    });

</script>





<script>
    const { createFFmpeg, fetchFile } = FFmpeg;
    const ffmpeg = createFFmpeg({ log: true });

    async function compressVideo(inputFile) {
        if (!ffmpeg.isLoaded()) {
            await ffmpeg.load();
        }

        ffmpeg.FS('writeFile', 'input.mp4', await fetchFile(inputFile));
        await ffmpeg.run('-i', 'input.mp4', '-b:v', '1000k', 'output.mp4');

        const data = ffmpeg.FS('readFile', 'output.mp4');

        const videoUrl = URL.createObjectURL(new Blob([data.buffer], { type: 'video/mp4' }));
        console.log(videoUrl); // You can use this URL to play or download the compressed video
    }

    document.getElementById('video_file').addEventListener('change', (event) => {
        const file = event.target.files[0];
        compressVideo(file);
    });
</script>

<script>
    document.getElementById('video_file').addEventListener('change', function(event) {
        const file = event.target.files[0];

        // Check if the browser supports the MediaRecorder API for recording video
        if (file && file.type.startsWith('video/')) {
            if (file.type.includes('audio')) {
                console.log('Video includes audio.');
            } else {
                console.warn('This video may not include audio.');
            }
        } else {
            alert('Please use the camcorder to record a video with audio.');
        }
    });
</script>





@stop

