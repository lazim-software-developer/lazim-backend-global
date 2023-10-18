<?php

namespace App\Http\Controllers\Facility;

use App\Http\Resources\Facility\FacilityResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\FacilityBookingRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;

class FacilityController extends Controller
{
    public function index(Building $building)
    {
        $facilities = $building->facilities;
        return FacilityResource::collection($facilities);
    }

    public function bookFacility(FacilityBookingRequest $request, Building $building)
    {
        $oaId = $building->ownerAssociation->id;

        $booking = FacilityBooking::create([
            'facility_id' => $request->facility_id,
            'user_id' => auth()->user()->id,
            'building_id' => $building->id,
            'owner_association_id' => $oaId,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return new CustomResponseResource([
            'title' => 'Booking Successful',
            'message' => 'Facility booking has been successfully created.',
            'data' => new FacilityResource($booking),
        ]);
    }
}
