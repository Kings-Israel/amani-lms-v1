<?php

namespace App\Http\Controllers;

use App\models\Branch;
use App\models\CheckOffEmployee;
use App\models\CheckOffEmployer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class CheckOffEmployerController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|accountant');
    }

    /**
     * Display a listing of the resource.
     *
     * @return array|false|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function index()
    {
        $this->data['title'] = "Check Off Loan Employers";
        $this->data['sub_title'] = "List of all employers whose employees can apply for Advance Loans";
        return view('pages.check-off.employers.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return array|false|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function create()
    {
        $this->data['title'] = "Create New Check Off Loan Employer";
        $this->data['is_edit'] = false;
        return view('pages.check-off.employers.form', $this->data);
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
            "location" => "required|string|min:2|max:100",
            "contact_name" => "required|string|min:2|max:100",
            "contact_phone_number" => "required|string|min:2|max:100"
        ]);
        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }
        $employer = CheckOffEmployer::query()->create([
            'code' => $this->generateEmployerRefNumber(),
            'name' => $request->input('name'),
            'location' => $request->input('location'),
            'contact_name' => $request->input('contact_name'),
            'contact_phone_number' => '0' . substr($request->input('contact_phone_number'), -9),
            'status' => true
        ]);
        return Redirect::route('check-off-employers.index')->with('success', "$employer->name has been registered successfully.");
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
        $employer = CheckOffEmployer::query()->find($id);
        $this->data['employer'] = $employer;
        $this->data['title'] = "Edit Employer Details:  $employer->name";
        $this->data['is_edit'] = true;
        return view('pages.check-off.employers.form', $this->data);
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
            "location" => "required|string|min:2|max:100",
            "contact_name" => "required|string|min:2|max:100",
            "contact_phone_number" => "required|string|min:2|max:100"
        ]);
        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', $validator->errors()->first());
        }
        $employer = CheckOffEmployer::query()->find($id);
        if ($employer){
            $employer->update([
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'contact_name' => $request->input('contact_name'),
                'contact_phone_number' => '0' . substr($request->input('contact_phone_number'), -9) ,
            ]);
            return Redirect::route('check-off-employers.index')->with('success', "$employer->name has been registered successfully.");
        }
        return Redirect::route('check-off-employers.index')->with('error', "Employer Details could not be retrieved.");

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $employer = CheckOffEmployer::query()->withCount('employees')->find($id);
        $employeesCount = $employer->employees_count;
        if ($employeesCount == 0){
            $employer->delete();
            return Redirect::route('check-off-employers.index')->with('success', "$employer->name has been deleted successfully.");
        }
        return Redirect::route('check-off-employers.index')->with('error', "$employer->name has registered employees and can therefore not be deleted.");

    }

    /**
     * Data Table
     * @return mixed
     * @throws \Exception
     */
    public function data(){
        $lo = CheckOffEmployer::query()->select('check_off_employers.*');
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
                return '<div class="btn-group text-center">
                                                <a type="button" class="btn btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings"></i> </a>
                                                        <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                                                             <li><a href="'.route('check-off-employers.edit', $lo->id).'"><i class="feather icon-edit text-warning"></i> Edit Details</a></li>
                                                              <li><a href="'.route('check-off.employers.change_status', $lo->id).'"><i class="feather icon-eye text-info"></i> Change Status</a></li>
                                                              <li><a href="#" onclick="event.preventDefault(); document.getElementById('. $lo->id .').submit();"><i class="feather icon-trash text-danger"></i> Delete </a></li>
                                                        </ul>
                                        </div>
                                        <form id="'. $lo->id .'" action="'.route('check-off-employers.destroy', $lo->id).'"method="POST" style="display: none;">
                                        <input type="hidden" name="_method" value="DELETE"></>
                                            '.csrf_field().'
                                        </form>
                                        ';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * @return string
     */
    private function generateEmployerRefNumber(): string
    {
        $ref_number = 'BCL' . mt_rand(100, 999);
        while ($this->refNumberExists($ref_number)) {
            $ref_number = 'BCL' . mt_rand(999, 9999);
        }
        return $ref_number;
    }


    /**
     * @param string $ref_number
     * @return bool
     */
    private function refNumberExists(string $ref_number): bool
    {
        return CheckOffEmployer::query()->where('code', '=',$ref_number)->exists();
    }


    /**
     * @param $checkOffEmployerId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function change_status($checkOffEmployerId): \Illuminate\Http\RedirectResponse
    {
       $checkOffEmployer = CheckOffEmployer::query()->find($checkOffEmployerId);
        $status = $checkOffEmployer->status;
        if ($status){
            $checkOffEmployer->update([
                'status' => false
            ]);
            return Redirect::back()->with('success', $checkOffEmployer->name . ' has successfully been marked as INACTIVE');
        } else {
            $checkOffEmployer->update([
                'status' => true
            ]);
            return Redirect::back()->with('success', $checkOffEmployer->name . ' has successfully been marked as ACTIVE');
        }
    }
}
