<?php

namespace App\Http\Controllers\Facility;

use App\Http\Resources\Facility\FacilityBookingResource;
use App\Http\Resources\Facility\FacilityResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\FacilityBookingRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use Illuminate\Http\Request;

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
            // New booking starts during an existing booking
            $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                // New booking ends during an existing booking
                ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                // New booking completely overlaps an existing booking
                ->orWhere(function ($subQuery) use ($request) {
                    $subQuery->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>=', $request->end_time);
                })
                // New booking starts and ends within the duration of an existing booking
                ->orWhere(function ($subQuery) use ($request) {
                    $subQuery->where('start_time', '>=', $request->start_time)
                        ->where('end_time', '<=', $request->end_time);
                });
        })
        ->exists();

        if ($existingBooking) {
            return (new CustomResponseResource([
                'title' => 'Booking Error',
                'message' => 'The facility is already booked for the specified time range.',
                'code' => 400,
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
            'owner_association_id' => $building->owner_association_id
        ]);

        return new CustomResponseResource([
            'title' => 'Booking Successful',
            'message' => 'Facility booking has been successfully created.',
            'code' => 200,
        ]);
    }

    // User booking
    public function userBookings(Request $request, Building $building)
    {
        $type = $request->input('type');
        
        $query = FacilityBooking::where('user_id', auth()->user()->id)
            ->where('building_id', $building->id);

        if ($type === 'facilities') {
            $query->where('bookable_type', 'App\Models\Master\Facility');
        } elseif ($type === 'services') {
            $query->where('bookable_type', 'App\Models\Master\Service');
        } 
        $bookings = $query->latest()->paginate(10);

        return FacilityBookingResource::collection($bookings);
    }
}
