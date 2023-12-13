<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\SelectServicesRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Services\ServiceResource;
use App\Http\Resources\Vendor\SelectServicesResource;
use App\Http\Resources\Vendor\SubCategoryResource;
use App\Models\Accounting\SubCategory;
use App\Models\Master\Service;
use App\Models\Vendor\ServiceVendor;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class SelectServicesController extends Controller
{
public function listServices(SubCategory $subcategory)
    {
        $services = Service::where('active', 1)->where('subcategory_id',$subcategory->id)->get();
        return SelectServicesResource::collection($services);
    }

    // Add a new custom service;
    public function addService(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string',
            'subcategory_id' => 'required|integer|exists:subcategories,id',
        ]);

        $request->merge([
            'custom' => 1,
            'active' => 1,
            'owner_association_id' => $vendor->owner_association_id,
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
            'data'  => $service
        ]))->response()->setStatusCode(201);
    }

    public function tagServices(SelectServicesRequest $request, Vendor $vendor)
    {
        $vendor->services()->syncWithoutDetaching([$request->service]);

        return (new CustomResponseResource([
            'title' => 'Service taged!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function untagServices(SelectServicesRequest $request, Vendor $vendor)
    {
        $vendor->services()->detach([$request->service]);

        return (new CustomResponseResource([
            'title' => 'Service untaged!',
            'message' => "",
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function showServices(Request $request,Vendor $vendor)
    {
        // $vendorServices = ServiceVendor::where('vendor_id',$vendor->id)->where('active', true)->when(isset($request->building_id), function ($query) use ($request) {
        //     $buildingId = $request->building_id;
        //     return $query->where('building_id', $buildingId);
        // })->pluck('service_id');
        $services = $vendor->services->unique();

        return SelectServicesResource::collection($services);
    }

    public function listSubCategories()
    {
        $categories = SubCategory::all();
        return SubCategoryResource::collection($categories);
    }
}
