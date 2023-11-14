<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\SelectServicesRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Services\ServiceResource;
use App\Http\Resources\Vendor\SelectServicesResource;
use App\Models\Master\Service;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class SelectServicesController extends Controller
{
    public function listServices()
    {
        $services = Service::where('active', 1)->get();
        return ServiceResource::collection($services);
    }

    // Add a new custom service;
    public function addService(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $request->merge([
            'custom' => 1,
            'active' => 1,
            'owner_association_id' => $vendor->owner_association_id
        ]);

        $service = Service::firstOrCreate(
            [
                'name' => $request->name
            ],
            $request->all()
        );

        $vendor->services()->syncWithoutDetaching([$service->id]);

        return (new CustomResponseResource([
            'title' => 'Service added!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function tagServices(SelectServicesRequest $request, Vendor $vendor)
    {
        $vendor->services()->syncWithoutDetaching([$request->service]);

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
