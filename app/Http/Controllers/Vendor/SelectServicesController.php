<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\SelectServicesRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\SelectServicesResource;
use App\Models\Master\Service;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelectServicesController extends Controller
{
    public function listServices(Request $request)
    {
        $services = Service::all();
        return $services;
    }

    public function addService(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'vendor_id' => 'required|integer|exists:vendors,id',
        ]);
        $request->merge([ 
            'custom' => 1,
            'active' => 1,
            'owner_association_id' => Vendor::find($request->vendor_id)->owner_association_id 
        ]);

        $service = Service::create($request->all());       

        return (new CustomResponseResource([
            'title' => 'Service added!',
            'message' => "",
            'errorCode' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function tagServices(SelectServicesRequest $request)
    {

        $serviceIds= $request->service_ids;
        $vendor = Vendor::find($request->vendor_id);
        foreach ($serviceIds as $serviceId){
            if(!(DB::table('service_vendor')->where('vendor_id',$request->vendor_id)->where('service_id',$serviceId))->first()){
                $vendor->services()->attach($serviceId);
            }
        };
        return (new CustomResponseResource([
            'title' => 'Services taged!',
            'message' => "",
            'errorCode' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);

    }

    public function showServices(Request $request)
    {
        $vendor_id =Vendor::where('owner_id', auth()->user()->id)->first()->id;
        $services=DB::table('service_vendor')->where('vendor_id',$vendor_id)->get();

        return SelectServicesResource::collection($services);
    }
}
