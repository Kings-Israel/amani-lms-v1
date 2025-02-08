@extends('layouts.master')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('bower_components/sweetalert/css/sweetalert.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bulma/bulma.css" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card-block">
                <div class="dt-responsive table-responsive">
                    <table id="cbtn-selectors1" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>Owner</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Amount</th>
                                <th>Phone</th>
                                <th>Disbursement Date</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th>Owner</th>
                                <th>Product</th>
                                <th>Installments</th>
                                <th>% Interest</th>
                                <th>Amount</th>
                                <th>Phone</th>
                                <th>Disbursement Date</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#cbtn-selectors1').DataTable({
                dom: 'Bfrtip',
                "processing": true,
                "serverSide": true,
                buttons: [{
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: ':visible'
                    }
                }, {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: ':visible'
                    }
                }, {
                    extend: 'pdfHtml5',
                    exportOptions: {
                        columns: ':visible'
                    }
                }, 'colvis', 'pageLength'],

                ajax: '{!! route('disbursed_loans.data') !!}',
                columns: [
                    {
                        data: 'owner',
                        name: 'owner'
                    },
                    {
                        data: 'product_name',
                        name: 'products.product_name'
                    },
                    {
                        data: 'installments',
                        name: 'products.installments'
                    },
                    {
                        data: 'interest',
                        name: 'products.interest'
                    },
                    {
                        data: 'loan_amount',
                        name: 'loan_amount'
                    },
                    {
                        data: 'phone',
                        name: 'customers.phone'
                    },
                    {
                        data: 'disburseme_date',
                        name: 'date_disbursed'
                    },
                    {
                        data: 'date_created',
                        name: 'date_created'
                    },
                ],
            });
        })
    </script>
@endsection
