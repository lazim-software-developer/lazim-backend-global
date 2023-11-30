<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceBookingRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Facility\FacilityResource;
use App\Http\Resources\Services\ServiceResource;
use App\Imports\ServicesImport;
use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use App\Models\Master\Service;
use App\Models\Vendor\ServiceVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ServiceController extends Controller
{
    public function listServicesForBuilding(Building $building)
    {
        // Fetch vendor IDs associated with the building
        // $vendorIds = $building->vendors()->pluck('vendors.id');

        // // Fetch services provided by those vendors along with their prices
        // $services = Service::with(['vendors' => function ($query) use ($vendorIds) {
        //     $query->whereIn('vendors.id', $vendorIds)->select('service_vendor.price');
        // }])
        // ->whereHas('vendors', function ($query) use ($vendorIds) {
        //     $query->whereIn('vendors.id', $vendorIds);
        // })->get();

        // New code
        return $building->services->where('active', 1)->where('type', 'inhouse');

    }

    // Book a service
    public function bookService(ServiceBookingRequest $request, Building $building)
    {
        // Check for existing bookings for the same facility, date, and time range
        $existingBooking = FacilityBooking::where([
            'bookable_id' => $request->service_id,
            'bookable_type' => 'App\Models\Master\Service',
            'date' => $request->date,
        ])
            ->where(function ($query) use ($request) {
                $query->where('start_time', [$request->start_time, $request->end_time]);
            })->where(['user_id' => auth()->user()->id, 'bookable_id' => $request->service_id])
            ->exists(); // Just check for existence for the user

        if ($existingBooking) {
            return (new CustomResponseResource([
                'title' => 'Booking Error',
                'message' => 'The service is already booked by you for the specified date and time.',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        FacilityBooking::create([
            'bookable_id' => $request->service_id,
            'bookable_type' => 'App\Models\Master\Service',
            'user_id' => auth()->user()->id,
            'building_id' => $building->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => now()->addDays(7), //TODO: NEEDS TO CHANGE
            'owner_association_id' => $building->owner_association_id
        ]);

        return new CustomResponseResource([
            'title' => 'Booking Successful',
            'message' => 'Service booking has been successfully created.',
            'code' => 200,
        ]);
    }

    // Import Services
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);


        $servicesImport = new ServicesImport;

        Excel::import($servicesImport, $request->file('file'));

        return response()->json(['message' => 'Services imported successfully']);
    }
}
