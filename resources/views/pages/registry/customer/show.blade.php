@extends("layouts.master")
@section("css")
    <link rel="stylesheet" type="text/css" href="{{ asset('css/themify-icons.css') }}">
@stop
@section("content")
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col">
                <a href="{{ route("registry.edit", encrypt($customer->id)) }}" class="btn btn-primary btn-sm float-right">
                    <i class="ti ti-pencil-alt h5"></i>
                    <span>Edit</span>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="list-group" id="list-tab" role="tablist">
                    <a class="list-group-item list-group-item-action active" id="list-home-list" data-toggle="list" href="#list-home" role="tab" aria-controls="home">Personal Information <i class="feather icon-chevron-right"></i></a>
                    <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-customer_photos" role="tab" aria-controls="settings">Customer Documents <small>(Photos)</small> <i class="feather icon-chevron-right"></i></a>
                    <a class="list-group-item list-group-item-action" id="list-profile-list" data-toggle="list" href="#list-profile" role="tab" aria-controls="profile">Location <i class="feather icon-chevron-right"></i></a>
                    <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-loans" role="tab" aria-controls="settings">Loans <i class="feather icon-chevron-right"></i></a>
                </div>
            </div>
            <div class="col-md-9 card card-body">
                <div class="tab-content" id="nav-tabContent">
                    {{-- begin personal information --}}
                    <div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
                        <div class="row">
                            <div class="col-md-6">
                                {{-- begin table responsive --}}
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                            <th scope="row">Type</th>
                                            <td>{{ uppercase($customer->type) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Title</th>
                                                <td>{{ uppercase($customer->title) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">First Name</th>
                                                <td>{{ uppercase($customer->fname) }}</td>
                                            </tr>

                                            <tr>
                                                <th scope="row">Middle Name</th>
                                                <td>{{ uppercase($customer->mname) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Last Name</th>
                                                <td>{{ uppercase($customer->lname) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Relationship Officer</th>
                                            <td>{{ uppercase($customer->Officer->name) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Mobile Line</th>
                                                <td>{{ uppercase($customer->phone) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                {{-- end table responsive --}}
                            </div>
                            <div class="col-md-6">
                                {{-- begin table responsive --}}
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Email</th>
                                                <td>{{ uppercase($customer->email) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Identity Type</th>
                                                <td>{{ uppercase($customer->idDocument->dname) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Identity Number</th>
                                                <td>{{ uppercase( $customer->id_no ) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Guarantor</th>
                                                <td>{{ ($customer->guarantor) ? uppercase( $customer->guarantor->gname ) : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Prequalified Amount</th>
                                            <td>KSh {{ number_format( $customer->prequalified_amount ) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Alternate Mobile Line</th>
                                                <td>{{ $customer->alternate_phone }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                {{-- end table responsive --}}
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            @forelse($customer->referees as $key => $referee)
                                                <tr>
                                                    <th scope="row">{{ $key + 1 }}. Referee Full Name</th>
                                                    <td>{{ uppercase($referee->full_name) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">{{ $key + 1 }}. Referee National ID Number</th>
                                                    <td>{{ uppercase($referee->id_number) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">{{ $key + 1 }}. Referee Phone Number</th>
                                                    <td>{{ uppercase($referee->phone_number) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <th scope="row">There are no registered referees</th>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </div>
                        </div>
                        {{-- end personal information --}}
                        <div class="tab-pane fade" id="list-customer_photos" role="tabpanel" aria-labelledby="list-settings-list">
                            <div class="row">
                                @if($customer->customer_document)
                                    <a href="{{ route('customer-documents.edit', encrypt($customer->customer_document->id)) }}" class="btn btn-primary btn-sm float-right mb-3">
                                        <span>Edit Customer Images</span>
                                    </a>
                                @else
                                    <a href="{{ route('customer-documents.create', encrypt($customer->id)) }}" class="btn btn-primary btn-sm float-right mb-3">
                                        <span>Upload Customer Images</span>
                                    </a>
                                @endif
                            </div>
                            <div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">Image/Video</th>
                <th scope="col">Type</th>
                <th scope="col">View Full</th>
            </tr>
        </thead>
        <tbody>
            <!-- Profile Photo Row -->
            @if($customer->customer_document && $customer->customer_document->profile_photo_path)
                <tr>
                    <td>
                        <a href="{{ Storage::url($customer->customer_document->profile_photo_path) }}" target="_blank">
                            <img src="{{ Storage::url($customer->customer_document->profile_photo_path) }}" style="width: 100px; height: auto;" alt="Profile Photo">
                        </a>
                    </td>
                    <td>Profile Photo</td>
                    <td>
                        <a href="{{ Storage::url($customer->customer_document->profile_photo_path) }}" target="_blank">View</a>
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="3" class="text-center">No Profile Photo has been set</td>
                </tr>
            @endif

            <!-- Front ID Photo Row -->
            @if($customer->customer_document && $customer->customer_document->id_front_path)
                <tr>
                    <td>
                        <a href="{{ Storage::url($customer->customer_document->id_front_path) }}" target="_blank">
                            <img src="{{ Storage::url($customer->customer_document->id_front_path) }}" style="width: 100px; height: auto;" alt="Front ID Photo">
                        </a>
                    </td>
                    <td>Front Facing ID Photo</td>
                    <td>
                        <a href="{{ Storage::url($customer->customer_document->id_front_path) }}" target="_blank">View</a>
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="3" class="text-center">No Front Facing ID Photo has been set</td>
                </tr>
            @endif

            <!-- Back ID Photo Row -->
            @if($customer->customer_document && $customer->customer_document->id_back_path)
                <tr>
                    <td>
                        <a href="{{ Storage::url($customer->customer_document->id_back_path) }}" target="_blank">
                            <img src="{{ Storage::url($customer->customer_document->id_back_path) }}" style="width: 100px; height: auto;" alt="Back ID Photo">
                        </a>
                    </td>
                    <td>Back Facing ID Photo</td>
                    <td>
                        <a href="{{ Storage::url($customer->customer_document->id_back_path) }}" target="_blank">View</a>
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="3" class="text-center">No Back Facing ID Photo has been set</td>
                </tr>
            @endif

            <!-- Video Row -->
            @if($customer->customer_document && $customer->customer_document->video_path)
                <tr>
                    <td>
                        <video width="200" height="140" controls>
                            <source src="{{ Storage::url($customer->customer_document->video_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </td>
                    <td>Video</td>
                    <td>
                        <a href="{{ Storage::url($customer->customer_document->video_path) }}" target="_blank">View</a>
                    </td>
                </tr>
            @elseif($customer->customer_document && $customer->customer_document->external_video_link)
                <tr>
                    <td>
                        <a href="{{ $customer->customer_document->external_video_link }}" target="_blank">
                            <img src="{{ asset('images/preview-image.jpg') }}" alt="External Video Preview" style="width: 320px; height: auto;" class="img-thumbnail">
                        </a>
                    </td>
                    <td>External Video</td>
                    <td>
                        <a href="{{ $customer->customer_document->external_video_link }}" target="_blank">View</a>
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="3" class="text-center">No Video has been uploaded</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>


                        </div>

                        <div class="tab-pane fade" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">
                            {{-- begin location --}}
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- begin table responsive --}}
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                <tr>
                                                    <th scope="row">Country</th>
                                                    <td>{{ uppercase( $customer->location->country ) }}</td>
                                                </tr>

                                                <tr>
                                                    <th scope="row">County</th>
                                                    <td>{{ uppercase( $customer->location->county->cname ) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Constituency</th>
                                                    <td>{{ uppercase( $customer->location->constituency ) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Ward</th>
                                                    <td>{{ uppercase( $customer->location->ward ) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    {{-- end table responsive --}}
                                </div>
                            </div>
                            {{-- end location --}}
                        </div>

                        <div class="tab-pane fade" id="list-loans" role="tabpanel" aria-labelledby="list-settings-list">

                        <div class="row justify-content-center">
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Total Amount</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($totalAmount) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-bar-chart f-30 text-c-pink"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Total Paid</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($paidAmount) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-credit-card f-30 text-secondary"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">O.L.B.</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($balance) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-wind f-30 text-danger"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Total Principle</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($principalAmount) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-bookmark f-30 text-c-yellow"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Total Interest</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($interestAmount) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-sliders f-30 text-info"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-primary text-white">
                                    <div class="card-block">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <p class="m-b-5">Registration Fees</p>
                                                <b style="font-size: medium" class="m-b-0">Ksh. {{ number_format($registrationFees) }}</b>
                                            </div>
                                            {{-- <div class="col col-auto text-right">
                                                <i class="feather icon-sliders f-30 text-danger"></i>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            {{-- begin loans --}}
                            <button class="btn btn-sm btn-primary mb-3 mt-1" id="btn-show-all-children" type="button">Expand All</button>
                            <button class="btn btn-sm btn-primary mb-3 mt-1" id="btn-hide-all-children" type="button">Collapse All</button>
                            <div class="dt-responsive table-responsive">
                                <table id="loan-statements" class="table table-striped table-bordered nowrap">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>Product</th>
                                        <th>Installments</th>
                                        <th>% Interest</th>
                                        <th>Amount</th>
                                        <th>Total</th>
                                        <th>Amount Paid</th>
                                        <th>Balance</th>
                                        <th>End Date</th>
                                        <th>Settled</th>
                                    </tr>
                                    </thead>

                                    <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>Product</th>
                                        <th>Installments</th>
                                        <th>% Interest</th>
                                        <th>Amount</th>
                                        <th>Total</th>
                                        <th>Amount Paid</th>
                                        <th>Balance</th>
                                        <th>End Date</th>
                                        <th>Settled</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            {{-- end loans --}}
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        function format (d) {
            function getLoanPayments()
            {
                //loop through predictions array
                var payments =  d.payments;
                var dataArray = [];
                for (var index = 0; index < payments.length; ++index) {
                    var transaction_id = payments[index].transaction_id;
                    var amount = payments[index].amount;
                    var type = payments[index].type == 'Processing Fee' ? 'Application Fee' : payments[index].type;
                    var date_payed = payments[index].date_payed;
                    var value = '<tr><td>'+type+ '</td><td>'+transaction_id+'</td><td>'+amount+'</td><td>'+ date_payed+'<td></tr>';
                    dataArray.push(value);
                }
                return dataArray;
            }
            return `<table style="margin-bottom: 1px; margin-top: 1px">
                            <thead>
                                <tr>
                                    <th scope="col">Transaction Type</th>
                                    <th scope="col">Mpesa Confirmation Code</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                ${getLoanPayments()}
                            </tbody>
                        </table>`;
        }
        var  oTable =
            $('#loan-statements').DataTable({
                "processing": true,
                "serverSide": true,
                dom: 'Bfrtip',
                buttons: [{extend: 'copyHtml5'}, {
                    extend: 'excelHtml5',
                    exportOptions: {columns: ':visible'},
                },
                    {
                        extend: 'pdfHtml5', /*exportOptions: {columns: ':visible'}*/
                        orientation: 'landscape',
                        pageSize: 'TABLOID'
                    },
                    'colvis','pageLength'],
                "lengthMenu": [[25, 50, -1], [25, 50, "All"]],
                "order": [1, 'DESC'],
                ajax:{
                    url: '{!! route('customer_account_statement_loans_data', ['customer'=> $customer_id]) !!}',
                },
                columns: [
                    {
                        className : 'details-control',
                        orderable : false,
                        data : null,
                        defaultContent : ''
                    },
                    {data: 'product_name', name: 'products.product_name'},
                    {data: 'installments', name: 'products.installments'},
                    {data: 'interest', name: 'products.interest'},
                    {data: 'loan_amount', name: 'loan_amount'},
                    {data: 'total', name: 'total'},
                    {data: 'amount_paid', name: 'amount_paid'},
                    {data: 'balance', name: 'balance'},
                    {data: 'end_date', name: 'end_date'},
                    {data: 'settled', name: 'settled'},
                ],
            });
        // Add event listener for opening and closing details
        $('#loan-statements tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = oTable.row( tr );
            if ( row.child.isShown() ) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(row.data()) ).show();
                tr.addClass('shown');
            }
        } );

        // Handle click on "Expand All" button
        $('#btn-show-all-children').on('click', function(){
            // Enumerate all rows
            oTable.rows().every(function(){
                // If row has details collapsed
                if(!this.child.isShown()){
                    // Open this row
                    this.child(format(this.data())).show();
                    $(this.node()).addClass('shown');
                }
            });
        });

        // Handle click on "Collapse All" button
        $('#btn-hide-all-children').on('click', function(){
            // Enumerate all rows
            oTable.rows().every(function(){
                // If row has details expanded
                if(this.child.isShown()){
                    // Collapse row details
                    this.child.hide();
                    $(this.node()).removeClass('shown');
                }
            });
        });

    });
</script>
@stop
