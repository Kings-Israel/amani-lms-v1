@extends("layouts.master")
@section("css")

@stop

@section("content")

    <div class="row">
        <div class="col-sm-12">




            <div class="card">
                <div class="card-block">
                    <div class="dt-responsive table-responsive">
                        <table id="cbtn-selectors1"
                               class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Branch</th>
                                <th>Action</th>

                            </tr>
                            </thead>

                            <tbody>
                            @foreach($managers as $manager)
                                <tr>
                                    <td>{{$manager->id}}</td>
                                    <td>{{$manager->name}}</td>
                                    <td>{{$manager->phone}}</td>
                                    <td>{{$manager->branch}}</td>
                                    <td><a href="{{route('manager_performance', ['id' => encrypt($manager->id)])}}" class="sel-btn btn btn-xs btn-primary" >View</a></td>

                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Branch</th>
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



