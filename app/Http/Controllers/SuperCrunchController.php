<?php

namespace App\Http\Controllers;

use App\models\SuperCrunchResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuperCrunchController extends Controller
{
    public function callback(Request $request): \Illuminate\Http\JsonResponse
    {
        if (count($request->all()) > 0) {
            $response = new SuperCrunchResponse();
            $response->response = $request->all();
            $response->save();
            return response()->json(['message'=>'Success'], Response::HTTP_OK);
        }
        return response()->json(['message'=>'Failed'], Response::HTTP_BAD_REQUEST);
    }
}
