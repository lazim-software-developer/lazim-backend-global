<?php

namespace App\Http\Controllers\Facility;

use App\Http\Resources\Facility\FacilityBookingResource;
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
        // Check for existing bookings for the same facility, date, and time range
        $existingBooking = FacilityBooking::where([
            'bookable_id' => $request->facility_id,
            'bookable_type' => 'App\Models\Master\Facility',
            'date' => $request->date,
        ])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })
            ->exists(); // Just check for existence

        if ($existingBooking) {
            return (new CustomResponseResource([
                'title' => 'Booking Error',
                'message' => 'The facility is already booked for the specified time range.',
                'errorCode' => 400,
            ]))->response()->setStatusCode(400);
        }

        $booking = FacilityBooking::create([
            'bookable_id' => $request->facility_id,
            'bookable_type' => 'App\Models\Master\Facility',
            'user_id' => auth()->user()->id,
            'building_id' => $building->id,
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

    // User booking
    public function userBookings(Building $building)
    {
        $bookings = FacilityBooking::where('user_id', auth()->user()->id)
            ->where('building_id', $building->id)
            ->get();

        return FacilityBookingResource::collection($bookings);
    }
}
