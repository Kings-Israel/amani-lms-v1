<?php

namespace App\Http\Controllers;

use App\models\Customer;
use App\models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CustomerDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $customer_id
     * @return array|false|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function create($customer_id)
    {
        $customer = Customer::query()->findOrFail(decrypt($customer_id));
        $this->data['customer'] = $customer;
        $this->data['is_edit'] = false;
        return view('pages.customer_documents.form', $this->data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'profile_photo' => 'required',//|image|mimes:jpeg,png,jpg,gif,svg|max:4048,
            'id_front' => 'required',//|image|mimes:jpeg,png,jpg,gif,svg|max:4048,
            'id_back' => 'required',//|image|mimes:jpeg,png,jpg,gif,svg|max:4048,
        ]);
        //dd($request->all());


        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->with('error', $validator->errors()->first())->withInput();
        }
        $customer = Customer::query()->find($request->input('customer_id'));
        $profile_photo_file = $request->file('profile_photo');
        $id_front_file = $request->file('id_front');
        $id_back_file = $request->file('id_back');

        $profile_photo_file_name = $customer->fname.'_'.$customer->lname.'-'.'profile_photo';
        $profile_photo_extension = $profile_photo_file->extension();
        $profile_photo_file_name = $profile_photo_file_name .".". $profile_photo_extension;
        //Storage::disk('public')->putFileAs('customer_documents/'.$customer->id_no, $profile_photo_file, $profile_photo_file_name);
        $profile_photo_file->move(storage_path('app/public/customer_documents/'.$customer->id_no), $profile_photo_file_name);

        $profile_photo_path = sprintf('customer_documents/%s/%s', $customer->id_no, $profile_photo_file_name);

        $id_front_file_name = $customer->fname.'_'.$customer->lname.'-'.'id_front';
        $id_front_extension = $id_front_file->extension();
        $id_front_file_name = $id_front_file_name .".". $id_front_extension;
        //Storage::disk('public')->putFileAs('customer_documents/'.$customer->id_no, $id_front_file, $id_front_file_name);
        $id_front_file->move(storage_path('app/public/customer_documents/'.$customer->id_no), $id_front_file_name);

        $id_front_path = sprintf('customer_documents/%s/%s', $customer->id_no, $id_front_file_name);

        $id_back_file_name = $customer->fname.'_'.$customer->lname.'-'.'id_back';
        $id_back_extension = $id_back_file->extension();
        $id_back_file_name = $id_back_file_name .".". $id_back_extension;
       // Storage::disk('public')->putFileAs('customer_documents/'.$customer->id_no, $id_back_file, $id_back_file_name);
        $id_back_file->move(storage_path('app/public/customer_documents/'.$customer->id_no), $id_back_file_name);

        $id_back_path = sprintf('customer_documents/%s/%s', $customer->id_no, $id_back_file_name);

        $customer_document = CustomerDocument::query()->create([
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
            'profile_photo_path' => $profile_photo_path,
            'id_front_path' => $id_front_path,
            'id_back_path' => $id_back_path,
        ]);

        return Redirect::route('customer-documents.edit', (array)encrypt($customer_document->id))->with('success', 'Customer Photos have been added successfully');

    }

    public function view_mpesa_statement($customer_document_id) {
        $customer_doc = CustomerDocument::query()->with('customer')->findOrFail(decrypt($customer_document_id));
        return Storage::disk('public')->response($customer_doc->mpesa_statement_path);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_mpesa_statement(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'mpesa_statement' => 'required|max:18000',//|image|mimes:jpeg,png,jpg,gif,svg|max:4048,
        ]);
        //dd('here');

        if ($validator->fails()){
            return Redirect::back()->withErrors($validator)->with('error', $validator->errors()->first())->withInput();
        }
        $customer = Customer::query()->find($request->input('customer_id'));

        $document = CustomerDocument::query()->where('customer_id', '=', $customer->id)->first();

        if ($document and $document->mpesa_statement_path != null){
            if (Storage::disk('public')->exists($document->mpesa_statement_path)){
                Storage::disk('public')->delete($document->mpesa_statement_path);
            }
        }
        $mpesa_statement = $request->file('mpesa_statement');
        $mpesa_statement_file_name = $customer->fname.'_'.$customer->lname.'-'.'mpesa_statement';
        $mpesa_statement_extension = $mpesa_statement->extension();
        $mpesa_statement_file_name = $mpesa_statement_file_name .".". $mpesa_statement_extension;
       // Storage::disk('public')->putFileAs('customer_documents/'.$customer->id_no, $mpesa_statement, $mpesa_statement_file_name);

        $mpesa_statement->move(storage_path('app/public/customer_documents/'.$customer->id_no), $mpesa_statement_file_name);




        $mpesa_statement_path = sprintf('customer_documents/%s/%s', $customer->id_no, $mpesa_statement_file_name);


        $document = CustomerDocument::query()->where('customer_id', '=', $customer->id)->first();
        if ($document){
            $document->update([ 'mpesa_statement_path' => $mpesa_statement_path ]);
        } else {
            $document = CustomerDocument::query()->create([
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'profile_photo_path' => null,
                'id_front_path' => null,
                'id_back_path' => null,
                'mpesa_statement_path' => $mpesa_statement_path,
            ]);
        }

        return Redirect::route('customer-documents.edit', (array)encrypt($document->id))->with('success', 'Customer MPESA Statement has been added successfully');

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
     * @param $customer_document_id
     * @return \Illuminate\Http\Response
     */
    public function edit($customer_document_id)
    {
        $customer_doc = CustomerDocument::query()->with('customer')->findOrFail(decrypt($customer_document_id));
        $customer = $customer_doc->customer;
        $this->data['customer'] = $customer;
        $this->data['customer_document'] = $customer_doc;
        $this->data['is_edit'] = false;
        return view('pages.customer_documents.edit', $this->data);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $customer_document_identifier
     * @return \Illuminate\Http\RedirectResponse
     */


     public function update(Request $request, $customer_document_identifier)
     {
         $validator = Validator::make($request->all(), [
             'customer_id' => 'required|exists:customers,id',
             'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
             'id_front' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
             'id_back' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
             'mpesa_statement' => 'nullable|mimes:pdf|max:4048',
         ]);

         if ($validator->fails()) {
             return Redirect::back()->withErrors($validator)->with('error', $validator->errors()->first())->withInput();
         }

         $customerDocument = CustomerDocument::find($customer_document_identifier);
         if (!$customerDocument) {
             return Redirect::back()->with('error', 'Document not found.');
         }

         $customer = Customer::find($request->input('customer_id'));

         // Process profile photo
         if ($request->hasFile('profile_photo')) {
             Log::info('Profile photo upload started.');
             if (Storage::disk('public')->exists($customerDocument->profile_photo_path)) {
                 Storage::disk('public')->delete($customerDocument->profile_photo_path);
             }
             $profile_photo_path = $request->file('profile_photo')->storeAs(
                 'customer_documents/'.$customer->id_no,
                 $customer->fname.'_'.$customer->lname.'-profile_photo.'.$request->file('profile_photo')->extension(),
                 'public'
             );
             $customerDocument->profile_photo_path = $profile_photo_path;
             Log::info('Profile photo uploaded to: '.$profile_photo_path);
         }

         // Process ID Front
         if ($request->hasFile('id_front')) {
             Log::info('ID Front upload started.');
             if (Storage::disk('public')->exists($customerDocument->id_front_path)) {
                 Storage::disk('public')->delete($customerDocument->id_front_path);
             }
             $id_front_path = $request->file('id_front')->storeAs(
                 'customer_documents/'.$customer->id_no,
                 $customer->fname.'_'.$customer->lname.'-id_front.'.$request->file('id_front')->extension(),
                 'public'
             );
             $customerDocument->id_front_path = $id_front_path;
             Log::info('ID Front uploaded to: '.$id_front_path);
         }

         // Process ID Back
         if ($request->hasFile('id_back')) {
             Log::info('ID Back upload started.');
             if (Storage::disk('public')->exists($customerDocument->id_back_path)) {
                 Storage::disk('public')->delete($customerDocument->id_back_path);
             }
             $id_back_path = $request->file('id_back')->storeAs(
                 'customer_documents/'.$customer->id_no,
                 $customer->fname.'_'.$customer->lname.'-id_back.'.$request->file('id_back')->extension(),
                 'public'
             );
             $customerDocument->id_back_path = $id_back_path;
             Log::info('ID Back uploaded to: '.$id_back_path);
         }

         $customerDocument->user_id = Auth::id();
         $customerDocument->save();

         return Redirect::route('registry.show', encrypt($customer->id))
             ->with('success', 'Customer Documents have been updated successfully');
     }



    // public function update(Request $request, $customer_document_identifier)
    // {
    //     $validator = Validator::make($request->all(), [
    //        'customer_id' => 'required|exists:customers,id',
    //         'profile_photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
    //         'profile_photo' => 'nullable|image|mimes:jpeg,jpg',
    //         'id_front' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
    //         'id_back' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
    //     ]);
    //     /*$file = $request->file('profile_photo');
    //     $mimeType = $file->getMimeType();*/

    //     $customerDocument = CustomerDocument::query()->find($customer_document_identifier);
    //     if ($validator->fails()){
    //         return Redirect::back()->withErrors($validator)->with('error', $validator->errors()->first())->withInput();
    //     }
    //     $customer = Customer::query()->find($request->input('customer_id'));

    //     if ($request->has('profile_photo')){
    //         if (Storage::disk('public')->exists($customerDocument->profile_photo_path)){
    //            // dd('yes');

    //             Storage::disk('public')->delete($customerDocument->profile_photo_path);

    //            // dd('yes');
    //         }
    //         $profile_photo_file = $request->file('profile_photo');
    //         $profile_photo_file_name = $customer->fname.'_'.$customer->lname.'-'.'profile_photo';
    //         $profile_photo_extension = $profile_photo_file->extension();
    //         $profile_photo_file_name = $profile_photo_file_name .".". $profile_photo_extension;
    //       //  Storage::disk('public')->putFileAs('customer_documents/'.$customer->id_no, $profile_photo_file, $profile_photo_file_name);

    //         $profile_photo_file->move(storage_path('app/public/customer_documents/'.$customer->id_no), $profile_photo_file_name);

    //         $profile_photo_path = sprintf('customer_documents/%s/%s', $customer->id_no, $profile_photo_file_name);
    //         $customerDocument->profile_photo_path = $profile_photo_path;

    //     }

    //     if ($request->has('id_front')) {
    //         if (Storage::disk('public')->exists($customerDocument->id_front_path)){
    //             Storage::disk('public')->delete($customerDocument->id_front_path);
    //             // dd('yes');

    //         }
    //         $id_front_file = $request->file('id_front');
    //         $id_front_file_name = $customer->fname . '_' . $customer->lname . '-' . 'id_front';
    //         $id_front_extension = $id_front_file->extension();
    //         $id_front_file_name = $id_front_file_name . "." . $id_front_extension;
    //         //Storage::disk('public')->putFileAs('customer_documents/' . $customer->id_no, $id_front_file, $id_front_file_name);
    //         $id_front_file->move(storage_path('app/public/customer_documents/'.$customer->id_no), $id_front_file_name);

    //         $id_front_path = sprintf('customer_documents/%s/%s', $customer->id_no, $id_front_file_name);
    //         $customerDocument->id_front_path = $id_front_path;
    //     }


    //     if ($request->has('id_back')) {
    //         if (Storage::disk('public')->exists($customerDocument->id_back_path)){
    //             Storage::disk('public')->delete($customerDocument->id_back_path);
    //         }
    //         $id_back_file = $request->file('id_back');
    //         $id_back_file_name = $customer->fname . '_' . $customer->lname . '-' . 'id_back';
    //         $id_back_extension = $id_back_file->extension();
    //         $id_back_file_name = $id_back_file_name . "." . $id_back_extension;
    //        // Storage::disk('public')->putFileAs('customer_documents/' . $customer->id_no, $id_back_file, $id_back_file_name);
    //         $id_back_file->move(storage_path('app/public/customer_documents/'.$customer->id_no), $id_back_file_name);

    //         $id_back_path = sprintf('customer_documents/%s/%s', $customer->id_no, $id_back_file_name);
    //         $customerDocument->id_back_path = $id_back_path;
    //     }

    //     //mpesa statement
    //     if ($request->has('mpesa_statement')) {


    //         if (Storage::disk('public')->exists($customerDocument->mpesa_statement_path)) {
    //             Storage::disk('public')->delete($customerDocument->mpesa_statement_path);
    //         }
    //         $mpesa_statement = $request->file('mpesa_statement');
    //         $mpesa_statement_file_name = $customer->fname . '_' . $customer->lname . '-' . 'mpesa_statement';
    //         $mpesa_statement_extension = $mpesa_statement->extension();
    //         $mpesa_statement_file_name = $mpesa_statement_file_name . "." . $mpesa_statement_extension;
    //        // Storage::disk('public')->putFileAs('customer_documents/' . $customer->id_no, $mpesa_statement, $mpesa_statement_file_name);
    //         $mpesa_statement->move(storage_path('app/public/customer_documents/'.$customer->id_no), $mpesa_statement_file_name);

    //         $mpesa_statement_path = sprintf('customer_documents/%s/%s', $customer->id_no, $mpesa_statement_file_name);
    //         $customerDocument->mpesa_statement_path = $mpesa_statement_path;


    //     }







    //     //dd($id_back_path, $id_front_path, $profile_photo_path);
    //     $customerDocument->user_id = Auth::id();
    //     $customerDocument->save();
    //     //dd($customerDocument);

    //     return Redirect::route('registry.show', (array)encrypt($customer->id))->with('success', 'Customer Photos have been added successfully');
    // }

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

    public function uploadVideo(Request $request, $customer_id)
    {
        $request->validate([
            'video_file' => 'required|mimes:mp4,avi|max:50480',
        ]);

        $customer_document = CustomerDocument::where('customer_id', $customer_id)->first();

        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('videos', 'public');
            $customer_document->video_path = $path;
            $customer_document->save();
        }

        return redirect()->back()->with('success', 'Video uploaded successfully!');
    }

    public function uploadStoreVideo(Request $request, $customer_id)
    {
        $request->validate([
            'video_file' => 'required|mimes:mp4,avi|max:50480',
        ]);

        if ($request->hasFile('video_file')) {
            $customer_document = new CustomerDocument();

            $customer_document->customer_id = $customer_id;

            $customer_document->user_id = auth()->id();

            $path = $request->file('video_file')->store('videos', 'public');

            $customer_document->video_path = $path;

            $customer_document->save();
        }

        return redirect()->back()->with('success', 'Video uploaded successfully!');
    }




    public function store_video(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'video_file' => 'required|max:50000',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)
                ->with('error', $validator->errors()->first())
                ->withInput();
        }

        $customer = Customer::query()->find($request->input('customer_id'));

        $document = CustomerDocument::query()->where('customer_id', '=', $customer->id)->first();
        if ($document && $document->video_path != null) {
            if (Storage::disk('public')->exists($document->video_path)) {
                Storage::disk('public')->delete($document->video_path);
            }
        }

        $video_file = $request->file('video_file');
        $video_file_name = $customer->fname . '_' . $customer->lname . '-video';
        $video_file_extension = $video_file->extension();
        $video_file_name = $video_file_name . "." . $video_file_extension;

        $video_file->move(storage_path('app/public/customer_videos/' . $customer->id_no), $video_file_name);

        $video_file_path = sprintf('customer_videos/%s/%s', $customer->id_no, $video_file_name);

        if ($document) {
            $document->update(['video_path' => $video_file_path]);
        } else {
            CustomerDocument::query()->create([
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'profile_photo_path' => null,
                'id_front_path' => null,
                'id_back_path' => null,
                'mpesa_statement_path' => null,
                'video_path' => $video_file_path,
            ]);
        }

        return Redirect::route('customer-documents.edit', (array)encrypt($document->id))
            ->with('success', 'Customer video has been added successfully');
    }



}
