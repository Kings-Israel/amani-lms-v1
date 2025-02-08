<?php

namespace App\Http\Controllers;

use App\models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class BranchesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('role:admin|sector_manager');
        // Alternativly
    }
    public function index()
    {
        $this->data['branches'] = Branch::all();
        $this->data['title'] = "System Branches";
        $this->data['sub_title'] = "List of all branches";


        return view('pages.registry.branches.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $this->data['branches'] = Branch::all();
        $this->data['title'] = "Create New Branch";
        $this->data['is_edit'] = false;

        // $this->data['sub_title'] = "List of all loan officers";


        return view('pages.registry.branches.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'bname' => 'required|min:3|unique:branches',
            'bemail' => 'required|email',
            'bphone' => 'required|digits:10',
            'paybill' => 'required',


        ]);
        // dd($request->all());
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();


        }


        $branch = Branch::create([
            'bname' => $request->bname,
            'bphone' => '254' . substr($request->bphone, -9),
            'bemail' => $request->bemail,
            'paybill' => $request->paybill

        ]);

        return back()->with('success', 'Successfully created Branch');


    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $branch = Branch::find($id);
        $this->data['brach'] = $branch;
        $this->data['title'] = "Edit Branch " . $branch->bname;
        $this->data['is_edit'] = true;

        // $this->data['sub_title'] = "List of all loan officers";


        return view('pages.registry.branches.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bname' => 'required|min:3',
            'bemail' => 'required|email',
            'bphone' => 'required',
            'paybill' => 'required'

        ]);
        // dd($request->all());
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();


        }

        $branch = Branch::where('id', $id)->update([
            'bname' => $request->bname,
            'bphone' => '254' . substr($request->bphone, -9),
            'bemail' => $request->bemail,
            'paybill' => $request->paybill

        ]);

        return back()->with('success', 'Successfully updated Branch');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function data()
    {
        $lo = Branch::all()->each->setAppends([]);
        return Datatables::of($lo)
            ->editColumn('status', function ($lo) {
                if ($lo->status) {
                    return 'Active';
                } else {
                    return 'Inactive';
                }
            })
            ->addColumn('action', function ($lo) {
                $data = $lo->id;
                //return '<a href="'.route('events.edit',['id' => $events->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                return '<div class="btn-group text-center">
                                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                            <li><a href="' . route('branches.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning" ></i> Edit</a></li>

                                                        </ul>
                                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
