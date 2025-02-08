<?php

namespace App\Http\Controllers;

use App\models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('role:admin');
        // Alternativly
    }
    public function index()
    {

        $this->data['products'] = Product::all();
        $this->data['title'] = "Loan Products";
        $this->data['sub_title'] = "List of Loan products ";


        return view('pages.accounts.products.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['title'] = "Create New Loan Product";
        $this->data['is_edit'] = false;

        // $this->data['sub_title'] = "List of all loan officers";


        return view('pages.accounts.products.form', $this->data);
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
            'product_name' => 'required|min:3',
            'interest' => 'required|numeric|gt:0',
            'installments' => 'required|numeric|gt:0',
            'duration' => 'required|numeric|gt:0',



        ]);
      //   dd($validator);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();


        }




        $product = Product::create($request->input());

        return back()->with('success', 'Successfully created Product');
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
        $product = Product::find($id);
        $this->data['title'] = "Update ".$product->bname."Loan Product";
        $this->data['is_edit'] = true;
        $this->data['product'] = $product;


        // $this->data['sub_title'] = "List of all loan officers";


        return view('pages.accounts.products.form', $this->data);
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
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|min:3',
            'interest' => 'required|numeric|gt:0',
            'installments' => 'required|numeric|gt:0',
            'duration' => 'required|numeric|gt:0',



        ]);
        //   dd($validator);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();


        }




        $product = Product::where('id', $id)->update($request->except(['_method', '_token']));

        return back()->with('success', 'Successfully created Product');
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
        $lo = Product::all();
        // $lo = User::role([/*'admin',*/'manager', 'field_agent'])->get();


        return Datatables::of($lo)
            ->editColumn('status', function ($lo) {
                if($lo->status){
                    return 'Active';
                }
                else{
                    return 'Inactive';
                }


            })



            ->addColumn('action', function ($lo) {
                $data = $lo->id;


                //return '<a href="'.route('events.edit',['id' => $events->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';<i class="icon-settings"></i>


                return '<div class="btn-group text-center">
                                                <a type="button" class="btn" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">

                                                             <li><a href="' . route('products.edit', ['id' => $data]) . '"><i class="feather icon-edit text-warning"></i> Edit</a></li>

                                                        </ul>
                                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);

    }
}
