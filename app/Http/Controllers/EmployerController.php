<?php

namespace App\Http\Controllers;

use App\models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EmployerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['title'] = "Employers";
        $this->data['sub_title'] = "List of all employers";


        return view('pages.registry.employers.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['title'] = "Create New Employer";
        $this->data['is_edit'] = false;

        // $this->data['sub_title'] = "List of all loan officers";


        return view('pages.registry.employers.form', $this->data);
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
            'ename' => 'required|min:3',
            'eemail' => 'required|email|unique:employers',
            'ephone' => 'required|digits:10|unique:employers',
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'


        ]);
        // dd($request->all());
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();


        }


        $employer = Employer::create([
            'ename' => $request->ename,
            'ephone' => '254' . substr($request->ephone, -9),
            'eemail' => $request->eemail,
            'location' => $request->location,
            'latitude' => $request->location,
            'longitude' => $request->longitude


        ]);

        return back()->with('success', 'Successfully created employer');
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


    /**

     *Get loan officer data

     */

    public  function data(){
        $lo = Employer::all();

        return Datatables::of($lo)
           /* ->editColumn('status', function ($lo) {
                if($lo->status){
                    return 'Active';
                }
                else{
                    return 'Inactive';
                }


            })*/
            ->addColumn('action', function ($lo) {
                $data = $lo->id;


                //return '<a href="'.route('events.edit',['id' => $events->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';<i class="icon-settings"></i>


                return '<div class="btn-group text-center">
                                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                            <li><a href="' . route('employers.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning"></i> Edit</a></li>
                                                            <li><a href="' . route('employers.view', ['id' => $data]) . '"><i class="feather icon-eye text-info"></i> View</a></li>


                                                        </ul>
                                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);

    }
}
