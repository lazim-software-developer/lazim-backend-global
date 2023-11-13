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
    public function listServices()
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

        Service::create($request->all());       

        return (new CustomResponseResource([
            'title' => 'Service added!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function tagServices(SelectServicesRequest $request, Vendor $vendor)
    {
        $serviceIds = $request->service_ids;

        $vendor->services()->sync($serviceIds);

        return (new CustomResponseResource([
            'title' => 'Services taged!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);

    }

    public function showServices(Vendor $vendor)
    {
        $services = $vendor->services;

        return SelectServicesResource::collection($services);
    }
}
