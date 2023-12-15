<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Models\Accounting\SubCategory;
use App\Models\Building\BuildingPoc;
use App\Models\Building\Complaint;
use App\Http\Resources\HelpDesk\Complaintresource;
use App\Models\Master\Service;

class ComplaintController extends Controller
{
    public function index()
    {
        // Check if the gatekeeper is having active account inuildingPOC table
        $building = BuildingPoc::where([
            'user_id' => auth()->user()->id,
            'role_name' => 'security',
            'active' => 1
        ]);

        if(!$building->exists()) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => "You don't have access to login to the application!",
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        // Get the building_id
        $buildingId = $building->first()->building_id;

        // Fetch al the complaints which are not resolved for the building where service_id is security related services

        // Fetch service_id of "Security Services" from subcategories table 

        $securityServiceId = SubCategory::where('name' , 'Security Services')->value('id');

        $services = Service::where('subcategory_id', $securityServiceId)->pluck('id');

        // Fetch complaints
        $complaints = Complaint::whereIn('service_id', $services)
        ->where([
            'building_id' => $buildingId,
            'status' => 'open',
        ])->latest()->paginate(10);

        return Complaintresource::collection($complaints);
    }
}
