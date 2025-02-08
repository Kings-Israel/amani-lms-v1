@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">
            @include('layouts.alert')

            <div class="card">
                <div class="card-header">

                    @role(['admin|manager|customer_informant|field_agent'])
                    <div class="row">
                        <div class="col-md-3">
                            <a class="btn btn-primary" href="{{route('groups.create')}}">Add New Group</a>
                        </div>
                    </div>
                    @endrole

                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <button class="btn btn-sm btn-primary mb-3 mt-1" id="btn-show-all-children" type="button">Expand All</button>
                        <button class="btn btn-sm btn-primary mb-3 mt-1" id="btn-hide-all-children" type="button">Collapse All</button>
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Group Name</th>
                                <th>GroupID</th>
                                <th>Leader</th>
                                <th>Members Count</th>
                                <th>Approval</th>
                                <th>Group Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Group Name</th>
                                <th>GroupID</th>
                                <th>Leader</th>
                                <th>Members Count</th>
                                <th>Approval</th>
                                <th>Group Status</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        $(document).ready(function() {
            function format ( d ) {
                console.log(d);
                function getMembers(){
                    //loop through predictions array
                    var members =  d.members;
                    var dataArray = [];
                    for (var index = 0; index < members.length; ++index) {
                        var name = members[index].name;
                        var contact = members[index].contact;
                        var role = members[index].role;
                        var badge;
                        if (role === 'leader'){
                           badge = '<span class="badge badge-success"><b>Leader</b></span>'
                        } else{
                           badge = '';
                        }
                        var str = '';
                        var value = str.concat( name, ' - ', contact, ' ',badge,'<br>');
                        dataArray.push(value);
                    }
                    return dataArray.join('<br/>');
                }
                let output = `
                        <table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">
                            <tr>
                                <td><b>Registered Branch:</b></td>
                                <td>${d.branch}</td>
                                <td><b>Credit Officer:</b></td>
                                <td>${d.field_agent}</td>
                            </tr>
                            <tr>
                                <td><b>Group Members:</b></td>
                                <td colspan="4"> ${getMembers()}</td>
                            </tr>
                            <tr>
                                <td><b>Group Status:</b></td>
                                <td colspan="4">${d.status}</td>

                            </tr>
                            <tr>
                                <td><b>Registered By:</b></td>
                                <td>${d.created_by}</td>
                                <td><b>Date Registered:</b></td>
                                <td>${d.created_at}</td>
                            </tr>
                            <tr>
                                <td><b>Approved By:</b></td>
                                <td>${d.approved_by}</td>
                                <td><b>Date Approved:</b></td>
                                <td>${d.approval_date}</td>
                            </tr>
                        </table>
    `;

                return output;
            }
            var table =  $('#cbtn-selectors1').DataTable({
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
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                ajax: '{!! route('groups.data') !!}',
                columns: [
                    {
                        className : 'details-control',
                        orderable : false,
                        data : null,
                        defaultContent : ''
                    },
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'unique_id', name: 'unique_id'},
                    {data: 'leader', name: 'leader', },
                    {data: 'members_count', name: 'members_count'},
                    {data: 'approved', name: 'approved'},
                    {data: 'status', name: 'status'},
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],

            });
            // Add event listener for opening and closing details
            $('#cbtn-selectors1 tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row( tr );


                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );

            // Handle click on "Expand All" button
            $('#btn-show-all-children').on('click', function(){
                // Enumerate all rows
                table.rows().every(function(){
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
                table.rows().every(function(){
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
