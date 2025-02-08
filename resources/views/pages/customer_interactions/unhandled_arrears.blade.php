@extends("layouts.master")
@section("css")
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')
            <div class="row">
                <div class="col-md-12 mb-3">
                </div>
            </div>
            <div class="card">
{{--                <div class="card-header">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-md-3">--}}
{{--                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">--}}
{{--                                Add New Interaction--}}
{{--                            </button>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                </div>--}}
                <div class="card-block">

                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Loan Account</th>
                                <th>Amount</th>
                                <th>Last Paid</th>
                                <th>Was Due</th>
                                <th>Date Created</th>

                                <th>Action</th>

                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Loan Account</th>
                                <th>Amount</th>
                                <th>Last Paid</th>
                                <th>Was Due</th>
                                <th>Date Created</th>

                                <th>Action</th>

                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>





        </div>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create a New Arrear Interaction Record Under {{$customer->full_name}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('customer-interactions.store')}}" method="post">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{$customer->id}}">
                        <input type="hidden" id="model_id" name="model_id">

                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="interaction_category_id">Interaction Category</label>

                                <input id="interaction_category_id" readonly type="text" value="{{ $category->name }}"  autocomplete="off" name="interaction_category_id" class="form-control {{ $errors->has('interaction_category_id') ? ' is-invalid' : '' }}" >

                            @if ($errors->has('interaction_category_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interaction_category_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="interaction_type_id">Interaction Type</label>
                                <select name='interaction_type_id' id='interaction_type_id' class="form-control">
                                    <option value="" disabled>Kindly specify the type of interaction</option>
                                    @foreach($interaction_types as $interaction_type)
                                        <option value="{{$interaction_type->id}}" {{(old('interaction_type_id') == $interaction_type->id ) ? 'selected' : ''}}>{{$interaction_type->name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('interaction_type_id'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('interaction_type_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="datetimepicker2">Next Scheduled Interaction</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">
                                        <i class="icofont icofont-ui-calendar"></i>
                                    </span>
                                    <input id="datetimepicker2" type="date" value="{{ old('next_scheduled_interaction') }}" min="{{now()->format('Y-m-d')}}" autocomplete="off" name="next_scheduled_interaction" class="form-control {{ $errors->has('next_scheduled_interaction') ? ' is-invalid' : '' }}"  required>
                                </div>
                                @if ($errors->has('next_scheduled_visit'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('next_scheduled_visit') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <label for="remark">Remark</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="icofont icofont-ui-message"></i></span>
                                    <textarea type="text" cols="10" rows="10" id="remark" name="remark"  class="form-control {{ $errors->has('remark') ? ' is-invalid' : '' }}" required>{{ old('remark') }}</textarea>
                                </div>
                                @if ($errors->has('remark'))
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('remark') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-primary float-left">Save </button>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



@stop


@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $(document).on('click', '.interact', function () {
           // $("#customer_id").val($(this).attr("data-customer_id"));
           // $("#datetimepicker2").val($(this).attr("data-date"));
            //$("#interaction_category_id").val($(this).attr("data-category"));
           // $("#remark").text($(this).attr("data-remark"));
            $("#model_id").val($(this).attr("data-model"));
         //   $("#preinteraction_id").val($(this).attr("data-pre_id"));



        })
        $(document).ready(function () {
            var oTable = $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                "processing": true,
                "serverSide": true,
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
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],





                ajax: {
                    url: '{!! route('unhandled_arrears_data', encrypt($customer->id)) !!}',
                    data: function (d) {

                        d.status = 'all';
                        d.category ='all';


                    }
                },





                order: [0, 'desc'],



                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'loan_account', name: 'loan_account'},
                    {data: 'amount', name: 'amount'},
                    {data: 'last_payment_date', name: 'last_payment_date'},
                    {data: 'was_due', name: 'was_due'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', orderable: false, searchable: false},
                ],
            });

            $('#search').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });




        });

    </script>


@stop
