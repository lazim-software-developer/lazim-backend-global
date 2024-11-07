<?php

namespace App\Http\Controllers;

use App\Models\WorkPermit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\WorkPermitRequest;
use App\Http\Resources\WorkListResource;
use App\Models\Building\FacilityBooking;
use App\Http\Resources\PermitWorkResource;
use App\Http\Resources\CustomResponseResource;

class PermitWorkController extends Controller
{
    public function workList()
    {
        $workPermit = WorkPermit::where('active', true);

        return WorkListResource::collection($workPermit->paginate(10));
    }
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:work_permits,name',
        ]);

        $data = $request->all();
        if($request->hasFile('file')){
            $data['icon'] = optimizeDocumentAndUpload($request->file);
        }
        WorkPermit::create($data);

        return new CustomResponseResource([
            'title'   => 'Created Successful',
            'message' => 'Successful creted new work list',
            'code'    => 201,
            'status'  => 'Ok',
        ]);

    }
    public function index(Request $request)
    {
        $workPermit = FacilityBooking::where([
            'user_id'       => auth()->user()->id,
            'bookable_type' => WorkPermit::class,
            'building_id'   => $request->building_id,
            'flat_id'       => $request->flat_id,
        ]);

        return PermitWorkResource::collection($workPermit->paginate(10));
    }

    public function store(WorkPermitRequest $request)
    {
        // Check for existing bookings for the same facility, date, and time range
        $existingBooking = FacilityBooking::where([
            'bookable_id'   => $request->facility_id,
            'bookable_type' => WorkPermit::class,
            'date'          => $request->date,
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
                'title'   => 'Booking Error',
                'message' => 'The Permit to work is already booked for the specified time range.',
                'code'    => 400,
            ]))->response()->setStatusCode(400);
        }
        $flat_id = $request->input('flat_id');
        $owner_association_id = DB::table('building_owner_association')->where(['building_id' => $request->building_id , 'active' => true])
                                ->first()?->owner_association_id;
        FacilityBooking::create([
            'bookable_id'          => $request->facility_id,
            'bookable_type'        => WorkPermit::class,
            'user_id'              => auth()->user()->id,
            'building_id'          => $request->building_id,
            'flat_id'              => $flat_id,
            'date'                 => $request->date,
            'start_time'           => $request->start_time,
            'end_time'             => $request->end_time,
            'description'          => $request->description,
            'owner_association_id' => $owner_association_id,
        ]);

        return new CustomResponseResource([
            'title'   => 'Booking Successful',
            'message' => 'Permit to work has been successfully created.',
            'code'    => 200,
        ]);

    }
}
