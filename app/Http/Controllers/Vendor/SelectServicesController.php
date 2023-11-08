<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\SelectServicesRequest;
use App\Http\Resources\CustomResponseResource;
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
            'owner_association_id' => 'required|integer|exists:owner_associations,id',
        ]);
        $request->merge([ 'custom' => 1, 'active' => true ]);

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
            if(!(DB::table('service_vendor')->where([['service_id'=>$serviceId],['vendor_id'=>$vendor->id]]))){
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
}
