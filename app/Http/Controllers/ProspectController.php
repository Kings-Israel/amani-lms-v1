<?php

namespace App\Http\Controllers;

use App\Imports\ProspectsImport;
use App\Jobs\Prospects;
use App\Jobs\Sms;
use App\models\Customer;
use App\models\Guarantor;
use App\models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\VarDumper\Cloner\Data;
use Yajra\DataTables\DataTables;

class ProspectController extends Controller
{
    public function index()
    {
        $this->data['title'] = "System Prospects ";
        $this->data['prospects_count'] = Prospect::query()->count();
        return view('pages.registry.prospects.index', $this->data);
    }

    public function prospects_data()
    {
        $prospects = Prospect::select('*')->orderByDesc('id');
        return DataTables::of($prospects)
            ->addColumn('action', function ($lo) {
                $data = $lo->id;
             return '<div class="btn-group text-center">
                      <button type="button" class="sel-btn btn btn-xs btn-primary" data-toggle="dropdown" aria-expanded="false"><i class="feather icon-settings" style="margin-right: 0 !important;"></i> </button>
                         <ul class="dropdown-menu" style=" left: -10em; padding: 1em">
                            <li><a href="' . route('prospect.delete', ['id' => encrypt($data)]) . '"><i class="feather icon-delete text-danger" ></i> Delete</a></li>
                         </ul>
                     </div>';
            })
->addColumn('checkbox', function ($lo) {
    return '<input type="checkbox" name="id[]" value="' . $lo->id . '" id="' . $lo->id . '">';


})
->rawColumns(['action', 'checkbox'])
            ->toJson();


    }
    /*********************************send sms form*******************/
    public function prospects_sms(Request $request){
       // dd($request->all());
        if (isset($request->delete)){
            foreach ($request->id as $id){
                $prospect = Prospect::find($id)->delete();
            }
            return back()->with(['success' => 'successfully deleted prospects']);
        }
        $this->data['is_customer'] =  false;
        $this->data['is_guarantor'] =  false;
        if ($request->check){
            $this->data['title'] = "SMS Selected Prospects ";
            $this->data['selected'] = $request->id;
           //var_dump($request->id); exit();
            $this->data['is_selected'] =  true;
        }
        else{
            $this->data['title'] = "SMS All Prospects ";
            $this->data['prospects'] = Prospect::all();
            $this->data['is_selected'] =  false;
        }
        return view('pages.registry.prospects.sms', $this->data);
    }

    public function prospect_sms_post(Request $request){
        //dd(json_decode($request->selected));
        if($request->selected){
            //dd(json_decode($request->selected));

            foreach (json_decode($request->selected) as $id){
                $prospect = Prospect::find($id);
                $amessage = "Dear ".$prospect->name.', '."\r\n"
                    . $request->message;

                $aphone = '+254' . substr($prospect->phone, -9);
                $auser = Auth::user();
                $suser_type = true;

                $fnd = dispatch(new Prospects(

                    $aphone, $amessage, $auser, $suser_type, $prospect

                ));

            }


        }
        elseif ($request->is_customer){
            $prospects = Customer::all();

            foreach ($prospects as $prospect){
                $amessage = "Dear ".$prospect->fname.', '."\r\n"
                    . $request->message;
                $aphone = '+254' . substr($prospect->phone, -9);
                $auser = $prospect;
                $suser_type = false;

                $fnd = dispatch(new Sms(

                    $aphone, $amessage, $auser, $suser_type

                ));

            }

            return redirect()->route('registry.index');

        }

        elseif ($request->is_guarantor){
            $prospects = Guarantor::all();

            foreach ($prospects as $prospect){
                $amessage = "Dear ".$prospect->gname.', '."\r\n"
                    . $request->message;
                $aphone = '+254' . substr($prospect->ghone, -9);
                $auser = Auth::user();
                $suser_type = true;

                $fnd = dispatch(new Sms(

                    $aphone, $amessage, $auser, $suser_type

                ));

            }

            return redirect()->route('guarantors.index');

        }

        else{
            $prospects = Prospect::where('received', false)->get();

            foreach ($prospects as $prospect){
                $amessage = "Dear ".$prospect->name.', '."\r\n"
                    . $request->message;
                $aphone = '+254' . substr($prospect->phone, -9);
                $auser = Auth::user();
                $suser_type = true;

                $fnd = dispatch(new Prospects(

                    $aphone, $amessage, $auser, $suser_type, $prospect

                ));

            }
        }



        return redirect()->route('prospects')/*->with(['success', 'sending messages'])*/;
    }

    public function  create(){
        $this->data['title'] = "Create Prospects ";
        return view('pages.registry.prospects.create', $this->data);
    }

    public function get_template(){
        $template = public_path('templates'. DIRECTORY_SEPARATOR .'prospect-template.xlsx');
        return response()->download($template);
    }

    public function post_template(Request $request){
        $this->validate($request,[
            'template' => 'required|mimes:xls,xlsx'
        ], [
            'template.mimes' => 'The template must be an Excel sheet'
        ]);
        // Get the file.
        $file = $request->file('template');
//        $filename = str_random(20) .'.'. $file->getClientOriginalExtension();
        //Move Uploaded File
//        $destinationPath = public_path('uploads'. DIRECTORY_SEPARATOR .'prospects');
//        $file->move($destinationPath, $filename);
        //added
        $import =  Excel::import( new ProspectsImport(), $file);
//        $users = Excel::load($destinationPath . DIRECTORY_SEPARATOR . $filename, function ($reader) {
//            try {
//                return $reader->select(array('name','phone'))->get();
//            } catch (\Exception $e) {
//                return 0;
//            }
//        });
//        $data = $users->toArray();
//        $user_data = array_filter($data);
//          $phone = '254' . substr($request->phone, -9);
//
//        if (count($user_data) == 0) {
//            return back()->with('error','Ooops! Something went wrong.');
//        }
//        $now = date('Y-m-d H:i:s');

//        foreach($user_data as $key => $user) {
//            if ($user['name']==null || $user['phone']==null){
//                return back()->with('error','Ensure you fill all the fields.');
//            }
//            $user_data[$key]['phone'] = '+254' . substr($user['phone'], -9);
//            $user_data[$key]['created_at'] = $now;
//            $user_data[$key]['updated_at'] = $now;
//            $user2 = Prospect::updateOrCreate(['phone' => '+254'.substr($user['phone'], -9)], array('name' => $user['name']));
//        }
//        unlink($destinationPath . DIRECTORY_SEPARATOR . $filename);
        //Prospect::insert($user_data);
        //$user2 = Prospect::updateOrCreate(['phone' => '+254'.substr($user['phone'], -9)], array('name' => $user['name']));
//        return back()->with('success','Successfully added prospects.');
        if ($import){
            return redirect()->route('prospects')->with('success','Successfully added prospects.');
        }else{
            return redirect()->route('prospects')->with('error','Something went wrong');
        }
    }

    public function delete($id){
        $find = Prospect::where('id', decrypt($id))->delete();
        return back()->with('success', 'successfully deleted prospect');
    }

    public function delete_all_prospects(): \Illuminate\Http\RedirectResponse
    {
        Prospect::query()->delete();
        return back()->with('success', 'successfully deleted all prospects');
    }

    public function delete_selected(Request $request){
        dd($request->all());
    }
}
