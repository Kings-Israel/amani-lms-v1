<?php

namespace App\Http\Controllers;

use App\models\CheckOffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CheckOffProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['title'] = "Check Off Loan Products";
        $this->data['sub_title'] = "List of all loan products employees can apply for Advance Loans";
        return view('pages.check-off.products.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['title'] = "Create New Check Off Product";
        $this->data['is_edit'] = false;
        return view('pages.check-off.products.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|min:2|max:100",
            "interest" => "required|integer|min:1|max:99",
            "period" => "required|integer|min:1|max:1000",
        ]);
        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }
        $product = CheckOffProduct::query()->create([
            'name' => $request->input('name'),
            'interest' => $request->input('interest'),
            'period' => $request->input('period'),
            'status' => true
        ]);
        return Redirect::route('check-off-products.index')->with('success', "$product->name has been registered successfully.");
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
        $product = CheckOffProduct::query()->find($id);
        $this->data['product'] = $product;
        $this->data['title'] = "Edit Product Details:  $product->name";
        $this->data['is_edit'] = true;
        return view('pages.check-off.products.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|min:2|max:100",
            "interest" => "required|integer|min:1|max:99",
            "period" => "required|integer|min:1|max:1000",
        ]);
        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }
        $product = CheckOffProduct::query()->find($id);
        if ($product){
            $product->update([
                'name' => $request->input('name'),
                'interest' => $request->input('interest'),
                'period' => $request->input('period')
            ]);
            return Redirect::route('check-off-products.index')->with('success', "$product->name has been updated successfully.");
        }
        return Redirect::route('check-off-products.index')->with('error', "Product Details could not be retrieved.");

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $product = CheckOffProduct::query()->withCount('loans')->find($id);
        $loansCount = $product->loans_count;
        if ($loansCount == 0){
            $product->delete();
            return Redirect::route('check-off-products.index')->with('success', "$product->name has been deleted successfully.");
        }
        return Redirect::route('check-off-products.index')->with('error', "$product->name has registered employees and can therefore not be deleted.");
    }

    /**
     * Data Table
     * @return mixed
     * @throws \Exception
     */
    public function data(){
        $lo = CheckOffProduct::query()->select('check_off_products.*');
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
                return '<div class="btn-group text-center">
                                                <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                             <li><a href="'.route('check-off-products.edit', $lo->id).'"><i class="feather icon-edit text-warning"></i> Edit Details</a></li>
                                                              <li><a href="'.route('check-off.products.change_status', $lo->id).'"><i class="feather icon-eye text-info"></i> Change Status</a></li>
                                                              <li><a href="#" onclick="event.preventDefault(); document.getElementById('. $lo->id .').submit();"><i class="feather icon-trash text-danger"></i> Delete </a></li>
                                                        </ul>
                                        </div>
                                        <form id="'. $lo->id .'" action="'.route('check-off-products.destroy', $lo->id).'"method="POST" style="display: none;">
                                        <input type="hidden" name="_method" value="DELETE"></>
                                            '.csrf_field().'
                                        </form>
                                        ';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * @param $checkOffProductId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function change_status($checkOffProductId): \Illuminate\Http\RedirectResponse
    {
        $checkOffProduct = CheckOffProduct::query()->find($checkOffProductId);
        $status = $checkOffProduct->status;
        if ($status){
            $checkOffProduct->update([
                'status' => false
            ]);
            return Redirect::back()->with('success', $checkOffProduct->name . ' has successfully been marked as INACTIVE');
        } else {
            $checkOffProduct->update([
                'status' => true
            ]);
            return Redirect::back()->with('success', $checkOffProduct->name . ' has successfully been marked as ACTIVE');
        }
    }
}
