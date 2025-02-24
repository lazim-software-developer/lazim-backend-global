<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomResponseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{
    public function version(Request $request){

        $version = DB::table('app_versions')->where('type', $request->type)->where('active',true)->latest()->first();
        if($version){
            return $version;
        }
        return (new CustomResponseResource([
            'title' => 'No Verions Available',
            'message' => 'No Verions Available for this Application!',
            'code' => 404,
        ]))->response()->setStatusCode(404);
    }
}
