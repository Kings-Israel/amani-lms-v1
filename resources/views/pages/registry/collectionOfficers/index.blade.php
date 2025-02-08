@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-header">
                    <a class="btn btn-primary" href="{{route('collection_officer.create')}}">Add New</a>

                </div>
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Branch</th>
                                <th>RO</th>

                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            {{-- <tbody>
                             <tr>
                                 <td>Tiger Nixon</td>
                                 <td>System Architect</td>
                                 <td>Edinburgh</td>
                                 <td>61</td>
                                 <td>$320,800</td>
                             </tr>
                             <tr>
                                 <td>Garrett Winters</td>
                                 <td>Accountant</td>
                                 <td>Tokyo</td>
                                 <td>63</td>
                                 <td>$170,750</td>
                             </tr>
                             <tr>
                                 <td>Ashton Cox</td>
                                 <td>Junior Technical Author</td>
                                 <td>San Francisco</td>
                                 <td>66</td>
                                 <td>$86,000</td>
                             </tr>
                             <tr>
                                 <td>Cedric Kelly</td>
                                 <td>Senior Javascript Developer</td>
                                 <td>Edinburgh</td>
                                 <td>22</td>
                                 <td>$433,060</td>
                             </tr>
                             <tr>
                                 <td>Airi Satou</td>
                                 <td>Accountant</td>
                                 <td>Tokyo</td>
                                 <td>33</td>
                                 <td>$162,700</td>
                             </tr>
                             <tr>
                                 <td>Brielle Williamson</td>
                                 <td>Integration Specialist</td>
                                 <td>New York</td>
                                 <td>61</td>
                                 <td>$372,000</td>
                             </tr>
                             <tr>
                                 <td>Herrod Chandler</td>
                                 <td>Sales Assistant</td>
                                 <td>San Francisco</td>
                                 <td>59</td>
                                 <td>$137,500</td>
                             </tr>
                             <tr>
                                 <td>Rhona Davidson</td>
                                 <td>Integration Specialist</td>
                                 <td>Tokyo</td>
                                 <td>55</td>
                                 <td>$327,900</td>
                             </tr>
                             <tr>
                                 <td>Colleen Hurst</td>
                                 <td>Javascript Developer</td>
                                 <td>San Francisco</td>
                                 <td>39</td>
                                 <td>$205,500</td>
                             </tr>
                             <tr>
                                 <td>Sonya Frost</td>
                                 <td>Software Engineer</td>
                                 <td>Edinburgh</td>
                                 <td>23</td>
                                 <td>$103,600</td>
                             </tr>
                             <tr>
                                 <td>Jena Gaines</td>
                                 <td>Office Manager</td>
                                 <td>London</td>
                                 <td>30</td>
                                 <td>$90,560</td>
                             </tr>
                             <tr>
                                 <td>Quinn Flynn</td>
                                 <td>Support Lead</td>
                                 <td>Edinburgh</td>
                                 <td>22</td>
                                 <td>$342,000</td>
                             </tr>
                             <tr>
                                 <td>Charde Marshall</td>
                                 <td>Regional Director</td>
                                 <td>San Francisco</td>
                                 <td>36</td>
                                 <td>$470,600</td>
                             </tr>
                             <tr>
                                 <td>Haley Kennedy</td>
                                 <td>Senior Marketing Designer</td>
                                 <td>London</td>
                                 <td>43</td>
                                 <td>$313,500</td>
                             </tr>
                             <tr>
                                 <td>Tatyana Fitzpatrick</td>
                                 <td>Regional Director</td>
                                 <td>London</td>
                                 <td>19</td>
                                 <td>$385,750</td>
                             </tr>
                             <tr>
                                 <td>Michael Silva</td>
                                 <td>Marketing Designer</td>
                                 <td>London</td>
                                 <td>66</td>
                                 <td>$198,500</td>
                             </tr>
                             <tr>
                                 <td>Paul Byrd</td>
                                 <td>Chief Financial Officer (CFO)</td>
                                 <td>New York</td>
                                 <td>64</td>
                                 <td>$725,000</td>
                             </tr>
                             <tr>
                                 <td>Gloria Little</td>
                                 <td>Systems Administrator</td>
                                 <td>New York</td>
                                 <td>59</td>
                                 <td>$237,500</td>
                             </tr>
                             <tr>
                                 <td>Bradley Greer</td>
                                 <td>Software Engineer</td>
                                 <td>London</td>
                                 <td>41</td>
                                 <td>$132,000</td>
                             </tr>
                             <tr>
                                 <td>Dai Rios</td>
                                 <td>Personnel Lead</td>
                                 <td>Edinburgh</td>
                                 <td>35</td>
                                 <td>$217,500</td>
                             </tr>
                             </tbody>--}}
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Branch</th>
                                <th>RO</th>
                                <th>Status</th>
                                <th>Action</th>
                                {{-- <th>Salary</th>--}}
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
        $('#cbtn-selectors1').DataTable({
            "processing": true,
            "serverSide": true,
            dom: 'Bfrtip',
            buttons: [{extend: 'copyHtml5', exportOptions: {columns: ':visible'}}, {
                extend: 'excelHtml5',
                exportOptions: {columns: ':visible'}
            }, {extend: 'pdfHtml5', exportOptions: {columns: ':visible'}}, 'colvis','pageLength'],


            ajax: '{!! route('Collection_Officer.data') !!}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'email', name: 'email'},
                {data: 'branch', name: 'branch'},
                {data: 'ro', name: 'ro'},
                {data: 'status', name: 'status'},
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ],
        });
    </script>


@stop
