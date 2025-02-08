<?php

namespace App\Http\Controllers;

use App\models\Relationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class KinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['title'] = "Kin Relationship";
        $this->data['sub_title'] = "Relationshi of customer and their next of kin";


        return view('pages.registry.kin_relationship.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       // $this->data['branches'] = Branch::all();
        $this->data['title'] = "Add a New customer Kin Relationship";
        $this->data['is_edit'] = false;

        // $this->data['sub_title'] = "List of all loan officers";


        return view('pages.registry.kin_relationship.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'rname' => 'required|min:3',

        ]);
        // dd($request->all());
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();


        }


        $branch = Relationship::create([
            'rname' => $request->rname,
        ]);

        return back()->with('success', 'Successfully created Relationship');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



    public  function data(){
        $lo = Relationship::all();
       // dd($lo);

        return Datatables::of($lo)
            /*  ->editColumn('status', function ($lo) {
                  if($lo->status){
                      return 'Active';
                  }
                  else{
                      return 'Inactive';
                  }


              })*/
            ->addColumn('action', function ($lo) {
                $data = $lo->id;


                //return '<a href="'.route('events.edit',['id' => $events->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';



                return '<div class="btn-group text-center">
                                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                            <li><a href="' . route('kin.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>

                                                        </ul>
                                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);

    }
}
