<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Resources\Services\ServiceResource;
use App\Models\Building\Building;
use App\Models\Master\Service;

class ServiceController extends Controller
{
    public function listServicesForBuilding(Building $building)
    {
        // Fetch vendor IDs associated with the building
        $vendorIds = $building->vendors()->pluck('vendors.id');

        // Fetch services provided by those vendors
        $services = Service::whereHas('vendors', function ($query) use ($vendorIds) {
            $query->whereIn('vendors.id', $vendorIds);
        })->get();

        return ServiceResource::collection($services);
    }
}
