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
use Illuminate\Support\Facades\DB;

class SelectServicesController extends Controller
{
    public function listServices(SubCategory $subcategory)
    {
        $services = Service::where('active', 1)->where('subcategory_id', $subcategory->id)->get();
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
            'data' => $service
        ]))->response()->setStatusCode(201);
    }

    public function tagServices(SelectServicesRequest $request, Vendor $vendor)
    {
        if ($request->has('building_id')) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

        try {
            // Check if service is already tagged
            $isServiceAlreadyTagged = $vendor->services()->where('service_id', $request->service)->exists();


            if ($isServiceAlreadyTagged) {
                // Optionally you can return a success message here if you want
                return (new CustomResponseResource([
                    'title' => 'Service already exists.',
                    'message' => 'Service is already tagged to this vendor.',
                    'code' => 200, // Return a success code instead of error
                    'status' => 'success',
                ]))->response()->setStatusCode(200);
            }

            $vendor->services()->syncWithoutDetaching([$request->service]);

            return (new CustomResponseResource([
                'title' => 'Service tagged successfully.',
                'message' => 'Service tagged successfully',
                'code' => 201,
                'status' => 'success',
            ]))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Failed to tag service. Please try again.',
                'code' => 500,
                'status' => 'error',
            ]))->response()->setStatusCode(500);
        }
    }

    public function untagServices(SelectServicesRequest $request, Vendor $vendor)
    {
        if ($request->has('building_id')) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

        $vendor->services()->detach([$request->service]);

        return (new CustomResponseResource([
            'title' => 'Service untaged!',
            'message' => "",
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function showServices(Request $request, Vendor $vendor)
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

    public function listCategories()
    {
        $categories = Service::whereIn('id', [5, 36, 69, 40, 228])->get();
        return SubCategoryResource::collection($categories);
        // return [
        //     'data' => [
        //         [
        //             "id" => 5,
        //             "name" => "House Keeping"
        //         ],
        //         [
        //             "id" => 36,
        //             "name" => "Security"
        //         ],
        //         [
        //             "id" => 69,
        //             "name" => "Electrical"
        //         ],
        //         [
        //             "id" => 69,
        //             "name" => "Plumbing"
        //         ],
        //         [
        //             "id" => 69,
        //             "name" => "AC"
        //         ],
        //         [
        //             "id" => 40,
        //             "name" => "Pest Control"
        //         ],
        //         [
        //             "id" => 228,
        //             "name" => "Other"
        //         ]
        //     ]
        // ];
    }
}
